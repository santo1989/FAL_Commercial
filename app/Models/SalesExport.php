<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesExport extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class, 'contract_id');
    }

    public function salesImport()
    {
        return $this->hasMany(SalesImport::class, 'contract_id', 'contract_id');
    }
}
