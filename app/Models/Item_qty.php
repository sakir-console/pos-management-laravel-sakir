<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item_qty extends Model
{
    use HasFactory;

    protected $table='item_quantities';
    protected $fillable=['qty','price'];

    protected $hidden=['id','item_id','created_at','updated_at'];
}
