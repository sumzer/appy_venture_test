<?php
namespace App\Models;

use App\Enums\BidStatus;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'load_id',
        'carrier_id',
        'amount',
        'message',
        'status'
    ];
    protected $casts = [
        'status' => BidStatus::class
    ];

    public function getLoadAttribute()
    {
        return $this->loadRelation;
    }

    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }
    public function carrier()
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }
}
