<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSalesContractsTableForCalculation extends Migration
{
    public function up()
    {
        Schema::table('sales_contracts', function (Blueprint $table) {
            // Change data types for calculation fields
            $table->decimal('Revised_value', 18, 2)->nullable()->change();
            $table->decimal('Revised_qty_pcs', 18, 2)->nullable()->change();
            $table->decimal('ud_value', 18, 2)->nullable()->change();
            $table->decimal('ud_qty_pcs', 18, 2)->nullable()->change();
            $table->decimal('used_value', 18, 2)->nullable()->change();

            // Add history fields if not exists
            if (!Schema::hasColumn('sales_contracts', 'revised_history')) {
                $table->json('revised_history')->nullable();
            }

            if (!Schema::hasColumn('sales_contracts', 'ud_history')) {
                $table->json('ud_history')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_contracts', function (Blueprint $table) {
            // Revert to original string types
            $table->string('Revised_value')->nullable()->change();
            $table->string('Revised_qty_pcs')->nullable()->change();
            $table->string('ud_value')->nullable()->change();
            $table->string('ud_qty_pcs')->nullable()->change();
            $table->string('used_value')->nullable()->change();

            // Remove history fields if they were added in this migration
            if (Schema::hasColumn('sales_contracts', 'revised_history')) {
                $table->dropColumn('revised_history');
            }

            if (Schema::hasColumn('sales_contracts', 'ud_history')) {
                $table->dropColumn('ud_history');
            }
        });
    }
}
