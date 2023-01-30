<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $table  = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'supplier_id',
        'image',
        'product_name',
        'description',
        'base_price',
        'selling_price',
        'quantity',
        'unit',
        'is_active',
        'created_by'
    ];


    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brands()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'product_id', 'id');
    }

    public function product_stock_in()
    {
        return $this->hasMany(StockIn::class, 'product_id', 'id');
    }

    public function product_stock_return()
    {
        return $this->hasMany(StockReturn::class, 'product_id', 'id');
    }

}
