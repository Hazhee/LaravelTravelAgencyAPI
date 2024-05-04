<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_travel_slug_returns_correct_tours()
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_shown_correctly()
    {
        $travel = Travel::factory()->create();
        Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.45]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '123.45']);

    }

    public function test_tours_list_returns_pagination()
    {
        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonCount(15, 'data');
    }

    public function test_tours_list_sorts_by_price_correctly()
    {
        $travel = Travel::factory()->create();
        Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 100]);
        Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 200]);
        Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 300]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc&priceFrom=100&priceTo=300');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.price', '100.00');
        $response->assertJsonPath('data.3.price', '200.00');
        $response->assertJsonPath('data.6.price', '300.00');
    }

    // public function test_tours_list_filters_by_price_correctly()
    // {
    //     $travel = Travel::factory()->create();
    //     Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 100]);
    //     Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 200]);
    //     Tour::factory(3)->create(['travel_id' => $travel->id, 'price' => 300]);

    //     $response = $this->get("/api/v1/travels/".$travel->slug."/tours?priceFrom=200&priceTo=300&sortBy=price&sortOrder=asc");

    //     $response->assertStatus(200);
    //     $response->assertJsonCount(3, 'data');
    //     $response->assertJsonPath('data.0.price', "200.00");
    //     $response->assertJsonPath('data.2.price', "300.00");
    // }

    public function test_tours_list_filters_by_date_correctly()
    {
        $travel = Travel::factory()->create();
        Tour::factory(3)->create(['travel_id' => $travel->id, 'starting_date' => '2021-01-01']);
        Tour::factory(3)->create(['travel_id' => $travel->id, 'starting_date' => '2021-02-01']);
        Tour::factory(3)->create(['travel_id' => $travel->id, 'starting_date' => '2021-03-01']);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours?dateFrom=2021-02-01&dateTo=2021-03-01');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('data.0.starting_date', '2021-02-01');
        $response->assertJsonPath('data.2.starting_date', '2021-03-01');
    }
}
