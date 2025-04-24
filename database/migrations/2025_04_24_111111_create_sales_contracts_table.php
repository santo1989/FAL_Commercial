<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('buyer_name');
            $table->string('sales_contract_no')->unique();
            $table->date('contract_date');
            $table->decimal('sales_cont_value', 18, 2);
            $table->decimal('sales_contract_value', 18, 0);
            $table->json('Revised_Contract_details');
            $table->string('ud_no');
            $table->string('ud_date');
            $table->string('ud_value');
            $table->string('ud_qty_pcs');
            $table->string('bank_name');
            $table->string('data_1');
            $table->string('data_2');
            $table->string('data_3');
            $table->string('data_4'); 
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
        Schema::dropIfExists('sales_contracts');
    }
}
