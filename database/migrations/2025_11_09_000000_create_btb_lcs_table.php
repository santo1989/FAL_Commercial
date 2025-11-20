<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('btb_lcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained('sales_contracts')->nullOnDelete();
            $table->foreignId('import_id')->nullable()->constrained('sales_imports')->nullOnDelete();

            $table->string('btb_lc_no')->nullable()->index();
            $table->date('date')->nullable();
            $table->string('bank_name')->nullable();

            $table->date('aceptence_date')->nullable();
            $table->decimal('aceptence_value', 15, 2)->nullable();
            $table->string('aceptence_type')->nullable(); // DP/Sight/USENCE/EDF/UPAS

            // tenor information
            $table->integer('tenor_days')->nullable(); // number of days
            $table->integer('tenor_date_of')->nullable(); // day number if needed

            $table->date('mature_date')->nullable(); // aceptence_date + tenor_days (if present)

            $table->date('date_of_payment_to_supplier_by_bank')->nullable();

            $table->date('repayment_date')->nullable();
            $table->decimal('repayment_value', 15, 2)->nullable();

            $table->decimal('closing_balance', 15, 2)->nullable();

            $table->string('proclument_type')->nullable(); // local / overseas
            $table->string('import_type')->nullable(); // Goods / Services

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('btb_lcs');
    }
};
