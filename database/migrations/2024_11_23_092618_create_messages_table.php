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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            $table->unsignedBigInteger('sender_id'); //or uuid
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('receiver_id'); // or uuid
            $table->foreign('receiver_id')->references('id')->on('recipients')->onDelete('cascade');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();

           // $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');

            $table->enum('status', ['sent', 'delivered', 'read', 'failed', 'received'])->default('sent');

            $table->string('message_id')->unique()->nullable();

            $table->timestamp('receiver_deleted_at')->nullable();
            $table->timestamp('sender_deleted_at')->nullable();

            $table->text('body')->nullable();
           // $table->json('metadata')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
