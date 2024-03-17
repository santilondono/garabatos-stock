<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryDetail extends Model
{
    use HasFactory;

    protected $table = 'entries_detail';

    protected $primaryKey = 'entry_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'product_id',
        'quantity_entered',
        'purchase_price'
    ];
}
