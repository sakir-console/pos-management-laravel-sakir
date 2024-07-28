<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;
    protected $table='counters';
    public function counter_items()
    {
        return $this->hasMany(Counter_detail::class,'counter_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

}
