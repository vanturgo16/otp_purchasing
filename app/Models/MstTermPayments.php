<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstTermPayments extends Model
{
    use HasFactory;
    protected $table = 'master_term_payments';
    protected $guarded=[
        'id'
    ];
}
