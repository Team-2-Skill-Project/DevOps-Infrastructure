<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\CandidateSkill;
use App\Models\JobSkill;
use App\Models\RoadmapStep;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SkillTaxonomyService
{
    public function __construct(private AuditService $audit) {}

    public function resolve(string $name): ?Skill
    {
        $normalized = $this->normalize($name);
        $skill = Skill::query()->where('normalized_name', $normalized)->first();

        return $skill ?? SkillAlias::query()->where('normalized_alias', $normalized)->first()?->skill;
    }

    public function normalize(string $name): string
    {
        return Str::lower(Str::trim($name));
    }

    /** @param array{name?: string, skill_category_id?: int|null, category?: string|null} $data */
    public function saveSkill(?Skill $skill, array $data, ?User $actor = null): Skill
    {
        try {
            return DB::transaction(function () use ($skill, $data, $actor): Skill {
                $record = $skill ? Skill::query()->lockForUpdate()->findOrFail($skill->id) : new Skill;

                $isNew = ! $record->exists;
                $before = $isNew ? null : $record->only(['name', 'category', 'skill_category_id']);

                if (array_key_exists('name', $data)) {
                    $data['name'] = Str::trim($data['name']);
                    $normalized = $this->normalize($data['name']);
                    $this->ensureAvailable($normalized, $record->exists ? $record->id : null, null, false);
                    $record->normalized_name = $normalized;
                }

                $record->fill($data)->save();
                $record->refresh();

                $this->audit->record(
                    $isNew ? AuditAction::SKILL_CREATED : AuditAction::SKILL_UPDATED,
                    AuditEntityType::SKILL,
                    (int) $record->getKey(),
                    AuditSource::ADMIN,
                    $actor ?? $this->audit->currentActor(),
                    $before,
                    $record->only(['name', 'category', 'skill_category_id']),
                );

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => __('skill.validation.name_taken')]);
        }
    }

    /** @param array{alias?: string} $data */
    public function saveAlias(Skill $skill, ?SkillAlias $alias, array $data, ?User $actor = null): SkillAlias
    {
        try {
            return DB::transaction(function () use ($skill, $alias, $data, $actor): SkillAlias {
                $parent = Skill::query()->lockForUpdate()->findOrFail($skill->id);
                $record = $alias ? $parent->aliases()->lockForUpdate()->findOrFail($alias->id) : new SkillAlias;

                $isNew = ! $record->exists;
                $before = $isNew ? null : $record->only(['alias']);

                if (array_key_exists('alias', $data)) {
                    $data['alias'] = Str::trim($data['alias']);
                    $this->ensureAvailable($this->normalize($data['alias']), $parent->id, $record->exists ? $record->id : null, true);
                }

                $record->fill($data);
                $record->skill()->associate($parent);
                $record->save();
                $record->refresh();

                $this->audit->record(
                    $isNew ? AuditAction::SKILL_ALIAS_CREATED : AuditAction::SKILL_ALIAS_UPDATED,
                    AuditEntityType::SKILL,
                    (int) $parent->getKey(),
                    AuditSource::ADMIN,
                    $actor ?? $this->audit->currentActor(),
                    $before,
                    $record->only(['alias']),
                    ['alias_id' => (int) $record->getKey()],
                );

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['alias' => __('skill.validation.alias_taken')]);
        }
    }

    public function deleteAlias(Skill $skill, SkillAlias $alias, ?User $actor = null): void
    {
        DB::transaction(function () use ($skill, $alias, $actor): void {
            $parent = Skill::query()->lockForUpdate()->findOrFail($skill->id);
            $record = $parent->aliases()->lockForUpdate()->findOrFail($alias->id);

            $before = $record->only(['alias']);
            $aliasId = (int) $record->getKey();
            $record->delete();

            $this->audit->record(
                AuditAction::SKILL_ALIAS_DELETED,
                AuditEntityType::SKILL,
                (int) $parent->getKey(),
                AuditSource::ADMIN,
                $actor ?? $this->audit->currentActor(),
                $before,
                null,
                ['alias_id' => $aliasId],
            );
        }, 3);
    }

    /** @return array{skill: Skill, meta: array<string, int>} */
    public function mergeSkill(Skill $source, Skill $target, ?User $actor = null): array
    {
        if ($source->id === $target->id) {
            throw ValidationException::withMessages(['target_skill_id' => __('skill.validation.different_target')]);
        }

        try {
            return DB::transaction(function () use ($source, $target, $actor): array {
                $skills = Skill::query()->whereIn('id', [$source->id, $target->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $source = $skills->get($source->id);
                $target = $skills->get($target->id);

                if (! $source || ! $target) {
                    throw ValidationException::withMessages(['target_skill_id' => __('skill.validation.merge_subject_missing')]);
                }

                $ids = [$source->id, $target->id];
                $candidateLinks = CandidateSkill::query()->whereIn('skill_id', $ids)->orderBy('id')->lockForUpdate()->get();
                $jobLinks = JobSkill::query()->whereIn('skill_id', $ids)->orderBy('id')->lockForUpdate()->get();
                $aliases = SkillAlias::query()->whereIn('skill_id', $ids)->orderBy('id')->lockForUpdate()->get();
                $steps = RoadmapStep::query()->where('target_skill_id', $source->id)->orderBy('id')->lockForUpdate()->get();
                $meta = [
                    'merged_skill_id' => $source->id, 'target_skill_id' => $target->id,
                    'candidate_skill_links_moved' => 0, 'candidate_skill_links_consolidated' => 0,
                    'job_skill_links_moved' => 0, 'job_skill_links_consolidated' => 0,
                    'aliases_moved' => 0, 'aliases_collapsed' => 0, 'source_name_aliases_created' => 0,
                    'roadmap_step_references_moved' => 0,
                ];

                $candidateTargets = $candidateLinks->where('skill_id', $target->id)->keyBy('candidate_profile_id');
                foreach ($candidateLinks->where('skill_id', $source->id) as $link) {
                    $existing = $candidateTargets->get($link->candidate_profile_id);
                    if ($existing) {
                        $existing->update([
                            'proficiency_level' => $existing->proficiency_level ?? $link->proficiency_level,
                            'confidence' => $this->higherConfidence($existing->confidence, $link->confidence),
                            'evidence' => $this->mergeEvidence($existing->evidence, $link->evidence),
                        ]);
                        $link->delete();
                        $meta['candidate_skill_links_consolidated']++;
                    } else {
                        $link->update(['skill_id' => $target->id]);
                        $meta['candidate_skill_links_moved']++;
                    }
                }

                $jobTargets = $jobLinks->where('skill_id', $target->id)->keyBy('job_post_id');
                foreach ($jobLinks->where('skill_id', $source->id) as $link) {
                    $existing = $jobTargets->get($link->job_post_id);
                    if ($existing) {
                        $existing->update([
                            'is_required' => $existing->is_required || $link->is_required,
                            'importance' => $existing->importance ?? $link->importance,
                            'required_level' => $this->strongerRequiredLevel($existing->required_level, $link->required_level),
                        ]);
                        $link->delete();
                        $meta['job_skill_links_consolidated']++;
                    } else {
                        $link->update(['skill_id' => $target->id]);
                        $meta['job_skill_links_moved']++;
                    }
                }

                foreach ($steps as $step) {
                    $step->update(['target_skill_id' => $target->id]);
                    $meta['roadmap_step_references_moved']++;
                }

                foreach ($aliases->where('skill_id', $source->id) as $alias) {
                    $normalized = $this->normalize($alias->alias);
                    $duplicate = SkillAlias::query()->where('normalized_alias', $normalized)->where('id', '!=', $alias->id)->lockForUpdate()->first();
                    if (($duplicate && $duplicate->skill_id === $target->id) || $normalized === $target->normalized_name) {
                        $alias->delete();
                        $meta['aliases_collapsed']++;

                        continue;
                    }

                    $this->ensureAvailable($normalized, $target->id, $alias->id, true, $source->id);
                    $alias->update(['skill_id' => $target->id]);
                    $meta['aliases_moved']++;
                }

                $oldName = $this->normalize($source->name);
                if ($oldName !== $target->normalized_name) {
                    $existing = SkillAlias::query()->where('normalized_alias', $oldName)->lockForUpdate()->first();
                    $this->ensureAvailable($oldName, $target->id, $existing?->skill_id === $target->id ? $existing->id : null, true, $source->id);
                    if (! $existing) {
                        $target->aliases()->create(['alias' => $source->name]);
                        $meta['source_name_aliases_created']++;
                    }
                }

                $source->delete();

                $this->audit->record(
                    AuditAction::SKILL_MERGED,
                    AuditEntityType::SKILL,
                    (int) $target->id,
                    AuditSource::ADMIN,
                    $actor ?? $this->audit->currentActor(),
                    ['source_skill' => ['id' => $source->id, 'name' => $source->name]],
                    ['target_skill' => ['id' => $target->id, 'name' => $target->name]],
                    $meta,
                );

                return ['skill' => $target, 'meta' => $meta];
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['target_skill_id' => __('skill.validation.merge_conflict')]);
        }
    }

    private function higherConfidence(?string $target, ?string $source): ?string
    {
        if ($target === null) {
            return $source;
        }

        return $source !== null && (float) $source > (float) $target ? $source : $target;
    }

    /**
     * @param  array<array-key, mixed>|null  $target
     * @param  array<array-key, mixed>|null  $source
     * @return array<array-key, mixed>|null
     */
    private function mergeEvidence(?array $target, ?array $source): ?array
    {
        if ($target === null || $target === []) {
            return $source ?? $target;
        }
        if ($source === null || $source === [] || $target === $source) {
            return $target;
        }

        $merged = array_is_list($target) ? $target : [$target];
        foreach (array_is_list($source) ? $source : [$source] as $entry) {
            if (! in_array($entry, $merged, true)) {
                $merged[] = $entry;
            }
        }

        return $merged;
    }

    private function strongerRequiredLevel(?string $target, ?string $source): ?string
    {
        if ($target === null) {
            return $source;
        }

        $levels = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
        if ($source !== null && isset($levels[$source], $levels[$target]) && $levels[$source] > $levels[$target]) {
            return $source;
        }

        return $target;
    }

    private function ensureAvailable(string $normalized, ?int $skillId, ?int $aliasId, bool $forAlias, ?int $mergingSourceId = null): void
    {
        $canonical = Skill::query()->where('normalized_name', $normalized)->lockForUpdate()->first();
        $alias = SkillAlias::query()->where('normalized_alias', $normalized)->lockForUpdate()->first();

        if (($canonical && $canonical->id !== $skillId && $canonical->id !== $mergingSourceId)
            || ($alias && ($forAlias ? $alias->id !== $aliasId : $alias->skill_id !== $skillId))) {
            throw ValidationException::withMessages([
                $forAlias ? 'alias' : 'name' => __('skill.validation.name_unavailable'),
            ]);
        }
    }
}
