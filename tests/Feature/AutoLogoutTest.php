<?php

namespace Tests\Feature;

use App\Models\BiometricData;
use App\Models\User;
use App\Services\FacialRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AutoLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_logged_out_after_successful_clock_in()
    {
        // 1. Create a user and biometric data
        $user = User::factory()->create();
        $startEncoding = array_fill(0, 128, 0.1); // Dummy encoding
        
        BiometricData::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'facial_encoding' => $startEncoding,
            'facial_status' => 'captured',
            'facial_captured_at' => now(),
        ]);

        // 2. Mock FacialRecognitionService
        $mockService = Mockery::mock(FacialRecognitionService::class);
        $mockService->shouldReceive('extractEncodingFromBase64')
            ->andReturn(['encoding' => $startEncoding]);
        $mockService->shouldReceive('findBestMatch')
            ->once()
            ->andReturn([
                'user_id' => $user->id,
                'distance' => 0.1,
                'confidence' => 99.9,
                'match' => true
            ]);
            
        $this->app->instance(FacialRecognitionService::class, $mockService);

        // 3. Login as the user (simulating post-registration state)
        $this->actingAs($user);
        $this->assertTrue(Auth::check(), 'User should be logged in initially');

        // 4. Perform clock-in request
        $response = $this->postJson(route('attendance.verify'), [
            'facial_image' => 'data:image/jpeg;base64,dummybase64data',
            'latitude' => 0,
            'longitude' => 0,
            'accuracy' => 0,
        ]);

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // CRITICAL: Assert user is logged out
        $this->assertFalse(Auth::check(), 'User should be logged out after clock-in');
    }
}
