<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetailsSMT extends Model
{
    use HasFactory;
    protected $table = 'purchase_order_details_smt';
    protected $guarded=[
        'id'
    ];
}
