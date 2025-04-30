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
            $table->string('invoice_no')->nullable();
            $table->string('export_bill_no')->nullable();
            $table->decimal('amount_usd', 15, 2)->default(0);
            $table->decimal('realized_value', 15, 2)->default(0);
            $table->integer('g_qty_pcs')->default(0);
            $table->date('date_of_realized')->nullable();
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
