<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description', 512)->nullable()->default(null);
            $table->text('value')->nullable()->default(null);
            $table->text('params')->nullable()->default(null);
            $table->enum('type', ['text', 'textarea', 'image', 'select', 'radio', 'checkbox','blade', 'password']);
            $table->integer('order')->default('1');
            $table->integer('group_id')->index();
            $table->integer('group_sub_id')->default(0);
            $table->boolean('hidden')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
