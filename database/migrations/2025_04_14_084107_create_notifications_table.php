<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\text;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                         'video_reported',
                         'video_approved',
                         'comment_flagged',
                         'new_follower',
                         'engagement_update' ,
                         'credibility_alert'
                       ]);
            $table->unsignedBigInteger('reference_id')->nullable(); 
            $table->string('title', 255);
            $table->text('message')->nullable();       
            $table->string('thumbnail_url', 255)->nullable();       
            $table->boolean('is_read')->default(false);       
            $table->boolean('email_sent')->default(false);         
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('type');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
