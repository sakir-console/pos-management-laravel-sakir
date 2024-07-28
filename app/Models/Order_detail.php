<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_detail extends Model
{
    use HasFactory;
    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

}
