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

    public function carrier()
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }

    public function freight()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
