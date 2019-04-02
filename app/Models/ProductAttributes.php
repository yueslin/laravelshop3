<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributes extends Model
{
    protected $fillable = ['product_id', 'name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productSku()
    {
        return $this->belongsToMany(ProductSku::class,'product_attribute_sku','attribute_id','sku_id')
            ->withPivot('val');
    }

}
