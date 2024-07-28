<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table='items';
    public function cats()
    {
        return $this->belongsToMany(Item_category::class,'item_category_item');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function imgs()
    {
        return $this->hasMany(Item_image::class,'item_id');
    }
    public function qty()
    {
        return $this->hasMany(Item_qty::class,'item_id');
    }
}
