<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use App\Services\GuruPiketPopupService;
use App\Services\SettingsManager;
use Carbon\Carbon;
use Tests\TestCase;

class GuruPiketPopupServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_the_admin_popup_content_for_a_guru_assigned_today(): void
    {
        Carbon::setTestNow('2026-10-16 08:00:00');

        $guru = new Guru(['hari_piket' => ['Jumat']]);
        $user = new User();
        $user->setRelation('guru', $guru);
        $settings = new class extends SettingsManager {
            public function get($key, $default = null)
            {
                return [
                    'guru_piket_popup.title' => 'Piket Jumat',
                    'guru_piket_popup.content' => 'Silakan bertugas.',
                ][$key] ?? $default;
            }
        };

        $popup = (new GuruPiketPopupService($settings))->forLogin($user);

        $this->assertSame([
            'title' => 'Piket Jumat',
            'content' => '<p>Silakan bertugas.</p>',
        ], $popup);
    }

    public function test_it_does_not_return_a_popup_when_today_is_not_a_scheduled_day(): void
    {
        Carbon::setTestNow('2026-10-16 08:00:00');

        $guru = new Guru(['hari_piket' => ['Kamis']]);
        $user = new User();
        $user->setRelation('guru', $guru);

        $popup = (new GuruPiketPopupService(new SettingsManager()))->forLogin($user);

        $this->assertNull($popup);
    }

    public function test_it_does_not_return_a_popup_when_the_user_has_no_linked_guru(): void
    {
        Carbon::setTestNow('2026-10-16 08:00:00');

        $popup = (new GuruPiketPopupService(new SettingsManager()))->forLogin(new User());

        $this->assertNull($popup);
    }

    public function test_it_keeps_rich_formatting_and_removes_unsafe_html_from_popup_content(): void
    {
        $service = new GuruPiketPopupService(new SettingsManager());

        $clean = $service->sanitizeContent(
            '<p style="text-align: center"><strong>Piket hari ini</strong></p><script>alert(1)</script><a href="javascript:alert(1)">tautan</a>'
        );

        $this->assertStringContainsString('<strong>Piket hari ini</strong>', $clean);
        $this->assertStringContainsString('text-align:center', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }
}
