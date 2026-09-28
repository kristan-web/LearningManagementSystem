<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeleteUserOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_deleted_only_with_the_emailed_code(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response([], 201)]);
        $admin = User::create(['first_name' => 'Ad', 'last_name' => 'Min', 'email' => 'admin@example.com', 'password' => 'secret123', 'role' => 'Admin', 'status' => 'Active']);
        $target = User::create(['first_name' => 'Tar', 'last_name' => 'Get', 'email' => 'target@example.com', 'password' => 'secret123', 'role' => 'Student', 'status' => 'Active']);

        $this->actingAs($admin)->postJson("/admin/users/delete/{$target->user_id}/otp")->assertOk();
        // Reopening the modal right away must not send (and invalidate) a second code.
        $this->postJson("/admin/users/delete/{$target->user_id}/otp")->assertOk();
        Http::assertSentCount(1);

        // Pull the code out of the email sent to the admin.
        $code = null;
        Http::assertSent(function ($request) use (&$code) {
            preg_match_all('/>(\d)</', $request['htmlContent'], $m);
            $code = implode('', $m[1]);

            return $request['to'][0]['email'] === 'admin@example.com';
        });

        $wrong = $code === '000000' ? '111111' : '000000';
        $this->postJson("/admin/users/delete/{$target->user_id}", ['code' => $wrong])->assertStatus(422);
        $this->assertFalse((bool) $target->fresh()->is_deleted);

        $this->postJson("/admin/users/delete/{$target->user_id}", ['code' => $code])->assertOk();
        $this->assertTrue((bool) $target->fresh()->is_deleted);

        // Code is single-use.
        $this->postJson("/admin/users/delete/{$target->user_id}", ['code' => $code])->assertStatus(422);
    }
}
