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
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->string('action_url')->nullable()->after('message');
            $table->string('resource_type')->nullable()->after('action_url');
            $table->unsignedBigInteger('resource_id')->nullable()->after('resource_type');

            $table->index(
                ['user_id', 'resource_type', 'resource_id'],
                'notification_logs_resource_lookup',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex('notification_logs_resource_lookup');
            $table->dropColumn([
                'action_url',
                'resource_type',
                'resource_id',
            ]);
        });
    }
};
