<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_detail';

    protected $primaryKey = 'sale_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity_sold',
        'sale_price',
    ];
}
