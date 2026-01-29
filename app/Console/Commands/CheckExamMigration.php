<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckExamMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:check-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of exam settings migration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Exam Settings Migration Status...');

        // Cek tabel exam_questions
        $examQuestionsColumns = [
            'enable_timer' => false,
            'time_limit' => false,
            'enable_skip' => false,
            'show_explanation' => false,
            'enable_mark_review' => false,
            'randomize_choices' => false,
            'require_all_options' => false,
        ];

        foreach ($examQuestionsColumns as $column => &$exists) {
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = 'exam_questions'
                AND COLUMN_NAME = ?
            ", [$column]);

            $exists = $result[0]->count > 0;
        }

        // Cek tabel exam_attempts
        $examSettingsExists = DB::select("
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'exam_attempts'
            AND COLUMN_NAME = 'exam_settings'
        ")[0]->count > 0;

        // Tampilkan hasil
        $this->table(
            ['Table', 'Column', 'Status'],
            [
                ...array_map(function ($column, $exists) {
                    return ['exam_questions', $column, $exists ? '✅ EXISTS' : '❌ MISSING'];
                }, array_keys($examQuestionsColumns), array_values($examQuestionsColumns)),
                ['exam_attempts', 'exam_settings', $examSettingsExists ? '✅ EXISTS' : '❌ MISSING'],
            ]
        );

        // Hitung status
        $totalColumns = count($examQuestionsColumns) + 1;
        $existingColumns = count(array_filter($examQuestionsColumns)) + ($examSettingsExists ? 1 : 0);

        if ($existingColumns === $totalColumns) {
            $this->info("✅ All {$totalColumns} columns are properly migrated!");
        } else {
            $this->error("⚠️  Missing " . ($totalColumns - $existingColumns) . " columns!");
            $this->line('Run: php artisan exam:setup-settings');
        }
    }
}
