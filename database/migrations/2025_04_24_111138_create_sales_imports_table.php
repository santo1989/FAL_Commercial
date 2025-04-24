<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('sales_contracts');
            $table->string('btb_lc_no')->nullable();
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('fabric_value', 15, 2)->default(0);
            $table->decimal('accessories_value', 15, 2)->default(0);
            $table->decimal('fabric_qty_kg', 15, 2)->default(0);
            $table->decimal('accessories_qty', 15, 2)->default(0);$table->string('print_emb_qty')->default(0);
            $table->decimal('print_emb_value', 15, 2)->default(0);
            // $table->string('data_1')->nullable();
            // $table->string('data_2')->nullable();
            // $table->string('data_3')->nullable();
            // $table->string('data_4')->nullable();
            // $table->string('data_5')->nullable();
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
        Schema::dropIfExists('sales_imports');
    }
}
