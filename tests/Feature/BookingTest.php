<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Media $media;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = Media::create([
            'name' => 'Valla Test', 'type' => 'billboard', 'location' => 'San Salvador',
            'dimensions' => '12x4 m', 'price_per_day' => 50, 'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'name' => 'Cliente Test', 'email' => 'test@example.com',
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'media_id' => $this->media->id,
            'customer_id' => $this->customer->id,
            'starts_at' => now()->addDays(10)->format('Y-m-d'),
            'ends_at' => now()->addDays(12)->format('Y-m-d'),
        ], $override);
    }

    public function test_it_calculates_the_total_price_on_the_server(): void
    {
        $this->postJson('/api/bookings', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.total_price', 150)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_it_rejects_overlapping_bookings(): void
    {
        $this->postJson('/api/bookings', $this->payload())->assertCreated();

        $this->postJson('/api/bookings', $this->payload([
            'starts_at' => now()->addDays(11)->format('Y-m-d'),
            'ends_at' => now()->addDays(14)->format('Y-m-d'),
        ]))->assertStatus(422);
    }

    public function test_adjacent_dates_do_not_overlap(): void
    {
        $this->postJson('/api/bookings', $this->payload())->assertCreated();

        $this->postJson('/api/bookings', $this->payload([
            'starts_at' => now()->addDays(13)->format('Y-m-d'),
            'ends_at' => now()->addDays(14)->format('Y-m-d'),
        ]))->assertCreated();
    }

    public function test_cancelled_bookings_free_the_dates(): void
    {
        $id = $this->postJson('/api/bookings', $this->payload())->json('data.id');

        $this->patchJson("/api/bookings/{$id}/status", ['status' => 'cancelled'])->assertOk();

        $this->postJson('/api/bookings', $this->payload())->assertCreated();
    }

    public function test_it_rejects_end_date_before_start_date(): void
    {
        $this->postJson('/api/bookings', $this->payload([
            'starts_at' => now()->addDays(12)->format('Y-m-d'),
            'ends_at' => now()->addDays(10)->format('Y-m-d'),
        ]))->assertStatus(422)->assertJsonValidationErrors('ends_at');
    }

    public function test_it_rejects_inactive_media(): void
    {
        $this->media->update(['status' => 'inactive']);

        $this->postJson('/api/bookings', $this->payload())->assertStatus(422);
    }

    public function test_status_only_accepts_allowed_values(): void
    {
        $id = $this->postJson('/api/bookings', $this->payload())->json('data.id');

        $this->patchJson("/api/bookings/{$id}/status", ['status' => 'invalid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_availability_reflects_existing_bookings(): void
    {
        $this->postJson('/api/bookings', $this->payload())->assertCreated();

        $query = http_build_query([
            'starts_at' => now()->addDays(10)->format('Y-m-d'),
            'ends_at' => now()->addDays(12)->format('Y-m-d'),
        ]);

        $this->getJson("/api/media/{$this->media->id}/availability?{$query}")
            ->assertOk()
            ->assertJsonPath('available', false);
    }
}