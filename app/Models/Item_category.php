<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item_category extends Model
{
    use HasFactory;
    protected $table='item_categories';
    protected $fillable=['id','name'];
   protected $hidden=['vendor_id','created_at','updated_at','pivot'];

    public function items()
    {
        return $this->belongsToMany(Item::class,'item_category_item');
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
