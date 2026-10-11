<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function createCompanyAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

test('company store validation uses the requested language with English fallback', function (string $language, string $message) {
    $admin = createCompanyAdmin();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);
})->with([
    'Arabic' => ['ar', 'حقل الاسم مطلوب.'],
    'regional Arabic' => ['ar-EG', 'حقل الاسم مطلوب.'],
    'English' => ['en', 'The name field is required.'],
    'regional English' => ['en-US', 'The name field is required.'],
    'unsupported language' => ['fr-FR', 'The name field is required.'],
]);

test('company creation preserves submitted content across languages', function (string $language) {
    $admin = createCompanyAdmin();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies', [
            'name' => 'Acme Industries',
            'website_url' => 'https://acme.example.com',
            'industry' => 'Technology',
            'country' => 'Egypt',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Acme Industries')
        ->assertJsonPath('data.website_url', 'https://acme.example.com')
        ->assertJsonPath('data.industry', 'Technology')
        ->assertJsonPath('data.country', 'Egypt');

    $company = Company::query()->where('name', 'Acme Industries')->sole();
    expect($company->normalized_name)->toBe('acme industries');
})->with(['Arabic' => ['ar'], 'English' => ['en']]);

test('duplicate company and alias names return localized validation messages', function (string $language, string $message) {
    $admin = createCompanyAdmin();
    $company = Company::factory()->create(['name' => 'Globex', 'normalized_name' => 'globex']);
    CompanyAlias::factory()->for($company)->create(['alias' => 'Globex Corp', 'normalized_alias' => 'globex corp']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies', ['name' => '  GLOBEX  '])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies', ['name' => 'Globex Corp'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);
})->with([
    'Arabic' => ['ar', 'اسم الشركة مستخدم بالفعل من قبل شركة أو اسم بديل.'],
    'regional Arabic' => ['ar-EG', 'اسم الشركة مستخدم بالفعل من قبل شركة أو اسم بديل.'],
    'English' => ['en-US', 'This company name is already used by a company or alias.'],
]);

test('company listing and details keep data identical across languages', function () {
    $admin = createCompanyAdmin();
    $company = Company::factory()->create(['name' => 'Initech', 'industry' => 'Software']);

    $arabic = $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/admin/companies?search=initech')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Initech')
        ->json('data.0');

    $english = $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'en')
        ->getJson('/api/admin/companies?search=initech')
        ->assertOk()
        ->json('data.0');

    expect($arabic)->toBe($english);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'ar-EG')
        ->getJson('/api/admin/companies/'.$company->id)
        ->assertOk()
        ->assertJsonPath('data.id', $company->id)
        ->assertJsonPath('data.name', 'Initech')
        ->assertJsonPath('data.industry', 'Software');
});

test('company update preserves content and validates attribute names', function (string $language, string $nameMessage) {
    $admin = createCompanyAdmin();
    $company = Company::factory()->create(['name' => 'Hooli']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/admin/companies/'.$company->id, ['name' => 'Hooli Systems', 'website_url' => 'not-a-url'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.website_url.0', $nameMessage);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/admin/companies/'.$company->id, ['name' => 'Hooli Systems'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Hooli Systems');

    expect($company->fresh()->normalized_name)->toBe('hooli systems');
})->with([
    'Arabic' => ['ar', 'يجب أن يكون حقل رابط الموقع الإلكتروني رابطًا صالحًا.'],
    'English' => ['en', 'The website URL field must be a valid URL.'],
]);

test('missing companies return localized not-found messages', function (string $method, string $endpoint, string $language, string $message) {
    $admin = createCompanyAdmin();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->{$method}($endpoint)
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic show' => ['getJson', '/api/admin/companies/999999', 'ar', 'لم يتم العثور على الشركة.'],
    'regional Arabic show' => ['getJson', '/api/admin/companies/999999', 'ar-EG', 'لم يتم العثور على الشركة.'],
    'English show' => ['getJson', '/api/admin/companies/999999', 'en-US', 'Company not found.'],
    'English update' => ['patchJson', '/api/admin/companies/999999', 'en', 'Company not found.'],
]);

test('missing merge source returns a localized not-found message', function (string $language, string $message) {
    $admin = createCompanyAdmin();
    $target = Company::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies/999999/merge', ['target_company_id' => $target->id])
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على الشركة.'],
    'fallback' => ['fr', 'Company not found.'],
]);

test('company admin authentication errors use the requested language', function (string $language, string $message) {
    $this->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/companies')
        ->assertUnauthorized()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'غير مصادق عليه.'],
    'English' => ['en', 'Unauthenticated.'],
]);

test('existing admin authorization behavior is preserved with localized messages', function (string $role, bool $active, string $language, string $message) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/companies')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic candidate' => ['candidate', true, 'ar', 'فقط المسؤولون يمكنهم إدارة المهارات.'],
    'English candidate' => ['candidate', true, 'en-US', 'Only administrators can manage skills.'],
    'Arabic inactive admin' => ['admin', false, 'ar-EG', 'حسابك غير نشط.'],
    'English inactive admin' => ['super_admin', false, 'en', 'Your account is inactive.'],
]);

test('company merge target validation is localized', function (string $case, string $language, string $message) {
    $admin = createCompanyAdmin();
    $source = Company::factory()->create();
    Company::factory()->create();
    $data = match ($case) {
        'missing' => [],
        'absent' => ['target_company_id' => 999999],
        'self' => ['target_company_id' => $source->id],
    };

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies/'.$source->id.'/merge', $data)
        ->assertUnprocessable()
        ->assertJsonPath('errors.target_company_id.0', $message);
})->with([
    'Arabic missing' => ['missing', 'ar', 'حقل الشركة المستهدفة مطلوب.'],
    'English missing' => ['missing', 'en', 'The target company field is required.'],
    'Arabic absent' => ['absent', 'ar-EG', 'قيمة حقل الشركة المستهدفة المحددة غير صالحة.'],
    'English absent' => ['absent', 'en-US', 'The selected target company is invalid.'],
    'Arabic self merge' => ['self', 'ar', 'اختر شركة مستهدفة مختلفة.'],
    'English self merge' => ['self', 'en', 'Choose a different target company.'],
]);

test('company merge localizes nothing structural and preserves meta counters', function (string $language) {
    $admin = createCompanyAdmin();
    $source = Company::factory()->create(['name' => 'SourceCo']);
    $target = Company::factory()->create(['name' => 'TargetCo']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/companies/'.$source->id.'/merge', ['target_company_id' => $target->id])
        ->assertOk()
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.name', 'TargetCo')
        ->assertJsonPath('meta.merged_company_id', $source->id)
        ->assertJsonPath('meta.target_company_id', $target->id);

    expect(Company::query()->whereKey($source->id)->exists())->toBeFalse();
})->with(['Arabic' => ['ar'], 'English' => ['en']]);
