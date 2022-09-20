<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::create('cashier_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('shift_id')->nullable();
            $table->double('amount_got')->default(0);;
            $table->double('amount_deliver')->default(0);;
            $table->double('sales_amount')->default(0);
            $table->integer('approved_by')->default(0);
            $table->integer('warehouse_id')->default(1);
            $table->date('date');
            $table->time('time_closed')->nullable();
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
        Schema::dropIfExists('cashier_logs');
    }
}
