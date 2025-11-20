<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BtbLc extends Model
{
    use HasFactory;

    protected $table = 'btb_lcs';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'aceptence_date' => 'date',
        'mature_date' => 'date',
        'date_of_payment_to_supplier_by_bank' => 'date',
        'repayment_date' => 'date',
        'aceptence_value' => 'decimal:2',
        'repayment_value' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(SalesContract::class, 'contract_id');
    }

    public function import()
    {
        return $this->belongsTo(SalesImport::class, 'import_id');
    }

    /**
     * Ensure mature_date is computed if aceptecence_date + tenor_days provided.
     */
    public function computeMatureDate()
    {
        if ($this->aceptence_date && $this->tenor_days) {
            return Carbon::parse($this->aceptence_date)->addDays(intval($this->tenor_days));
        }
        return $this->mature_date;
    }
}
