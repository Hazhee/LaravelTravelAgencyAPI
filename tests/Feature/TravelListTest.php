<?php

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelListTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_list_returns_paginated_data_correctly(): void
    {
        Travel::factory()->count(16)->create(['is_public' => true]);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonCount(15, 'data');
    }

    public function test_travel_list_shows_only_public_records(): void
    {
        $public_travel = Travel::factory()->create(['is_public' => true]);
        Travel::factory()->create(['is_public' => false]);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', $public_travel->name);
        $response->assertJsonCount(1, 'data');
    }
}
