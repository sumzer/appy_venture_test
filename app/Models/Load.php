<?php
namespace App\Models;

use App\Enums\LoadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\Alpha3Country;
use Illuminate\Database\Eloquent\Builder;

class Load extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shipper_id',
        'origin_country',
        'origin_city',
        'destination_country',
        'destination_city',
        'pickup_date',
        'delivery_date',
        'weight_kg',
        'price_expectation',
        'status',
        'version'
    ];
    protected $casts = [
        'pickup_date' => 'date',
        'delivery_date' => 'date',
        'status' => LoadStatus::class,
        'version' => 'integer',
        'origin_country' => Alpha3Country::class,
        'destination_country' => Alpha3Country::class,
    ];

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function etag(): string
    {
        return 'W/"load-' . $this->id . '-' . $this->version . '"';
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $map = [
            'status' => fn(Builder $query, $val) => $query->where('status', $val),
            'origin_country' => fn(Builder $query, $val) => $query->where('origin_country', $val),
            'destination_country' => fn(Builder $query, $val) => $query->where('destination_country', $val),

            'pickup_from' => fn(Builder $query, $val) => $query->whereDate('pickup_date', '>=', $val),
            'pickup_to' => fn(Builder $query, $val) => $query->whereDate('pickup_date', '<=', $val),
            'delivery_from' => fn(Builder $query, $val) => $query->whereDate('delivery_date', '>=', $val),
            'delivery_to' => fn(Builder $query, $val) => $query->whereDate('delivery_date', '<=', $val),
        ];

        foreach ($filters as $key => $value) {
            if ($value === null || $value === '')
                continue;
            if (isset($map[$key])) {
                $map[$key]($query, $value);
            }
        }
        return $query;
    }

    public function acceptedBid()
    {
        return $this->hasOne(\App\Models\Bid::class)
            ->where('status', \App\Enums\BidStatus::Accepted->value);
    }
}
