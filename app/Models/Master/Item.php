<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Master\Country;

class Item extends Model
{
    use HasFactory;
    protected $table = 'mst_item';
    protected $primaryKey = 'item_id';
    public $incrementing = true;

    public function country(){
        return $this->hasOne(Country::class, 'id', 'kd_negara');
    }
}
