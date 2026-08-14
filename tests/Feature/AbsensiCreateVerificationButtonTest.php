<?php

namespace Tests\Feature;

use Tests\TestCase;

class AbsensiCreateVerificationButtonTest extends TestCase
{
    public function test_verification_save_button_is_not_submit_and_prevents_form_submission(): void
    {
        $contents = file_get_contents(base_path('resources/views/absensi/create.blade.php'));

        $this->assertStringContainsString('type="button" id="saveVerificationConfigBtn"', $contents);
        $this->assertStringContainsString("saveVerificationConfigBtn.addEventListener('click', function(event)", $contents);
        $this->assertStringContainsString('event.preventDefault();', $contents);
    }
}
