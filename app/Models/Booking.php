<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'bid_id',
        'carrier_id',
        'booked_at'
    ];
    protected $casts = [
        'booked_at' => 'datetime'
    ];

    public function getLoadAttribute()
    {
        return $this->loadRelation;
    }

    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }
    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }
    public function carrier()
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }
}
