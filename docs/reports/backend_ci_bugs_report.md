# Backend CI Failure & Bug Report
**Target System:** PHP 8.4 / Laravel 11 / PostgreSQL 16 Alpine / Redis 7 Alpine  
**CI Workflow:** `CI — Test All Services` (`backend-tests` job)  
**Status:** 1,077 Passed, **14 Failed** (Exit Code 2)

---

## Executive Summary for the Backend Team
The DevOps automated CI pipeline executes tests against real production-parity containers (**PostgreSQL 16** and **Redis 7**), matching the local CentOS Stream 10 target host. 

While tests pass locally when executed against an in-memory SQLite database (`phpunit.xml` default), running against PostgreSQL 16 revealed **14 test failures across 6 distinct categories**:
1. **3 failures:** `ORDER BY DESC` sorting of `NULL` dates differs between SQLite and PostgreSQL.
2. **3 failures:** Fatal `ValueError` due to an inconsistent `CvExtractionStatus` enum refactor.
3. **4 failures:** HTTP 401 Unauthorized due to using the default `web` session guard instead of `auth:api` in test requests.
4. **1 failure:** Strict type assertion failure (`=== 1`) on PostgreSQL native boolean column return.
5. **1 failure:** Seeder execution sequence bug leaving 0 skills attached to the candidate profile.
6. **2 failures:** PostgreSQL aborted transaction state (`25P02`) triggered by testing unhandled database-level constraints.

In addition, commit `c99bac3` (`"fix(ci): remove invalid JSON comments and bypass test execution in CI scripts"`) removed `@php artisan test` from `composer ci:check`, masking these failures on the standalone Backend repository.

---

## Detailed Breakdown of the 14 Failures & Recommended Fixes

### Category 1: SQL Dialect Incompatibility — `NULL` Sorting on `DESC` (3 Tests)

#### Affected Tests:
- `tests/Feature/CandidateEducationApiTest.php:82`
- `tests/Feature/CandidateExperienceApiTest.php:82`
- `tests/Feature/CandidateProjectApiTest.php:82`

#### Failure Symptoms:
```text
Failed asserting that two arrays are identical:
- Array [ 0 => 4, 1 => 3, 2 => 2, 3 => 1 ]  // Expected: Newer -> Older -> Undated
+ Array [ 0 => 1, 1 => 4, 2 => 3, 3 => 2 ]  // Actual (Postgres): Undated -> Newer -> Older
```

