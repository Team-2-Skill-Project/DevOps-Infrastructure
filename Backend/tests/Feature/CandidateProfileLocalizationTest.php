<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Certificate;
use App\Models\Language;
use App\Models\Skill;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('candidate profile errors use the requested language with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/candidate/career-preferences')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على ملف المرشح الشخصي.'],
    'regional Arabic' => ['ar-EG', 'لم يتم العثور على ملف المرشح الشخصي.'],
    'English' => ['en', 'Candidate profile not found.'],
    'regional English' => ['en-US', 'Candidate profile not found.'],
    'unsupported language' => ['fr-FR', 'Candidate profile not found.'],
]);

test('candidate authorization errors are localized in Arabic', function (array $attributes, string $message) {
    $user = User::factory()->create($attributes);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/candidate/profile')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'inactive candidate' => [
        ['role' => 'candidate', 'is_active' => false],
        'حسابك غير نشط.',
    ],
    'non-candidate' => [
        ['role' => 'admin', 'is_active' => true],
        'يمكن للمرشحين فقط الوصول إلى هذا الملف الشخصي.',
    ],
]);

test('owned candidate resources return localized Arabic not-found messages', function (string $endpoint, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', 'ar')
        ->deleteJson($endpoint)
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'education' => ['/api/candidate/educations/999999', 'لم يتم العثور على المؤهل التعليمي.'],
    'experience' => ['/api/candidate/experiences/999999', 'لم يتم العثور على خبرة العمل.'],
    'project' => ['/api/candidate/projects/999999', 'لم يتم العثور على المشروع.'],
    'skill' => ['/api/candidate/skills/999999', 'لم يتم العثور على مهارة المرشح.'],
]);

test('standard profile validation errors use localized attribute names with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/candidate/profile', ['github_url' => 'not-a-url'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.github_url.0', $message);

    $this->assertDatabaseCount('candidate_profiles', 0);
})->with([
    'Arabic' => ['ar', 'يجب أن يكون حقل رابط GitHub رابطًا صالحًا.'],
    'English' => ['en', 'The github url field must be a valid URL.'],
    'unsupported language' => ['de-DE', 'The github url field must be a valid URL.'],
]);

test('candidate skill search validation is localized in Arabic', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/skills/search?q=%20%20%20')
        ->assertUnprocessable()
        ->assertJsonPath('errors.q.0', 'حقل عبارة البحث مطلوب.');
});

test('candidate date relationship errors are localized in Arabic', function (string $endpoint, array $payload, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', 'ar')
        ->postJson($endpoint, $payload)
        ->assertUnprocessable()
        ->assertJsonPath('errors.end_date.0', $message);
})->with([
    'education date order' => [
        '/api/candidate/educations',
        ['institution' => 'جامعة القاهرة', 'start_date' => '2024-01-01', 'end_date' => '2023-01-01'],
        'يجب أن يكون تاريخ الانتهاء مساويًا لتاريخ البدء أو بعده.',
    ],
    'current education end date' => [
        '/api/candidate/educations',
        ['institution' => 'جامعة القاهرة', 'is_current' => true, 'end_date' => '2024-01-01'],
        'يجب أن يكون تاريخ الانتهاء فارغًا عندما يكون التعليم مستمرًا حاليًا.',
    ],
    'current experience end date' => [
        '/api/candidate/experiences',
        ['job_title' => 'Developer', 'company_name' => 'SkillMatch', 'is_current' => true, 'end_date' => '2024-01-01'],
        'يجب أن يكون تاريخ الانتهاء فارغًا عندما تكون خبرة العمل مستمرة حاليًا.',
    ],
    'project date order' => [
        '/api/candidate/projects',
        ['name' => 'Portfolio', 'start_date' => '2024-01-01', 'end_date' => '2023-01-01'],
        'يجب أن يكون تاريخ الانتهاء مساويًا لتاريخ البدء أو بعده.',
    ],
]);

test('duplicate candidate skill errors are localized in Arabic', function () {
    $profile = CandidateProfile::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);
    $candidateSkill = CandidateSkill::factory()->for($profile)->for($skill)->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/candidate/skills', ['name' => 'LARAVEL'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', 'هذه المهارة مضافة بالفعل إلى ملفك الشخصي.');

    $this->assertModelExists($candidateSkill);
    $this->assertDatabaseCount('candidate_skills', 1);
});

test('localized profile retrieval does not translate user-generated content or machine values', function () {
    $profile = CandidateProfile::factory()->create([
        'job_title' => 'Senior Backend Engineer',
        'professional_summary' => 'أبني واجهات برمجية موثوقة.',
    ]);
    Certificate::factory()->for($profile)->create(['name' => 'AWS Solutions Architect', 'source' => 'manual']);
    Language::factory()->for($profile)->create(['language' => 'Deutsch', 'proficiency_level' => 'B2', 'source' => 'manual']);

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/candidate/profile')
        ->assertOk()
        ->assertJsonPath('data.profile.job_title', 'Senior Backend Engineer')
        ->assertJsonPath('data.profile.professional_summary', 'أبني واجهات برمجية موثوقة.')
        ->assertJsonPath('data.profile.certificates.0.name', 'AWS Solutions Architect')
        ->assertJsonPath('data.profile.certificates.0.source', 'manual')
        ->assertJsonPath('data.profile.languages.0.language', 'Deutsch')
        ->assertJsonPath('data.profile.languages.0.proficiency_level', 'B2')
        ->assertJsonPath('data.profile.languages.0.source', 'manual');
});
