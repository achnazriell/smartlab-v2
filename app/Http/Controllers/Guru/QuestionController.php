<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Supported question types:
     *  PG  = Pilihan Ganda (1 jawaban benar)
     *  PGK = Pilihan Ganda Kompleks (beberapa jawaban benar)
     *  BS  = Benar/Salah
     *  DD  = Dropdown (1 jawaban benar, tampilan dropdown)
     *  IS  = Isian Singkat
     *  ES  = Esai (dinilai manual)
     *  SK  = Skala Linear
     *  MJ  = Menjodohkan (matching)
     */
    private const VALID_TYPES = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];

    private function teacherId()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Bukan akun guru');
        return $teacher->id;
    }

    /* ================================================================
     * STORE - Create new question
     * ================================================================ */
    public function store(Request $request, $examId)
    {
        Log::info('Store Question Request:', $request->all());

        $exam = Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);

        // Base validation
        $validator = validator($request->all(), [
            'question' => 'required|string|min:3',
            'type'     => 'required|in:' . implode(',', self::VALID_TYPES),
            'score'    => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // tampilkan error pertama langsung
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $type = strtoupper(trim($request->type)); // pastikan uppercase & trim

            $questionData = [
                'exam_id'     => $exam->id,
                'type'        => $type,
                'question'    => trim($request->question),
                'score'       => (int) $request->score,
                'explanation' => trim($request->explanation ?? ''),
                'order'       => ExamQuestion::where('exam_id', $exam->id)->max('order') + 1,
            ];

            // Build short_answers / choices depending on type
            switch ($type) {
                // ---------- PG / DD: single-correct multiple choice ----------
                case 'PG':
                case 'DD':
                    $this->validateChoices($request);
                    $question = ExamQuestion::create($questionData);
                    $correctAnswer = $request->correct_answer;
                    $this->saveChoices($question->id, $request->options ?? [], [$correctAnswer], false);
                    break;

                // ---------- PGK: multi-correct multiple choice ----------
                case 'PGK':
                    $this->validateChoices($request, multiCorrect: true);
                    $question = ExamQuestion::create($questionData);
                    $this->saveChoices($question->id, $request->options ?? [], $request->correct_answers ?? [], false);
                    break;

                // ---------- BS: True/False ----------
                case 'BS':
                    $bsAnswer = strtolower(trim($request->short_answer ?? ''));
                    if (!in_array($bsAnswer, ['benar', 'salah'])) {
                        throw new \Exception('Jawaban Benar/Salah harus "benar" atau "salah"');
                    }
                    $questionData['short_answers'] = json_encode([$bsAnswer]);
                    $question = ExamQuestion::create($questionData);
                    break;

                // ---------- IS: Short answer ----------
                case 'IS':
                    if (empty(trim($request->short_answer ?? ''))) {
                        throw new \Exception('Jawaban tidak boleh kosong untuk soal Isian Singkat');
                    }
                    $answers = array_values(array_filter(
                        array_map('trim', explode(',', $request->short_answer))
                    ));
                    $questionData['short_answers'] = json_encode([
                        'answers'        => $answers,
                        'case_sensitive' => (bool) ($request->case_sensitive ?? false),
                    ]);
                    $question = ExamQuestion::create($questionData);
                    break;

                // ---------- ES: Essay (manual scoring) ----------
                case 'ES':
                    $questionData['score'] = (int) $request->score;
                    $questionData['short_answers'] = json_encode([
                        'rubric' => trim($request->rubric ?? ''),
                    ]);
                    $question = ExamQuestion::create($questionData);
                    break;

                // ---------- SK: Linear scale ----------
                case 'SK':
                    $scaleMin = (int) ($request->scale_min ?? 1);
                    $scaleMax = (int) ($request->scale_max ?? 5);
                    if ($scaleMax <= $scaleMin) throw new \Exception('Skala maksimum harus lebih besar dari minimum');
                    $questionData['short_answers'] = json_encode([
                        'min'       => $scaleMin,
                        'max'       => $scaleMax,
                        'min_label' => trim($request->scale_min_label ?? ''),
                        'max_label' => trim($request->scale_max_label ?? ''),
                        'correct'   => ($request->scale_correct !== null && $request->scale_correct !== '') ? (int) $request->scale_correct : null,
                    ]);
                    $question = ExamQuestion::create($questionData);
                    break;

                // ---------- MJ: Matching ----------
                case 'MJ':
                    $pairs = $request->pairs ?? [];
                    if (count($pairs) < 2) throw new \Exception('Minimal 2 pasangan untuk soal Menjodohkan');
                    foreach ($pairs as $pair) {
                        if (empty(trim($pair['left'] ?? '')) || empty(trim($pair['right'] ?? ''))) {
                            throw new \Exception('Semua pasangan harus terisi (kiri dan kanan)');
                        }
                    }
                    $questionData['short_answers'] = json_encode($pairs);
                    $question = ExamQuestion::create($questionData);
                    break;

                default:
                    throw new \Exception('Tipe soal tidak dikenali: ' . $type);
            }

            DB::commit();

            $question->load(['choices' => fn($q) => $q->orderBy('order')]);

            return response()->json([
                'success'  => true,
                'message'  => 'Soal berhasil ditambahkan',
                'question' => $this->formatQuestion($question),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ================================================================
     * SHOW - Get single question (PERBAIKAN FORMAT)
     * ================================================================ */
    public function show($examId, $questionId)
    {
        try {
            $exam = Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);

            $question = ExamQuestion::where('exam_id', $exam->id)
                ->with(['choices' => fn($q) => $q->orderBy('order')])
                ->findOrFail($questionId);

            // Format data untuk frontend
            $formatted = $this->formatQuestion($question);

            return response()->json([
                'success'  => true,
                'question' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing question:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan'], 404);
        }
    }

    /* ================================================================
     * UPDATE - Edit existing question
     * ================================================================ */
    public function update(Request $request, $examId, $questionId)
    {
        Log::info('Update Question Request:', $request->all());

        $exam     = Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        $validator = validator($request->all(), [
            'question' => 'required|string|min:3',
            'type'     => 'required|in:' . implode(',', self::VALID_TYPES),
            'score'    => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $type = strtoupper(trim($request->type)); // pastikan uppercase

            $question->update([
                'type'        => $type,
                'question'    => trim($request->question),
                'score'       => (int) $request->score,
                'explanation' => trim($request->explanation ?? ''),
            ]);

            // Remove old choices
            $question->choices()->delete();
            // Clear short_answers
            $question->update(['short_answers' => null]);

            switch ($type) {
                case 'PG':
                case 'DD':
                    $this->validateChoices($request);
                    $this->saveChoices($question->id, $request->options ?? [], [$request->correct_answer], false);
                    break;

                case 'PGK':
                    $this->validateChoices($request, multiCorrect: true);
                    $this->saveChoices($question->id, $request->options ?? [], $request->correct_answers ?? [], false);
                    break;

                case 'BS':
                    $bsAnswer = strtolower(trim($request->short_answer ?? ''));
                    if (!in_array($bsAnswer, ['benar', 'salah'])) {
                        throw new \Exception('Jawaban harus "benar" atau "salah"');
                    }
                    $question->update(['short_answers' => json_encode([$bsAnswer])]);
                    break;

                case 'IS':
                    $answers = array_values(array_filter(
                        array_map('trim', explode(',', $request->short_answer ?? ''))
                    ));
                    if (empty($answers)) throw new \Exception('Jawaban tidak boleh kosong');
                    $question->update(['short_answers' => json_encode([
                        'answers'        => $answers,
                        'case_sensitive' => (bool) ($request->case_sensitive ?? false),
                    ])]);
                    break;

                case 'ES':
                    $question->update(['short_answers' => json_encode(['rubric' => trim($request->rubric ?? '')])]);
                    break;

                case 'SK':
                    $scaleMin = (int) ($request->scale_min ?? 1);
                    $scaleMax = (int) ($request->scale_max ?? 5);
                    if ($scaleMax <= $scaleMin) throw new \Exception('Skala maksimum harus lebih besar dari minimum');
                    $question->update(['short_answers' => json_encode([
                        'min'       => $scaleMin,
                        'max'       => $scaleMax,
                        'min_label' => trim($request->scale_min_label ?? ''),
                        'max_label' => trim($request->scale_max_label ?? ''),
                        'correct'   => ($request->scale_correct !== null && $request->scale_correct !== '') ? (int) $request->scale_correct : null,
                    ])]);
                    break;

                case 'MJ':
                    $pairs = $request->pairs ?? [];
                    if (count($pairs) < 2) throw new \Exception('Minimal 2 pasangan');
                    foreach ($pairs as $pair) {
                        if (empty(trim($pair['left'] ?? '')) || empty(trim($pair['right'] ?? ''))) {
                            throw new \Exception('Semua pasangan harus terisi (kiri dan kanan)');
                        }
                    }
                    $question->update(['short_answers' => json_encode($pairs)]);
                    break;

                default:
                    throw new \Exception('Tipe soal tidak dikenali: ' . $type);
            }

            DB::commit();

            $question->load(['choices' => fn($q) => $q->orderBy('order')]);

            return response()->json([
                'success'  => true,
                'message'  => 'Soal berhasil diperbarui',
                'question' => $this->formatQuestion($question),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /* ================================================================
     * DESTROY - Delete question
     * ================================================================ */
    public function destroy($examId, $questionId)
    {
        try {
            $exam     = Exam::where('teacher_id', $this->teacherId())->findOrFail($examId);
            $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

            DB::beginTransaction();
            $question->choices()->delete();
            $question->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Soal berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ================================================================
     * PRIVATE HELPERS
     * ================================================================ */

    private function validateChoices(Request $request, bool $multiCorrect = false): void
    {
        $options = $request->options ?? [];
        $filled  = array_filter(array_map('trim', $options));

        if (count($filled) < 2) {
            throw new \Exception('Minimal 2 opsi jawaban harus diisi');
        }

        if ($multiCorrect) {
            $correct = $request->correct_answers ?? [];
            if (empty($correct)) throw new \Exception('Pilih minimal 1 jawaban yang benar');
        } else {
            // correct_answer bisa null, string kosong, atau angka (termasuk 0)
            $ca = $request->correct_answer;
            if ($ca === null || $ca === '' || $ca === '-1') {
                throw new \Exception('Pilih jawaban yang benar');
            }
        }
    }

    private function saveChoices(int $questionId, array $options, array $correctIndexes, bool $isCheckbox): void
    {
        $correctIndexes = array_map('intval', $correctIndexes);

        foreach ($options as $index => $text) {
            $trimmed = trim($text ?? '');
            if ($trimmed === '') continue;

            ExamChoice::create([
                'question_id' => $questionId,
                'label'       => chr(65 + $index), // A, B, C…
                'text'        => $trimmed,
                'is_correct'  => in_array($index, $correctIndexes),
                'order'       => $index,
            ]);
        }
    }

    /**
     * Format question untuk dikirim ke frontend
     */
    private function formatQuestion(ExamQuestion $q): array
    {
        $data = [
            'id'           => $q->id,
            'type'         => $q->type,
            'question'     => $q->question,
            'score'        => $q->score,
            'explanation'  => $q->explanation,
            'short_answers' => null,
            'choices'      => [],
        ];

        // Decode short_answers
        if ($q->short_answers) {
            $raw = is_array($q->short_answers) ? $q->short_answers : json_decode($q->short_answers, true);
            $data['short_answers'] = $raw;
        }

        // Choices for PG, PGK, DD
        if (in_array($q->type, ['PG', 'PGK', 'DD'])) {
            $data['choices'] = $q->choices->map(fn($c) => [
                'id'         => $c->id,
                'label'      => $c->label,
                'text'       => $c->text,
                'is_correct' => (bool) $c->is_correct,
                'order'      => $c->order,
            ])->values()->toArray();
        }

        return $data;
    }
}
