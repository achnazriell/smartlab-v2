<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupExamSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:setup-settings {--fresh : Run fresh migration} {--seed : Seed data after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup exam settings migration and update database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Exam Settings Setup...');

        // Option untuk fresh migration
        if ($this->option('fresh')) {
            $this->warn('⚠️  Running fresh migration (will drop all tables)');

            if ($this->confirm('Are you sure you want to drop all tables?')) {
                $this->call('migrate:fresh');
                $this->info('✅ Database migrated fresh');
            } else {
                $this->info('❌ Operation cancelled');
                return;
            }
        } else {
            // Jalankan migration biasa
            $this->call('migrate');
            $this->info('✅ Database migrated');
        }

        // Cek apakah kolom sudah ada
        $this->info('🔍 Checking database columns...');

        $columnsExist = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'exam_questions'
            AND COLUMN_NAME IN (
                'enable_timer', 'time_limit', 'enable_skip',
                'show_explanation', 'enable_mark_review',
                'randomize_choices', 'require_all_options'
            )
        ");

        if (count($columnsExist) === 7) {
            $this->info('✅ All columns already exist in exam_questions table');
        } else {
            $this->warn('⚠️  Some columns are missing, consider running the migration');
        }

        // Check exam_attempts table
        $attemptsColumns = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'exam_attempts'
            AND COLUMN_NAME = 'exam_settings'
        ");

        if (count($attemptsColumns) > 0) {
            $this->info('✅ exam_settings column exists in exam_attempts table');
        } else {
            $this->warn('⚠️  exam_settings column is missing in exam_attempts table');
        }

        // Update models and cache
        $this->info('🔄 Clearing cache...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // Optimize for production
        $this->call('optimize:clear');

        $this->info('🎉 Exam settings setup completed!');

        // Tampilkan summary
        $this->newLine();
        $this->info('📋 Summary of what was added:');
        $this->table(
            ['Table', 'Column', 'Description'],
            [
                ['exam_questions', 'enable_timer', 'Aktifkan timer per soal'],
                ['exam_questions', 'time_limit', 'Batas waktu per soal (detik)'],
                ['exam_questions', 'enable_skip', 'Izinkan skip soal'],
                ['exam_questions', 'show_explanation', 'Tampilkan penjelasan'],
                ['exam_questions', 'enable_mark_review', 'Izinkan tandai review'],
                ['exam_questions', 'randomize_choices', 'Acak pilihan jawaban'],
                ['exam_questions', 'require_all_options', 'Wajib baca semua opsi'],
                ['exam_attempts', 'exam_settings', 'JSON settings ujian'],
            ]
        );
    }
}
