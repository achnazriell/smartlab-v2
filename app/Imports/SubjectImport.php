<?php

namespace App\Imports;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class SubjectImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    private $successCount = 0;
    private $skippedCount = 0;
    private $errorCount = 0;
    private $rowNumber = 1;
    private $importErrors = [];

    public function model(array $row)
    {
        $currentRow = $this->rowNumber++;

        // Get data from possible column names
        $nameSubject = $this->getValue($row, ['nama_mapel', 'name_subject', 'mapel', 'mata_pelajaran', 'subject_name']);
        $description = $this->getValue($row, ['deskripsi', 'description', 'keterangan']);

        // Skip empty rows
        if (empty($nameSubject)) {
            $this->skippedCount++;
            return null;
        }

        // Normalize data
        $nameSubject = trim($nameSubject);
        $description = !empty($description) ? trim($description) : null;

        // Check for duplicates
        $existingSubject = Subject::where('name_subject', $nameSubject)->first();
        if ($existingSubject) {
            $this->skippedCount++;
            $this->importErrors[] = "Baris {$currentRow}: Mapel '{$nameSubject}' sudah terdaftar";
            return null;
        }

        try {
            DB::beginTransaction();

            // Create new subject
            Subject::create([
                'name_subject' => $nameSubject,
                'description' => $description,
            ]);

            DB::commit();
            $this->successCount++;

            return null;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorCount++;
            $this->importErrors[] = "Baris {$currentRow}: " . $e->getMessage();
            return null;
        }
    }

    private function getValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            $variations = [
                $key,
                strtolower($key),
                strtolower(str_replace('_', ' ', $key)),
                strtolower(str_replace(' ', '_', $key)),
            ];

            foreach ($variations as $variation) {
                if (isset($row[$variation]) && !empty($row[$variation])) {
                    return $row[$variation];
                }
            }
        }
        return null;
    }

    public function rules(): array
    {
        return [
            '*.nama_mapel' => 'nullable|string|max:255',
            '*.name_subject' => 'nullable|string|max:255',
            '*.deskripsi' => 'nullable|string',
        ];
    }

    public function getImportStats()
    {
        return [
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount,
            'total' => $this->rowNumber - 1
        ];
    }

    public function getErrors()
    {
        return $this->importErrors;
    }
}
