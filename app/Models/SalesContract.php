<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesContract extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function salesExport()
    {
        return $this->hasMany(SalesExport::class, 'contract_id', 'id');
    }
    public function salesImport()
    {
        return $this->hasMany(SalesImport::class, 'contract_id', 'id');
    }
}
