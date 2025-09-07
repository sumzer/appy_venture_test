<?php

namespace Tests\Feature;

use App\Enums\BidStatus;
use App\Enums\LoadStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoadBidBookingTest extends TestCase
{
    use RefreshDatabase;

    private string $today;
    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now());
        $this->today = Carbon::now()->toDateString();
        $this->tomorrow = Carbon::now()->addDay()->toDateString();
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

    private function createOpenLoad(User $shipper, array $overrides = []): int
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
            'status' => LoadStatus::Open->value,
        ], $overrides);

        return $this->postJson('/api/loads', $payload)->assertCreated()->json('data.id');
    }

    private function bidAs(User $carrier, int $loadId, int $amount, ?string $message = null): int
    {
        $this->as($carrier);

        return $this->postJson("/api/loads/{$loadId}/bids", [
            'amount' => $amount,
            'message' => $message,
        ])->assertCreated()->json('data.id');
    }

    private function etagOfLoad(int $loadId): string
    {
        return $this->getJson("/api/loads/{$loadId}")
            ->assertOk()
            ->headers->get('ETag');
    }

    private function acceptAs(User $shipper, int $bidId, int $loadId)
    {
        $etag = $this->etagOfLoad($loadId);
        $this->as($shipper);

        return $this->postJson("/api/bids/{$bidId}/accept", [], [
            'If-Match' => $etag,
        ]);
    }

    public function test_happy_path_flow(): void
    {
        $shipper = $this->makeShipper();
        $carrierA = $this->makeCarrier();
        $carrierB = $this->makeCarrier();

        $loadId = $this->createOpenLoad($shipper);

        $bidA = $this->bidAs($carrierA, $loadId, 1200, 'Can take it tomorrow morning');
        $bidB = $this->bidAs($carrierB, $loadId, 1250);

        $accept = $this->acceptAs($shipper, $bidA, $loadId)->assertOk();
        $accept->assertJsonPath('data.status', LoadStatus::Booked->value);

        $this->assertDatabaseHas('bookings', [
            'load_id' => $loadId,
            'bid_id' => $bidA,
            'carrier_id' => $carrierA->id,
        ]);
        $this->assertDatabaseHas('bids', ['id' => $bidA, 'status' => BidStatus::Accepted->value]);
        $this->assertDatabaseHas('bids', ['id' => $bidB, 'status' => BidStatus::Rejected->value]);
    }

    public function test_authz_carrier_cannot_create_load(): void
    {
        $carrier = $this->makeCarrier();
        $this->as($carrier);

        $this->postJson('/api/loads', [
            'origin_country' => 'ESP',
            'origin_city' => 'Langton',
            'destination_country' => 'NLD',
            'destination_city' => 'Gutmannfurt',
            'pickup_date' => $this->today,
            'delivery_date' => $this->tomorrow,
            'weight_kg' => 1000,
            'status' => LoadStatus::Open->value,
        ])->assertForbidden();
    }

    public function test_authz_wrong_shipper_cannot_accept(): void
    {
        $owner = $this->makeShipper();
        $other = $this->makeShipper();
        $carrier = $this->makeCarrier();

        $loadId = $this->createOpenLoad($owner);
        $bidId = $this->bidAs($carrier, $loadId, 900);

        $this->as($other);
        $etag = $this->etagOfLoad($loadId);

        $this->postJson("/api/bids/{$bidId}/accept", [], ['If-Match' => $etag])
            ->assertForbidden();
    }

    public function test_business_prevents_duplicate_bid(): void
    {
        $shipper = $this->makeShipper();
        $carrier = $this->makeCarrier();

        $loadId = $this->createOpenLoad($shipper);

        $this->bidAs($carrier, $loadId, 800);
        $this->as($carrier);
        $this->postJson("/api/loads/{$loadId}/bids", ['amount' => 900])->assertStatus(409);
    }

    public function test_business_prevents_accept_when_already_booked(): void
    {
        $shipper = $this->makeShipper();
        $carrierA = $this->makeCarrier();
        $carrierB = $this->makeCarrier();

        $loadId = $this->createOpenLoad($shipper);

        $bidA = $this->bidAs($carrierA, $loadId, 1000);
        $bidB = $this->bidAs($carrierB, $loadId, 1100);

        $this->acceptAs($shipper, $bidA, $loadId)->assertOk();

        $this->acceptAs($shipper, $bidB, $loadId)->assertForbidden();
    }

    public function test_filters_returns_only_open(): void
    {
        $shipper = $this->makeShipper();

        $this->createOpenLoad($shipper, ['status' => LoadStatus::Draft->value]);

        $loadOpen = $this->createOpenLoad($shipper);

        $res = $this->getJson('/api/loads?status=' . LoadStatus::Open->value)
            ->assertOk()
            ->json('data');

        $this->assertIsArray($res);
        $this->assertGreaterThan(0, count($res));
        foreach ($res as $row) {
            $this->assertSame(LoadStatus::Open->value, $row['status']);
        }
    }
}
