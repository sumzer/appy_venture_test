<?php

namespace Tests\Feature;

use App\Enums\LoadStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoadSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private string $today;
    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(now());
        $this->today = now()->toDateString();
        $this->tomorrow = now()->addDay()->toDateString();
    }

    private function as(User $user): void
    {
        Sanctum::actingAs($user);
    }

    private function makeShipper(): User
    {
        return User::factory()->create(['role' => Role::Shipper]);
    }

    private function makeCarrier(): User
    {
        return User::factory()->create(['role' => Role::Carrier]);
    }

    private function createLoad(User $shipper, array $overrides = []): int
    {
        $this->as($shipper);

        $payload = array_merge([
            'origin_country' => 'ESP',
            'origin_city' => 'Langton',
            'destination_country' => 'NLD',
            'destination_city' => 'Gutmannfurt',
            'pickup_date' => $this->today,
            'delivery_date' => $this->tomorrow,
            'weight_kg' => 1000,
            'price_expectation' => 1300,
            'status' => LoadStatus::Draft->value,
        ], $overrides);

        return $this->postJson('/api/loads', $payload)->assertCreated()->json('data.id');
    }

    private function etagOfLoad(int $loadId): string
    {
        return $this->getJson("/api/loads/{$loadId}")
            ->assertOk()
            ->headers->get('ETag');
    }

    public function test_owner_soft_deletes_load_and_subsequent_calls_404(): void
    {
        $shipper = $this->makeShipper();
        $loadId = $this->createLoad($shipper);

        $etag = $this->etagOfLoad($loadId);

        $this->as($shipper);
        $this->deleteJson("/api/loads/{$loadId}", [], ['If-Match' => $etag])
            ->assertNoContent();

        $this->assertSoftDeleted('loads', ['id' => $loadId]);

        $this->getJson("/api/loads/{$loadId}")->assertNotFound();

        $this->deleteJson("/api/loads/{$loadId}", [], ['If-Match' => $etag])->assertNotFound();
    }

    public function test_carrier_cannot_delete_load(): void
    {
        $owner = $this->makeShipper();
        $carrier = $this->makeCarrier();
        $loadId = $this->createLoad($owner);
        $etag = $this->etagOfLoad($loadId);

        $this->as($carrier);
        $this->deleteJson("/api/loads/{$loadId}", [], ['If-Match' => $etag])
            ->assertForbidden();
    }

    public function test_other_shipper_cannot_delete_load(): void
    {
        $owner = $this->makeShipper();
        $other = $this->makeShipper();
        $loadId = $this->createLoad($owner);
        $etag = $this->etagOfLoad($loadId);

        $this->as($other);
        $this->deleteJson("/api/loads/{$loadId}", [], ['If-Match' => $etag])
            ->assertForbidden();
    }
}