#### Root Cause:
In the candidate controllers ([`EducationController.php:16`](file:///home/omar/Projects/SkillMatch/Backend/app/Http/Controllers/Candidate/EducationController.php#L16), [`ExperienceController.php:16`](file:///home/omar/Projects/SkillMatch/Backend/app/Http/Controllers/Candidate/ExperienceController.php#L16), [`ProjectController.php:16`](file:///home/omar/Projects/SkillMatch/Backend/app/Http/Controllers/Candidate/ProjectController.php#L16)):
```php
$educations = $request->profile()->educations()->orderByDesc('start_date')->orderByDesc('id')->get();
```
- In **SQLite / MySQL**, `NULL` values sort **LAST** by default when using `ORDER BY ... DESC`.
- In **PostgreSQL**, standard ANSI SQL specifies `NULLS FIRST` for `DESC` ordering by default.
Consequently, the undated entry (`start_date = NULL`, ID 1) is returned first in PostgreSQL.

#### Recommended Fix:
Enforce database-agnostic `NULLS LAST` sorting in all three controllers:
```php
// EducationController.php, ExperienceController.php, ProjectController.php
$records = $request->profile()->{relation}()
    ->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END, start_date DESC')
    ->orderByDesc('id')
    ->get();
```

---

### Category 2: Enum Backing Value Crash — `CvExtractionStatus` (3 Tests)

#### Affected Tests:
- `tests/Feature/CandidateProfileApiTest.php:72` (2 test cases)
- `tests/Feature/Models/CvDocumentTest.php:44`

#### Failure Symptoms:
```text
ValueError: "completed" is not a valid backing value for enum App\Enums\CvExtractionStatus
at vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:1317
```

#### Root Cause:
In commit `f1ba366`, `App\Enums\CvExtractionStatus` was changed:
```php
// Before:
case COMPLETED = 'completed';

// After:
case SUCCESS = 'SUCCESS';
```
However:
1. Migration `database/migrations/2026_09_10_114421_create_cv_extractions_table.php:21` specifies: `// pending | processing | completed | failed`.
2. Sister enum `App\Enums\CvParsingStatus` maintains `case COMPLETED = 'completed';`.
3. `CandidateProfileApiTest.php` and `CvDocumentTest.php` insert test records with `'status' => 'completed'`.
When Eloquent attempts to cast `'completed'` to `CvExtractionStatus`, PHP 8.4 throws a fatal `ValueError`.

#### Recommended Fix:
Update [`app/Enums/CvExtractionStatus.php`](file:///home/omar/Projects/SkillMatch/Backend/app/Enums/CvExtractionStatus.php) to support `completed`:
```php
namespace App\Enums;

enum CvExtractionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'failed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

---

### Category 3: Missing `api` Guard in Tests — CV Verification (4 Tests)

#### Affected Tests:
- `tests/Feature/CvVerificationTest.php:13` (`assertUnprocessable()` failed: expected 422, got 401)
- `tests/Feature/CvVerificationTest.php:25` (`assertForbidden()` failed: expected 403, got 401)
- `tests/Feature/CvVerificationTest.php:35` (`assertOk()` failed: expected 200, got 401)
- `tests/Feature/CvVerificationTest.php:35` (`assertOk()` failed: expected 200, got 401)

#### Failure Symptoms:
```text
Expected response status code [200|403|422] but received 401.
Failed asserting that 401 is identical to 200.
```

#### Root Cause:
The route `/api/cv/extractions/{extraction}/verify` is registered under [`routes/cv.php:6`](file:///home/omar/Projects/SkillMatch/Backend/routes/cv.php#L6):
```php
Route::middleware(['auth:api'])->prefix('cv')->group(function () { ... });
```
However, in [`tests/Feature/CvVerificationTest.php`](file:///home/omar/Projects/SkillMatch/Backend/tests/Feature/CvVerificationTest.php), the tests authenticate using:
```php
$this->actingAs($extraction->cvDocument->candidateProfile->user);
$this->actingAs(User::factory()->create());
```
Because no guard argument is passed, Laravel defaults to the `web` session guard. The `auth:api` (JWT) middleware rejects the request with HTTP 401 Unauthorized before the controller is ever invoked.

#### Recommended Fix:
Pass the `'api'` guard to `actingAs` (or use `$this->actingAsApi($user)` from `tests/TestCase.php`):
```php
// tests/Feature/CvVerificationTest.php:10, 22, 32
$this->actingAs($extraction->cvDocument->candidateProfile->user, 'api');
$this->actingAs(User::factory()->create(), 'api');
```

---

### Category 4: PostgreSQL Native Boolean Strict Equality (1 Test)

#### Affected Test:
- `tests/Feature/JobPostFoundationTest.php:93`

#### Failure Symptoms:
```text
Failed asserting that true is identical to 1.
at tests/Feature/JobPostFoundationTest.php:93
```

#### Root Cause:
```php
->and($job->requiredSkills->first()->pivot->is_required)->toBe(1)
```
- In **SQLite**, boolean columns are stored as integer `1`/`0`.
- In **PostgreSQL**, boolean columns are native booleans, so `pdo_pgsql` returns `true`/`false`.
- Pest's `->toBe(1)` performs strict equality (`=== 1`), which fails on PostgreSQL (`true !== 1`).

#### Recommended Fix:
Use `->toBeTrue()` or `->toBeTruthy()`:
```php
// tests/Feature/JobPostFoundationTest.php:93
->and($job->requiredSkills->first()->pivot->is_required)->toBeTruthy()
```

---

### Category 5: Seeder Execution Sequence (1 Test)

#### Affected Test:
- `tests/Feature/Models/CandidateProfileTest.php:215`

#### Failure Symptoms:
```text
Failed asserting that actual size 0 matches expected size 1.
at tests/Feature/Models/CandidateProfileTest.php:215
```

#### Root Cause:
In [`CandidateProfileSeeder.php:35-40`](file:///home/omar/Projects/SkillMatch/Backend/database/seeders/CandidateProfileSeeder.php#L35-L40):
```php
$skill = Skill::query()->where('name', 'Laravel')->first();
if ($skill) {
    $profile->skills()->syncWithoutDetaching([
        $skill->id => ['source' => CandidateSkill::SOURCE_MANUAL]
    ]);
}
```
In `tests/Feature/Models/CandidateProfileTest.php:209-210`:
```php
$this->seed(CandidateProfileSeeder::class);
$this->seed(SkillSeeder::class);
```
Because `CandidateProfileSeeder` runs **before** `SkillSeeder`, the "Laravel" skill does not exist yet. `$skill` is `null`, and no skill is attached. Then line 215 asserts `expect($profile->skills)->toHaveCount(1);`, which fails with count 0. In `database/seeders/DatabaseSeeder.php:26-30`, `SkillSeeder` correctly runs before `CandidateProfileSeeder`.

#### Recommended Fix:
Seed `SkillSeeder` first:
```php
// tests/Feature/Models/CandidateProfileTest.php:209-210
$this->seed(SkillSeeder::class);
$this->seed(CandidateProfileSeeder::class);
```

---

### Category 6: PostgreSQL Aborted Transaction on Integrity Constraint Tests (2 Tests)

#### Affected Tests:
- `tests/Feature/RawJobTest.php:108`
- `tests/Feature/RawJobTest.php:117`

#### Failure Symptoms:
```text
QueryException: SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted, 
commands ignored until end of transaction block (Connection: pgsql, SQL: select count(*) as "aggregate" from "raw_jobs")
```

#### Root Cause:
These tests deliberately trigger database integrity errors (`QueryException`) to verify that the schema blocks invalid foreign keys and duplicate records:
```php
expect(fn () => RawJob::factory()->create([...]))->toThrow(QueryException::class);
$this->assertDatabaseCount('raw_jobs', 0);
```
- In **SQLite**, when an `INSERT` fails inside a transaction, subsequent queries in the same transaction can still execute.
- In **PostgreSQL**, whenever an unhandled database error occurs within a transaction, PostgreSQL marks the entire transaction as aborted (`25P02`). Any subsequent query (such as `$this->assertDatabaseCount(...)`) fails immediately with `25P02`.

#### Recommended Fix:
Wrap the expected failure in a nested transaction (which creates a PostgreSQL `SAVEPOINT`):
```php
// tests/Feature/RawJobTest.php
try {
    DB::transaction(fn () => RawJob::factory()->create(['ingestion_run_id' => $run->id, 'job_source_id' => $other->id]));
} catch (QueryException) {}

$this->assertDatabaseCount('raw_jobs', 0);
```

---

## Action Checklist for Backend Team
1. [ ] Update `app/Http/Controllers/Candidate/` (`EducationController`, `ExperienceController`, `ProjectController`) to sort `NULL` dates last.
2. [ ] Add `case COMPLETED = 'completed';` to `App\Enums\CvExtractionStatus`.
3. [ ] Update `tests/Feature/CvVerificationTest.php` to authenticate with the `'api'` guard.
4. [ ] Update `tests/Feature/JobPostFoundationTest.php:93` to use `->toBeTruthy()`.
5. [ ] Swap seeder order in `tests/Feature/Models/CandidateProfileTest.php:209-210`.
6. [ ] Wrap expected database constraint errors in `tests/Feature/RawJobTest.php` in `DB::transaction()` savepoints.
7. [ ] Restore `@php artisan test` under `composer.json` (`ci:check`).
