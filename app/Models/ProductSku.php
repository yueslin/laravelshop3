<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = ['description', 'price', 'stock','product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productAttribute()
    {
        return $this->belongsToMany(ProductAttributes::class,'product_attribute_sku','sku_id','attribute_id')
            ->withPivot('val');
    }



}
