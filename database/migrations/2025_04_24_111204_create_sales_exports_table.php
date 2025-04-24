<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('sales_contracts');
            $table->string('invoice_no');
            $table->string('export_bill_no');
            $table->decimal('amount_usd', 15, 2);
            $table->decimal('realized_value', 15, 2);
            $table->integer('g_qty_pcs');
            $table->date('date_of_realized');
            $table->decimal('due_amount_usd', 15, 2)->default(0);
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
        Schema::dropIfExists('sales_exports');
    }
}
