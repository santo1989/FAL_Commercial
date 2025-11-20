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

    public function btbLc()
    {
        // Return the first BTB/LC for this contract. If you have an LC number on SalesExport,
        // update this relation to match by LC number as well.
        return $this->hasOne(BtbLc::class, 'contract_id', 'contract_id');
    }
}
