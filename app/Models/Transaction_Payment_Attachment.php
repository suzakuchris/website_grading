<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction_Payment_Attachment extends Model
{
    use HasFactory;
    protected $table = 'transaction_payment_attachment';
    protected $primaryKey = 'attachment_id';
    public $incrementing = true;
}
