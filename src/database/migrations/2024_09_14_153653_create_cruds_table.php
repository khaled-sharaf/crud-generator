<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    protected $connection = 'crud_sqlite';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cruds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module');
            $table->string('frontend_module')->nullable();
            $table->string('file_name')->nullable(); // when create crud by command line only
            $table->json('old_config')->nullable();
            $table->json('current_config')->nullable(); // get config from here when create crud by dashboard
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cruds');
    }
};
