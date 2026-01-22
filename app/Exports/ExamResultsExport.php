<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        return $this->exam->attempts()
            ->with('student')
            ->where('status', 'submitted')
            ->orderBy('final_score', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Skor',
            'Nilai',
            'Status',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi',
            'Pelanggaran',
            'Terkait Kecurangan'
        ];
    }

    public function map($attempt): array
    {
        return [
            $attempt->id,
            $attempt->student->nis ?? '-',
            $attempt->student->user->name,
            $attempt->score ?? 0,
            $attempt->final_score ?? 0,
            $attempt->final_score >= $this->exam->min_pass_grade ? 'Lulus' : 'Tidak Lulus',
            $attempt->started_at->format('d/m/Y H:i'),
            $attempt->ended_at ? $attempt->ended_at->format('d/m/Y H:i') : '-',
            $attempt->started_at && $attempt->ended_at
                ? $attempt->started_at->diffInMinutes($attempt->ended_at) . ' menit'
                : '-',
            $attempt->violation_count,
            $attempt->is_cheating_detected ? 'Ya' : 'Tidak'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
