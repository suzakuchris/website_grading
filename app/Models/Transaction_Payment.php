<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Transaction_Payment_Attachment;

class Transaction_Payment extends Model
{
    use HasFactory;
    protected $table = 'transaction_payment';
    protected $primaryKey = 'payment_id';
    public $incrementing = true;

    public function images(){
        return $this->hasMany(Transaction_Payment_Attachment::class, 'payment_id', 'payment_id');
    }
}
