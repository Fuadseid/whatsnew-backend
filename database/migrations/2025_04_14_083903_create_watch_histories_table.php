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
        Schema::create('watch_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->integer('watched_seconds')->default(0);
            $table->float('credibility_score', 8, 2)->nullable();
            $table->timestamp('last_watched_at')->useCurrent();
            
            // Composite unique constraint - one record per user-video pair
            $table->unique(['user_id', 'video_id']);

            // Indexes for performance
            $table->index('user_id');
            $table->index('video_id');
            $table->index('last_watched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_histories');
    }
};
