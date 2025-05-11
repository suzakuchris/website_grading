<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction_Header extends Model
{
    use HasFactory;
    protected $table = 'transaction_header';
    protected $primaryKey = 'header_id';
    public $incrementing = true;

    public function details(){
        return $this->hasMany(Transaction_Detail::class, 'header_id', 'header_id');
    }

    public function bank_notes(){
        return $this->hasMany(Transaction_Detail::class, 'header_id', 'header_id')->where('detail_type', 0);
    }

    public function coins(){
        return $this->hasMany(Transaction_Detail::class, 'header_id', 'header_id')->where('detail_type', 1);
    }

    public function payments(){
        return $this->hasMany(Transaction_Payment::class, 'header_id', 'header_id');
    }

    public function customer(){
        return $this->hasOne(Customer::class, 'customer_id', 'customer_id');
    }
}
