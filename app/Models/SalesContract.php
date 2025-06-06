<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesContract extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        // other casts...
        'ud_history'      => 'array',    // ← tell Laravel this is JSON
        'revised_history' => 'array',
        'ud_date'         => 'date',
        'contract_date'   => 'date',
        'Revised_Contract_details' => 'array', // Also cast other JSON fields if needed
    ];
   
    public function imports() // Changed from salesImport()
    {
        return $this->hasMany(SalesImport::class, 'contract_id');
    }

    public function exports() // Changed from salesExport()
    {
        return $this->hasMany(SalesExport::class, 'contract_id');
    }

    // Add these to your existing model

    public function getExportValueAttribute()
    {
        return $this->exports->sum('amount_usd') ?? 0;
    }

    public function getFabricsValueAttribute()
    {
        return $this->imports->sum('fabric_value') ?? 0;
    }

    public function getAccessoriesValueAttribute()
    {
        return $this->imports->sum('accessories_value') ?? 0;
    }

    public function getPrintEmbValueAttribute()
    {
        return $this->imports->sum('print_emb_value') ?? 0;
    }
    
}
