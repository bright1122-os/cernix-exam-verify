<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examiners', function (Blueprint $table) {
            if (! Schema::hasColumn('examiners', 'admin_user_id')) {
                $table->unsignedBigInteger('admin_user_id')->nullable()->index()->after('role');
            }

            if (! Schema::hasColumn('examiners', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->index()->after('is_active');
            }
        });

        Schema::table('audit_log', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_log', 'target_type')) {
                $table->string('target_type')->nullable()->after('action');
            }

            if (! Schema::hasColumn('audit_log', 'target_id')) {
                $table->string('target_id')->nullable()->after('target_type');
            }

            if (! Schema::hasColumn('audit_log', 'before_values')) {
                $table->json('before_values')->nullable()->after('target_id');
            }

            if (! Schema::hasColumn('audit_log', 'after_values')) {
                $table->json('after_values')->nullable()->after('before_values');
            }

            if (! Schema::hasColumn('audit_log', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('metadata');
            }

            if (! Schema::hasColumn('audit_log', 'device_fp')) {
                $table->string('device_fp')->nullable()->after('ip_address');
            }

            if (! Schema::hasColumn('audit_log', 'trace_id')) {
                $table->string('trace_id')->nullable()->after('device_fp');
            }

            if (! Schema::hasColumn('audit_log', 'session_id')) {
                $table->unsignedBigInteger('session_id')->nullable()->after('trace_id');
            }
        });

        DB::statement('CREATE INDEX IF NOT EXISTS audit_log_actor_type_actor_id_index ON audit_log (actor_type, actor_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_log_action_timestamp_index ON audit_log (action, timestamp)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_log_target_type_target_id_index ON audit_log (target_type, target_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_log_session_id_timestamp_index ON audit_log (session_id, timestamp)');
        DB::statement('CREATE INDEX IF NOT EXISTS examiners_admin_user_id_index ON examiners (admin_user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS examiners_last_active_at_index ON examiners (last_active_at)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS audit_log_actor_type_actor_id_index');
        DB::statement('DROP INDEX IF EXISTS audit_log_action_timestamp_index');
        DB::statement('DROP INDEX IF EXISTS audit_log_target_type_target_id_index');
        DB::statement('DROP INDEX IF EXISTS audit_log_session_id_timestamp_index');
        DB::statement('DROP INDEX IF EXISTS examiners_admin_user_id_index');
        DB::statement('DROP INDEX IF EXISTS examiners_last_active_at_index');

        Schema::table('audit_log', function (Blueprint $table) {
            $columns = [
                'target_type',
                'target_id',
                'before_values',
                'after_values',
                'ip_address',
                'device_fp',
                'trace_id',
                'session_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('audit_log', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('examiners', function (Blueprint $table) {
            if (Schema::hasColumn('examiners', 'admin_user_id')) {
                $table->dropColumn('admin_user_id');
            }

            if (Schema::hasColumn('examiners', 'last_active_at')) {
                $table->dropColumn('last_active_at');
            }
        });
    }
};
