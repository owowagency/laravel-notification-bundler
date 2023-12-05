<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_bundles', function (Blueprint $table) {
            $table->uuid();
            $table->string('channel');
            $table->string('bundle_identifier');
            $table->longText('payload');

            $table->timestamp('created_at')->nullable();
        });
    }
};
