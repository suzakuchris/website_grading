<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_Type extends Model
{
    use HasFactory;
    protected $table = 'mst_company_type';
    protected $primaryKey = 'type_id';
    public $incrementing = true;
}
