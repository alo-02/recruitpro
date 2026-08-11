<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('RecruitPro');
    }

    public function test_jobs_page_is_public(): void
    {
        $response = $this->get('/jobs');
        $response->assertStatus(200);
    }
}
