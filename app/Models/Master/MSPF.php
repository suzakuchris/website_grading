<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSPF extends Model
{
    use HasFactory;
    protected $table = 'mst_mspf';
    protected $primaryKey = 'row_id';
    public $incrementing = true;
}
