<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesImport extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class, 'contract_id');
    }
    public function salesExport()
    {
        return $this->hasMany(SalesExport::class, 'contract_id', 'contract_id');
    }

    
}
