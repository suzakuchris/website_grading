<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company_Detail_Type;

class Company_Detail extends Model
{
    use HasFactory;
    protected $table = 'company_detail';
    protected $primaryKey = 'detail_id';
    public $incrementing = true;

    public function type(){
        return $this->hasOne(Company_Detail_Type::class, 'type_id', 'detail_type');
    }
}
