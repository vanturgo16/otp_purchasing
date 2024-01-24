<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitions extends Model
{
    use HasFactory;
    protected $table = 'purchase_requisitions';
    protected $guarded=[
        'id'
    ];
}
