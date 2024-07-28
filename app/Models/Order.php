<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table='orders';

    public function order_items()
    {
        return $this->hasMany(Order_detail::class,'order_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
