<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    public $timestamps = false;

    protected $fillable = [
        'product_reference',
        'list_description',
        'product_description',
        'purchase_price',
        'sale_price',
        'weight',
        'length',
        'width',
        'height',
        'cubic_meter',
        'quantity',
        'stock'
    ];

    protected $guarded = [
        'gross_revenue',
        'image'
    ];
}
