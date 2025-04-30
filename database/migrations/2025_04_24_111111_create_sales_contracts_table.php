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
            $table->string('buyer_name')->nullable();
            $table->string('buyer_id')->nullable();
            $table->string('sales_contract_no')->unique();
            $table->date('contract_date');
            $table->decimal('sales_cont_value', 18, 2)->default(0);
            $table->decimal('sales_contract_value', 18, 0)->default(0);
            $table->json('Revised_Contract_details')->nullable();
            $table->json('revised_history')->nullable();
            $table->string('quantity_pcs')->default(0);
            $table->string('ud_no')->nullable();
            $table->string('ud_date')->nullable();
            $table->string('ud_value')->nullable();
            $table->string('ud_qty_pcs')->nullable();
            $table->string('bank_name')->nullable();
            $table->json('ud_history')->nullable();
            $table->string('data_1')->nullable();
            $table->string('data_2')->nullable();
            $table->string('data_3')->nullable();
            $table->string('data_4')->nullable(); 
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
