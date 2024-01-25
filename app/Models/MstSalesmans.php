<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstSalesmans extends Model
{
    use HasFactory;
    protected $table = 'master_salesmen';
    protected $guarded=[
        'id'
    ];
}
