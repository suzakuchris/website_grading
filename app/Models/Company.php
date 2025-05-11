<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company_Detail;
use App\Models\Company_Type;

class Company extends Model
{
    use HasFactory;
    protected $table = 'mst_company';
    protected $primaryKey = 'company_id';
    public $incrementing = true;

    public function details(){
        return $this->hasMany(Company_Detail::class, 'detail_company', 'company_id');
    }

    public function type(){
        return $this->hasOne(Company_Type::class, 'type_id', 'company_type');
    }
}
