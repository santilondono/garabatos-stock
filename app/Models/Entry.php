<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $table = 'entries';

    protected $primaryKey = 'entry_id';

    public $timestamps = false;

    protected $fillable = [
        'entry_date',
        'user_id',
        'is_comming',
    ];
}
