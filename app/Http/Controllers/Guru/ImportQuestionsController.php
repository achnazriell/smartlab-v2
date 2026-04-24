<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // PERBAIKAN: Gunakan Writer\Xlsx, bukan Reader\Xlsx
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * ImportQuestionsController
 *
 * Handles import of exam questions from Excel (.xlsx/.xls) and CSV files.
 */
class ImportQuestionsController extends Controller
{
    private const VALID_TYPES = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];

    private function teacherId(): int
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Bukan akun guru');
        return $teacher->id;
    }

    /* ================================================================
     * IMPORT
     * ================================================================ */
    public function import(Request $request, $examId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB
        ]);

        $exam = Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);

        try {
            $path    = $request->file('file')->getRealPath();
            $ext     = strtolower($request->file('file')->getClientOriginalExtension());
            $replace = (bool) $request->boolean('replace');

            $rows = $ext === 'csv'
                ? $this->readCsv($path)
                : $this->readExcel($path);

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'File kosong atau tidak dapat dibaca'], 422);
            }

            DB::beginTransaction();

            if ($replace) {
                // Delete existing questions and their choices
                $exam->questions->each(function ($q) {
                    $q->choices()->delete();
                    $q->delete();
                });
            }

            $startOrder = $replace ? 1 : (ExamQuestion::where('exam_id', $exam->id)->max('order') + 1);
            $imported   = 0;
            $skipped    = 0;

            foreach ($rows as $rowNum => $row) {
                try {
                    $this->processRow($exam->id, $row, $startOrder + $imported);
                    $imported++;
                } catch (\Exception $e) {
                    Log::warning("Import row {$rowNum} skipped: " . $e->getMessage(), ['row' => $row]);
                    $skipped++;
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "{$imported} soal berhasil diimpor",
                'imported' => $imported,
                'skipped'  => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()], 422);
        }
    }

    /* ================================================================
     * PROCESS ONE ROW
     * ================================================================ */
    private function processRow(int $examId, array $row, int $order): void
    {
        // Normalize keys to lowercase without spaces
        $r = [];
        foreach ($row as $k => $v) {
            $key    = strtolower(trim(preg_replace('/\s+/', '_', $k)));
            $r[$key] = is_string($v) ? trim($v) : $v;
        }

        $question = trim($r['pertanyaan'] ?? $r['question'] ?? $r['soal'] ?? '');
        if (empty($question)) throw new \Exception('Kolom pertanyaan kosong');

        $typeRaw = strtoupper(trim($r['tipe'] ?? $r['type'] ?? $r['jenis'] ?? 'PG'));
        if (!in_array($typeRaw, self::VALID_TYPES)) {
            throw new \Exception("Tipe soal '$typeRaw' tidak valid");
        }

        $score       = (int) ($r['skor'] ?? $r['score'] ?? $r['nilai'] ?? 10);
        $explanation = trim($r['pembahasan'] ?? $r['explanation'] ?? '');
        $answer      = trim($r['jawaban'] ?? $r['answer'] ?? $r['kunci'] ?? '');

        $qData = [
            'exam_id'     => $examId,
            'type'        => $typeRaw,
            'question'    => $question,
            'score'       => max(0, $score),
            'explanation' => $explanation,
            'order'       => $order,
        ];

        switch ($typeRaw) {
            case 'PG':
            case 'DD':
                $options = $this->extractOptions($r);
                if (count(array_filter($options)) < 2) throw new \Exception('PG minimal 2 opsi');
                $correctLabel  = strtoupper(trim($answer));
                $correctIndex  = ord($correctLabel) - ord('A');
                $q = ExamQuestion::create($qData);
                $this->saveChoices($q->id, $options, [$correctIndex]);
                break;

            case 'PGK':
                $options = $this->extractOptions($r);
                if (count(array_filter($options)) < 2) throw new \Exception('PGK minimal 2 opsi');
                $correctLabels  = array_map('trim', explode(',', strtoupper($answer)));
                $correctIndexes = array_map(fn($l) => ord($l) - ord('A'), $correctLabels);
                $q = ExamQuestion::create($qData);
                $this->saveChoices($q->id, $options, $correctIndexes);
                break;

            case 'BS':
                $bsAnswer = strtolower(trim($answer));
                // Accept variants: benar/true/1 → benar; salah/false/0 → salah
                if (in_array($bsAnswer, ['benar', 'true', '1', 'ya', 'yes'])) {
                    $bsAnswer = 'benar';
                } elseif (in_array($bsAnswer, ['salah', 'false', '0', 'tidak', 'no'])) {
                    $bsAnswer = 'salah';
                } else {
                    throw new \Exception('Jawaban BS harus "Benar" atau "Salah"');
                }
                $qData['short_answers'] = json_encode([$bsAnswer]);
                ExamQuestion::create($qData);
                break;

            case 'IS':
                $answers = array_values(array_filter(
                    array_map('trim', explode(',', $answer))
                ));
                if (empty($answers)) throw new \Exception('Jawaban IS tidak boleh kosong');
                $qData['short_answers'] = json_encode([
                    'answers'        => $answers,
                    'case_sensitive' => false,
                ]);
                ExamQuestion::create($qData);
                break;

            case 'ES':
                $qData['short_answers'] = json_encode(['rubric' => $answer]);
                ExamQuestion::create($qData);
                break;

            case 'SK':
                // Format: "min:1,max:5,correct:3" or defaults
                $skMeta = $this->parseKeyValue($answer, ['min' => 1, 'max' => 5, 'correct' => null]);
                if ((int)$skMeta['max'] <= (int)$skMeta['min']) {
                    throw new \Exception('Skala max harus > min');
                }
                $qData['short_answers'] = json_encode([
                    'min'       => (int) $skMeta['min'],
                    'max'       => (int) $skMeta['max'],
                    'min_label' => '',
                    'max_label' => '',
                    'correct'   => $skMeta['correct'] !== null ? (int) $skMeta['correct'] : null,
                ]);
                ExamQuestion::create($qData);
                break;

            case 'MJ':
                // Format: "A=X,B=Y" or "item1=match1;item2=match2"
                $pairs = $this->parsePairs($answer);
                if (count($pairs) < 2) throw new \Exception('MJ minimal 2 pasangan');
                $qData['short_answers'] = json_encode($pairs);
                ExamQuestion::create($qData);
                break;

            default:
                throw new \Exception("Tipe $typeRaw tidak ditangani");
        }
    }

    /* ================================================================
     * READ CSV
     * ================================================================ */
    private function readCsv(string $path): array
    {
        $rows    = [];
        $headers = null;

        if (($handle = fopen($path, 'r')) !== false) {
            // Try to detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = strpos($firstLine, ';') > strpos($firstLine, ',') ? ';' : ',';

            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                if ($headers === null) {
                    $headers = $data;
                    continue;
                }
                if (count($data) !== count($headers)) continue;
                $rows[] = array_combine($headers, $data);
            }
            fclose($handle);
        }

        return $rows;
    }

    /* ================================================================
     * READ EXCEL using PhpSpreadsheet
     * ================================================================ */
    private function readExcel(string $path): array
    {
        // PhpSpreadsheet is required: composer require phpoffice/phpspreadsheet
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \Exception('PhpSpreadsheet tidak terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        if (empty($rows)) return [];

        $headers = array_shift($rows);

        $result = [];
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue; // skip empty rows
            $result[] = array_combine($headers, array_slice($row, 0, count($headers)));
        }

        return $result;
    }

    /* ================================================================
     * DOWNLOAD TEMPLATE
     * ================================================================ */
    public function downloadTemplate(Request $request, $examId)
    {
        // Verify ownership
        Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);

        $templatePath = storage_path('app/imports/template_soal.xlsx');

        // Jika file belum ada, generate dulu
        if (!file_exists($templatePath)) {
            $this->generateTemplateFile();
        }

        return response()->download($templatePath, 'template_soal.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildTemplateCsv(): string
    {
        $header = ['no', 'pertanyaan', 'tipe', 'skor', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban', 'pembahasan'];

        $rows = [
            // PG example
            [1, 'Ibu kota Indonesia adalah?', 'PG', 10, 'Jakarta', 'Bandung', 'Surabaya', 'Medan', '', 'A', 'Jakarta adalah ibu kota Indonesia'],
            // PGK example
            [2, 'Manakah yang termasuk planet dalam tata surya?', 'PGK', 10, 'Bumi', 'Mars', 'Matahari', 'Bulan', 'Neptunus', 'A,B,E', ''],
            // BS example
            [3, 'Bumi adalah planet terbesar di tata surya', 'BS', 5, '', '', '', '', '', 'Salah', 'Jupiter adalah planet terbesar'],
            // IS example
            [4, 'Apa ibukota Jawa Barat?', 'IS', 10, '', '', '', '', '', 'Bandung,Kota Bandung', ''],
            // ES example
            [5, 'Jelaskan proses fotosintesis pada tumbuhan!', 'ES', 20, '', '', '', '', '', '', 'Sebutkan: bahan, proses, hasil fotosintesis'],
            // SK example
            [6, 'Seberapa puas kamu dengan pelajaran ini? (1=tidak puas, 5=sangat puas)', 'SK', 0, '', '', '', '', '', 'min:1,max:5,correct:', ''],
            // MJ example
            [7, 'Jodohkan negara dengan ibu kotanya!', 'MJ', 10, '', '', '', '', '', 'Indonesia=Jakarta;Jepang=Tokyo;Korea=Seoul', ''],
            // DD example
            [8, 'Pilih jenis segitiga berdasarkan sudutnya:', 'DD', 5, 'Lancip', 'Siku-siku', 'Tumpul', 'Sama sisi', '', 'C', ''],
        ];

        $output = fopen('php://temp', 'r+');
        // Add BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $header);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    /* ================================================================
     * PRIVATE HELPERS
     * ================================================================ */

    private function extractOptions(array $r): array
    {
        $options = [];
        $keys = [
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'a',
            'b',
            'c',
            'd',
            'e'
        ];

        // Try opsi_a…opsi_e pattern
        foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
            $val = $r["opsi_{$letter}"] ?? $r["option_{$letter}"] ?? $r[$letter] ?? null;
            $options[] = is_string($val) ? trim($val) : '';
        }

        return $options;
    }

    private function saveChoices(int $questionId, array $options, array $correctIndexes): void
    {
        $correctIndexes = array_map('intval', $correctIndexes);
        foreach ($options as $i => $text) {
            if (empty(trim($text ?? ''))) continue;
            ExamChoice::create([
                'question_id' => $questionId,
                'label'       => chr(65 + $i),
                'text'        => trim($text),
                'is_correct'  => in_array($i, $correctIndexes),
                'order'       => $i,
            ]);
        }
    }

    private function parseKeyValue(string $str, array $defaults = []): array
    {
        $result = $defaults;
        // Format: "key:value,key:value" or "key=value;key=value"
        $parts = preg_split('/[,;]/', $str);
        foreach ($parts as $part) {
            if (str_contains($part, ':')) {
                [$k, $v] = explode(':', $part, 2);
                $result[strtolower(trim($k))] = trim($v) !== '' ? trim($v) : null;
            } elseif (str_contains($part, '=')) {
                [$k, $v] = explode('=', $part, 2);
                $result[strtolower(trim($k))] = trim($v) !== '' ? trim($v) : null;
            }
        }
        return $result;
    }

    private function parsePairs(string $str): array
    {
        $pairs = [];
        // Try semicolon-separated first: "A=X;B=Y"
        $delimiter = str_contains($str, ';') ? ';' : ',';
        $parts = explode($delimiter, $str);
        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                [$left, $right] = explode('=', $part, 2);
                if (trim($left) && trim($right)) {
                    $pairs[] = ['left' => trim($left), 'right' => trim($right)];
                }
            }
        }
        return $pairs;
    }

    /**
     * Generate template Excel dan simpan ke storage.
     */
    private function generateTemplateFile(): string
    {
        // Pastikan direktori ada
        $directory = storage_path('app/imports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'A1' => 'No',
            'B1' => 'Pertanyaan',
            'C1' => 'Tipe',
            'D1' => 'Skor',
            'E1' => 'Opsi A',
            'F1' => 'Opsi B',
            'G1' => 'Opsi C',
            'H1' => 'Opsi D',
            'I1' => 'Opsi E',
            'J1' => 'Jawaban',
            'K1' => 'Pembahasan',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Data contoh
        $examples = [
            [1, 'Ibu kota Indonesia adalah?', 'PG', 10, 'Jakarta', 'Bandung', 'Surabaya', 'Medan', '', 'A', 'Jakarta adalah ibu kota'],
            [2, 'Manakah planet dalam tata surya?', 'PGK', 10, 'Bumi', 'Mars', 'Matahari', 'Bulan', 'Neptunus', 'A,B,E', ''],
            [3, 'Bumi adalah planet terbesar', 'BS', 5, '', '', '', '', '', 'Salah', 'Jupiter lebih besar'],
            [4, 'Ibu kota Jawa Barat?', 'IS', 10, '', '', '', '', '', 'Bandung,Kota Bandung', ''],
            [5, 'Jelaskan fotosintesis', 'ES', 20, '', '', '', '', '', '', 'Sebutkan bahan, proses, hasil'],
            [6, 'Kepuasan belajar (1-5)', 'SK', 0, '', '', '', '', '', 'min:1,max:5,correct:', ''],
            [7, 'Jodohkan negara-ibu kota', 'MJ', 10, '', '', '', '', '', 'Indonesia=Jakarta;Jepang=Tokyo;Korea=Seoul', ''],
            [8, 'Pilih segitiga berdasarkan sudut', 'DD', 5, 'Lancip', 'Siku-siku', 'Tumpul', 'Sama sisi', '', 'C', ''],
        ];

        $row = 2;
        foreach ($examples as $ex) {
            $col = 'A';
            foreach ($ex as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto size kolom
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Simpan ke storage - PERBAIKAN: Gunakan Writer\Xlsx
        $filePath = storage_path('app/imports/template_soal.xlsx');

        // PERBAIKAN: Gunakan Writer\Xlsx, bukan Reader\Xlsx
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
