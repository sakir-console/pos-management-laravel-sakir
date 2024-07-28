<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter_detail extends Model
{
    use HasFactory;
    protected $fillable = [
        'counter_code'
    ];


    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
