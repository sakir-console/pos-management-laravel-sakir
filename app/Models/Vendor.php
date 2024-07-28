<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table='vendors';

    //protected $visible = ['id','name'];

    protected $fillable = [
        'id',
        'email',
        'name'
    ];

    public function vendor_category()
    {
        return $this->belongsTo(Vendor_cat::class,'vendor_cat_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class,'division_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class,'district_id');
    }


    public function native()
    {
        return $this->belongsTo(Native::class,'native_id');
    }



}
