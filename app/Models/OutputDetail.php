<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputDetail extends Model
{
    use HasFactory;

    protected $table = 'outputs_detail';

    protected $primaryKey = 'output_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'output_id',
        'product_id',
        'quantity_taken_out'
    ];
}
