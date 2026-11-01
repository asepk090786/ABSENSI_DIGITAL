<?php

namespace Tests\Feature;

use App\Models\CbtSsoTicket;
use App\Models\Role;
use App\Models\User;
use App\Services\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class CbtSsoFeatureToggleTest extends TestCase
{
    use RefreshDatabase;

    protected FakeSettingsManager $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = new FakeSettingsManager();
        $this->app->instance(SettingsManager::class, $this->settings);
    }

    public function test_admin_can_control_the_cbt_login_setting(): void
    {
        $admin = $this->createUserWithRole('Admin');
        config(['services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start']);

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($admin)
            ->get(route('setting.menu'))
            ->assertOk()
            ->assertSee('Aktifkan login Garuda CBT untuk Admin, guru, dan siswa')
            ->assertSee('name="cbt_sso_enabled"', false);

        $this->actingAs($admin)
            ->post(route('setting.cbt-sso.update'), ['cbt_sso_enabled' => '1'])
            ->assertRedirect(route('setting.menu'));

        $this->assertTrue($this->settings->get('integrations.cbt_sso.enabled'));

        $this->actingAs($admin)
            ->post(route('setting.cbt-sso.update'), ['cbt_sso_enabled' => '0'])
            ->assertRedirect(route('setting.menu'));

        $this->assertFalse($this->settings->get('integrations.cbt_sso.enabled'));
    }

    public function test_only_admin_can_change_the_cbt_login_setting(): void
    {
        $teacher = $this->createUserWithRole('Guru');

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($teacher)
            ->post(route('setting.cbt-sso.update'), ['cbt_sso_enabled' => '1'])
            ->assertForbidden();

        $this->assertFalse($this->settings->get('integrations.cbt_sso.enabled', false));
    }

    public function test_admin_can_launch_cbt_sso_when_globally_enabled(): void
    {
        $admin = $this->createUserWithRole('Admin');
        config(['services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start']);

        $this->settings->set('integrations.cbt_sso.enabled', false);
        $this->actingAs($admin)->get(route('sso.cbt.login'))->assertForbidden();
        $this->actingAs($admin)->view('layouts.app')->assertDontSee('Login CBT');

        $this->settings->set('integrations.cbt_sso.enabled', true);
        $this->actingAs($admin)->view('layouts.app')->assertSee('Login CBT');
        $this->actingAs($admin)->view('tabler.partials.sidebar_menu')->assertSee('Login CBT');
        $this->actingAs($admin)
            ->get(route('sso.cbt.login'))
            ->assertRedirect('https://cbt.example.test/sso/start');
    }

    public function test_cbt_menu_is_only_visible_to_teachers_when_enabled(): void
    {
        $teacher = $this->createUserWithRole('Guru');

        $this->settings->set('integrations.cbt_sso.enabled', false);
        $this->actingAs($teacher)
            ->view('layouts.navbars.sidebar', ['menuVisibility' => ['guru' => [], 'siswa' => []]])
            ->assertDontSee('Login CBT');

        $this->settings->set('integrations.cbt_sso.enabled', true);
        $this->actingAs($teacher)
            ->view('layouts.navbars.sidebar', ['menuVisibility' => ['guru' => [], 'siswa' => []]])
            ->assertSee('Login CBT');
        $this->actingAs($teacher)
            ->view('tabler.partials.sidebar_menu')
            ->assertSee('Login CBT');
        $this->actingAs($teacher)
            ->view('layouts.app')
            ->assertSee('Login CBT');

        $student = $this->createUserWithRole('Siswa');
        $this->actingAs($student)
            ->view('layouts.navbars.sidebar', ['menuVisibility' => ['guru' => [], 'siswa' => []]])
            ->assertSee('Login CBT');
        $this->actingAs($student)
            ->view('tabler.partials.sidebar_menu')
            ->assertSee('Login CBT');
        $this->actingAs($student)
            ->view('layouts.app')
            ->assertSee('Login CBT');

        $this->settings->set('menu_visibility.guru', ['pembelajaran_materi']);
        $this->actingAs($teacher)
            ->view('tabler.partials.sidebar_menu')
            ->assertDontSee('Login CBT');
        $this->actingAs($teacher)
            ->view('layouts.app')
            ->assertDontSee('Login CBT');
        $this->settings->set('menu_visibility.siswa', ['pembelajaran_materi']);
        $this->actingAs($student)
            ->view('tabler.partials.sidebar_menu')
            ->assertDontSee('Login CBT');
        $this->actingAs($student)
            ->view('layouts.app')
            ->assertDontSee('Login CBT');

        $this->settings->set('menu_visibility.guru', ['integrations_cbt_sso']);
        $this->actingAs($teacher)
            ->view('tabler.partials.sidebar_menu')
            ->assertSee('Login CBT');
        $this->actingAs($teacher)
            ->view('layouts.app')
            ->assertSee('Login CBT');

        $this->settings->set('menu_visibility.siswa', ['integrations_cbt_sso']);
        $this->actingAs($student)
            ->view('layouts.app')
            ->assertSee('Login CBT');

        $this->settings->set('integrations.cbt_sso.enabled', false);
        $this->actingAs($teacher)
            ->view('tabler.partials.sidebar_menu')
            ->assertDontSee('Login CBT');
    }

    public function test_launch_is_blocked_while_disabled_and_issues_a_ticket_when_enabled(): void
    {
        $teacher = $this->createUserWithRole('Guru');
        $state = bin2hex(random_bytes(32));
        config(['services.cbt_sso.callback_url' => 'https://cbt.example.test/sso/consume']);

        $this->settings->set('integrations.cbt_sso.enabled', false);
        $this->actingAs($teacher)
            ->get(route('sso.cbt.launch', ['state' => $state]))
            ->assertForbidden();
        $this->assertDatabaseCount('cbt_sso_tickets', 0);

        $this->settings->set('integrations.cbt_sso.enabled', true);
        $response = $this->actingAs($teacher)
            ->get(route('sso.cbt.launch', ['state' => $state]));

        $response->assertRedirect();
        $destination = parse_url($response->headers->get('Location'));
        parse_str($destination['query'] ?? '', $query);
        $this->assertSame($state, $query['state'] ?? null);
        $this->assertMatchesRegularExpression('/\A[A-Za-z0-9]{64}\z/', $query['ticket'] ?? '');
        $this->assertDatabaseHas('cbt_sso_tickets', [
            'user_id' => $teacher->id,
            'ticket_hash' => hash('sha256', $query['ticket']),
            'state' => $state,
        ]);
    }

    public function test_teacher_login_menu_redirects_to_configured_cbt_start_url(): void
    {
        $teacher = $this->createUserWithRole('Guru');
        $this->settings->set('integrations.cbt_sso.enabled', true);
        config(['services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start']);

        $this->actingAs($teacher)
            ->get(route('sso.cbt.login'))
            ->assertRedirect('https://cbt.example.test/sso/start')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer');

        $this->assertDatabaseCount('cbt_sso_tickets', 0);
    }

    public function test_admin_cannot_enable_cbt_login_without_a_valid_https_start_url(): void
    {
        $admin = $this->createUserWithRole('Admin');
        config(['services.cbt_sso.start_url' => 'http://cbt.example.test/sso/start']);

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($admin)
            ->from(route('setting.menu'))
            ->post(route('setting.cbt-sso.update'), ['cbt_sso_enabled' => '1'])
            ->assertRedirect(route('setting.menu'))
            ->assertSessionHasErrors('cbt_sso_enabled');

        $this->assertFalse($this->settings->get('integrations.cbt_sso.enabled', false));
    }

    public function test_menu_settings_include_cbt_for_both_roles_and_persist_selections(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($admin)
            ->get(route('setting.menu'))
            ->assertOk()
            ->assertSee('name="guru_menus[]" value="integrations_cbt_sso"', false)
            ->assertSee('name="siswa_menus[]" value="integrations_cbt_sso"', false);

        $this->post(route('setting.menu.update'), [
            'guru_menus' => ['integrations_cbt_sso'],
            'siswa_menus' => ['pembelajaran_materi', 'integrations_cbt_sso'],
        ])->assertRedirect(route('setting.menu'));

        $this->assertSame(['integrations_cbt_sso'], $this->settings->get('menu_visibility.guru'));
        $this->assertSame(
            ['pembelajaran_materi', 'integrations_cbt_sso'],
            $this->settings->get('menu_visibility.siswa')
        );
    }

    public function test_student_can_start_cbt_sso_only_when_global_and_student_menu_are_enabled(): void
    {
        $student = $this->createUserWithRole('Siswa');
        config(['services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start']);
        $this->settings->set('integrations.cbt_sso.enabled', true);
        $this->settings->set('menu_visibility.siswa', ['pembelajaran_materi']);

        $this->actingAs($student)->get(route('sso.cbt.login'))->assertForbidden();

        $this->settings->set('menu_visibility.siswa', ['integrations_cbt_sso']);
        $this->actingAs($student)
            ->get(route('sso.cbt.login'))
            ->assertRedirect('https://cbt.example.test/sso/start');
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['role_name' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}

class FakeSettingsManager extends SettingsManager
{
    private array $settings = [];

    public function get($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function set($key, $value)
    {
        data_set($this->settings, $key, $value);
    }
}
