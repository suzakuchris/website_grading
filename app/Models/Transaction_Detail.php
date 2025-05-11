<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\Company_Detail;
use App\Models\Master\Item;
use App\Models\Master\Country;
use App\Models\Master\Material;
use App\Models\Master\MSPF;

class Transaction_Detail extends Model
{
    use HasFactory;
    protected $table = 'transaction_detail';
    protected $primaryKey = 'detail_id';
    public $incrementing = true;

    public function company(){
        return $this->hasOne(Company::class, 'company_id', 'company_id');
    }

    public function tier(){
        return $this->hasOne(Company_Detail::class, 'detail_id', 'company_detail_id');
    }

    public function item(){
        return $this->hasOne(Item::class, 'item_id', 'item_id');
    }

    public function country(){
        return $this->hasOne(Country::class, 'id', 'kd_negara');
    }

    public function material(){
        return $this->hasOne(Material::class, 'material_id', 'detail_material');
    }

    public function mspf(){
        return $this->hasOne(MSPF::class, 'row_id', 'detail_mspf');
    }
}
