<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABELAS PADRÃO DO LARAVEL (Users, Cache, Jobs)
        // =====================================================================
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // Campo Operacional (sem profile, pois usamos Spatie)
            $table->boolean('is_operator')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Lixeira
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 2. TABELAS DO SPATIE PERMISSIONS
        // =====================================================================
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        // 3. TABELAS DE SUPORTE E LOGS
        // =====================================================================
        
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module');
            $table->string('action');
            $table->json('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 4. TABELAS OPERACIONAIS (Programas, Grade, Timers)
        // =====================================================================

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('default_duration')->default(30);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->integer('duration');
            $table->string('custom_info')->nullable();
            $table->boolean('status_mago')->default(false);
            $table->boolean('status_verification')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('studio_timers', function (Blueprint $table) {
            $table->id();
            $table->dateTime('target_time')->nullable(); // Regressiva Principal
            $table->dateTime('bk_target_time')->nullable(); // Regressiva BK (Comercial)
            $table->string('mode_label')->nullable();
            $table->string('status_color')->default('normal');
            
            // Cronômetro Progressivo
            $table->dateTime('stopwatch_started_at')->nullable();
            $table->integer('stopwatch_accumulated_seconds')->default(0);
            $table->enum('stopwatch_status', ['running', 'paused', 'stopped'])->default('stopped');

            $table->timestamps();
        });

        // Inicializa o timer (ID 1)
        DB::table('studio_timers')->insert([
            'mode_label' => 'AGUARDANDO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. TABELAS DE RH (Escalas e Férias)
        // =====================================================================

        // REMOVIDO: Tabela 'scales' (Não é mais usada)

        Schema::create('scale_shifts', function (Blueprint $table) {
            $table->id();
            // REMOVIDO: scale_id (Não existe mais tabela pai)
            $table->foreignId('user_id')->nullable()->constrained();
            $table->date('date');
            $table->string('name');
            $table->integer('order');
            $table->timestamps();
        });

        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year'); // Ano de referência (2025, 2026...)
            $table->string('mode'); // '30_dias', '15_15', etc
            
            $table->date('period_1_start');
            $table->date('period_1_end');
            $table->date('period_2_start')->nullable();
            $table->date('period_2_end')->nullable();
            $table->date('period_3_start')->nullable();
            $table->date('period_3_end')->nullable();
            
            $table->string('status')->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropar na ordem inversa (filhas primeiro)
        Schema::dropIfExists('vacations');
        Schema::dropIfExists('scale_shifts');
        // Schema::dropIfExists('scales'); // Removido
        Schema::dropIfExists('studio_timers');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('settings');
        
        // Spatie Tables
        $tableNames = config('permission.table_names');
        if (!empty($tableNames)) {
            Schema::drop($tableNames['role_has_permissions']);
            Schema::drop($tableNames['model_has_roles']);
            Schema::drop($tableNames['model_has_permissions']);
            Schema::drop($tableNames['roles']);
            Schema::drop($tableNames['permissions']);
        }

        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};