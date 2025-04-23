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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->enum('reason', [
                'misinformation',
                'hate_speech',
                'harassment',
                'violence',
                'copyright',
                'other'
            ]);
            $table->text('details');
            $table->enum('status', [
                'pending',
                'under_review',
                'resolved',
                'dismissed'
            ])->default('pending');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps(); 
            
            // Performance indexes
            $table->index('video_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['reporter_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
