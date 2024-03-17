<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_reference',
        'list_description',
        'product_description',
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
        'image'
    ];
}
