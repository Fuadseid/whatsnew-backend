<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');;
            $table->foreignId('video_id')->constrained()->onDelete('cascade');;
            $table->string('algorithm_version', 50)->nullable();
            $table->float('score')->nullable();
            $table->enum('reason', [
                      'interest', 
                      'match',
                      'following',
                      'trending',
                      'popular',
                      'history_based',
                      'favorites_based',
                      'high_credibility'
                    ]);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
