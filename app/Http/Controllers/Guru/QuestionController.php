<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    private function teacherId()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Bukan akun guru');
        return $teacher->id;
    }

    public function store(Request $request, $examId)
    {
        Log::info('Store Question Request:', $request->all());
        Log::info('Exam ID:', ['examId' => $examId]);

        // Validasi exam
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($examId);

        // Validasi request
        $validator = validator($request->all(), [
            'question' => 'required|string|min:5',
            'type' => 'required|in:PG,IS',
            'score' => 'required|integer|min:1|max:100',
            'options' => 'required_if:type,PG|array|min:2|max:6',
            'options.*' => 'required_if:type,PG|string',
            'correct_answer' => 'required_if:type,PG|integer|min:0',
            'short_answer' => 'required_if:type,IS|string|min:1',
        ], [
            'options.required_if' => 'Opsi jawaban wajib diisi untuk soal Pilihan Ganda',
            'options.*.required_if' => 'Semua opsi wajib diisi',
            'short_answer.required_if' => 'Jawaban benar wajib diisi untuk soal Isian Singkat',
            'correct_answer.required_if' => 'Pilih jawaban yang benar untuk soal Pilihan Ganda',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            Log::info('Creating question with data:', [
                'exam_id' => $exam->id,
                'type' => $request->type,
                'question' => $request->question,
                'score' => $request->score,
            ]);

            $questionData = [
                'exam_id' => $exam->id,
                'type' => $request->type,
                'question' => trim($request->question),
                'score' => (int) $request->score,
                // TAMBAHKAN SETTING INI:
                'enable_skip' => $request->boolean('enable_skip', true),
                'enable_mark_review' => $request->boolean('enable_mark_review', true),
                'randomize_choices' => $request->boolean('randomize_choices', false),
                'show_explanation' => $request->boolean('show_explanation', false),
                'enable_timer' => $request->boolean('enable_timer', false),
                'time_limit' => $request->time_limit ?? null,
                'require_all_options' => $request->boolean('require_all_options', false),
            ];

            // Handle IS (Isian Singkat)
            if ($request->type === 'IS') {
                $answers = array_map('trim', explode(',', $request->short_answer));
                $answers = array_filter($answers, function ($answer) {
                    return !empty($answer);
                });

                if (empty($answers)) {
                    throw new \Exception('Jawaban benar tidak boleh kosong untuk soal Isian Singkat');
                }

                $questionData['short_answers'] = json_encode($answers);
                Log::info('IS Answers:', $answers);
            }

            $question = ExamQuestion::create($questionData);
            Log::info('Question created:', ['question_id' => $question->id]);

            // Handle PG (Pilihan Ganda)
            if ($request->type === 'PG') {
                $options = $request->options;
                $correctAnswer = (int) $request->correct_answer;

                Log::info('Creating PG choices:', [
                    'options_count' => count($options),
                    'correct_answer' => $correctAnswer
                ]);

                foreach ($options as $index => $option) {
                    if (!empty(trim($option))) {
                        ExamChoice::create([
                            'question_id' => $question->id,
                            'label' => chr(65 + $index), // A, B, C, D
                            'text' => trim($option),
                            'is_correct' => $index === $correctAnswer,
                            'order' => $index,
                        ]);
                        Log::info('Choice created:', [
                            'index' => $index,
                            'label' => chr(65 + $index),
                            'text' => trim($option),
                            'is_correct' => $index === $correctAnswer
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relationships for response
            $question->load(['choices' => function ($query) {
                $query->orderBy('order');
            }]);

            // Format response untuk Alpine.js
            $responseData = [
                'id' => $question->id,
                'type' => $question->type,
                'question' => $question->question,
                'score' => $question->score,
                'choices' => $question->type === 'PG' ? $question->choices->map(function ($choice) {
                    return [
                        'label' => $choice->label,
                        'text' => $choice->text,
                        'is_correct' => (bool) $choice->is_correct
                    ];
                })->toArray() : [],
                'short_answers' => $question->type === 'IS' ? ($question->short_answers ?? []) : []
            ];

            Log::info('Question created successfully:', $responseData);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil ditambahkan',
                'question' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($examId, $questionId)
    {
        try {
            $exam = Exam::where('teacher_id', $this->teacherId())
                ->findOrFail($examId);

            $question = ExamQuestion::where('exam_id', $exam->id)
                ->with(['choices' => function ($query) {
                    $query->orderBy('order');
                }])
                ->findOrFail($questionId);

            $responseData = [
                'id' => $question->id,
                'type' => $question->type,
                'question' => $question->question,
                'score' => $question->score,
                'choices' => $question->type === 'PG' ? $question->choices->map(function ($choice) {
                    return [
                        'label' => $choice->label,
                        'text' => $choice->text,
                        'is_correct' => (bool) $choice->is_correct
                    ];
                })->toArray() : [],
                'short_answers' => $question->type === 'IS' ? ($question->short_answers ?? []) : []
            ];

            return response()->json([
                'success' => true,
                'question' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching question:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data soal'
            ], 404);
        }
    }

    public function update(Request $request, $examId, $questionId)
    {
        Log::info('Update Question Request:', $request->all());

        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($examId);

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->findOrFail($questionId);

        $validator = validator($request->all(), [
            'question' => 'required|string|min:5',
            'type' => 'required|in:PG,IS',
            'score' => 'required|integer|min:1|max:100',
            'options' => 'required_if:type,PG|array|min:2|max:6',
            'options.*' => 'required_if:type,PG|string',
            'correct_answer' => 'required_if:type,PG|integer|min:0',
            'short_answer' => 'required_if:type,IS|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $question->update([
                'question' => trim($request->question),
                'type' => $request->type,
                'score' => (int) $request->score,
            ]);

            if ($request->type === 'IS') {
                $answers = array_map('trim', explode(',', $request->short_answer));
                $answers = array_filter($answers, function ($answer) {
                    return !empty($answer);
                });

                if (empty($answers)) {
                    throw new \Exception('Jawaban benar tidak boleh kosong');
                }

                $question->short_answers = json_encode($answers);
                $question->save();

                // Hapus pilihan lama jika ada
                $question->choices()->delete();
            } else {
                // Hapus pilihan lama
                $question->choices()->delete();

                // Buat pilihan baru
                $options = $request->options;
                $correctAnswer = (int) $request->correct_answer;

                foreach ($options as $index => $option) {
                    if (!empty(trim($option))) {
                        ExamChoice::create([
                            'question_id' => $question->id,
                            'label' => chr(65 + $index),
                            'text' => trim($option),
                            'is_correct' => $index === $correctAnswer,
                            'order' => $index,
                        ]);
                    }
                }

                // Clear short_answers jika ada
                $question->short_answers = null;
                $question->save();
            }

            DB::commit();

            // Reload data
            $question->load(['choices' => function ($query) {
                $query->orderBy('order');
            }]);

            $responseData = [
                'id' => $question->id,
                'type' => $question->type,
                'question' => $question->question,
                'score' => $question->score,
                'choices' => $question->type === 'PG' ? $question->choices->map(function ($choice) {
                    return [
                        'label' => $choice->label,
                        'text' => $choice->text,
                        'is_correct' => (bool) $choice->is_correct
                    ];
                })->toArray() : [],
                'short_answers' => $question->type === 'IS' ? ($question->short_answers ?? []) : []
            ];

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil diperbarui',
                'question' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($examId, $questionId)
    {
        try {
            $exam = Exam::where('teacher_id', $this->teacherId())
                ->findOrFail($examId);

            $question = ExamQuestion::where('exam_id', $exam->id)
                ->findOrFail($questionId);

            DB::beginTransaction();

            // Hapus pilihan terlebih dahulu
            $question->choices()->delete();

            // Hapus soal
            $question->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting question:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
