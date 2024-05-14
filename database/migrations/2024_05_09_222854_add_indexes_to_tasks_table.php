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
        Schema::table('task', function (Blueprint $table) {
            if (!Schema::hasTable('task') || !Schema::hasIndex('task', 'task_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasTable('task') || !Schema::hasIndex('task', 'task_priority_index')) {
                $table->index('priority');
            }
            if (!Schema::hasTable('task') || !Schema::hasIndex('task', 'task_created_at_index')) {
                $table->index('created_at');
            }
            if (!Schema::hasTable('task') || !Schema::hasIndex('task', 'task_user_id_index')) {
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', function (Blueprint $table) {
            if (Schema::hasTable('task') && Schema::hasIndex('task', 'task_status_index')) {
                $table->dropIndex(['status']);
            }
            if (Schema::hasTable('task') && Schema::hasIndex('task', 'task_priority_index')) {
                $table->dropIndex(['priority']);
            }
            if (Schema::hasTable('task') && Schema::hasIndex('task', 'task_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
            if (Schema::hasTable('task') && Schema::hasIndex('task', 'task_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
        });
    }
};
