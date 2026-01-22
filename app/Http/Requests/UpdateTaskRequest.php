<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'title_task'      => 'required|string|max:255',
        'subject_id'      => 'required|exists:subjects,id',
        'materi_id'       => 'required|exists:materis,id',
        'class_id'        => 'required|array',
        'class_id.*'      => 'exists:classes,id',
        'date_collection' => 'required|date',
        'description_task'=> 'nullable|string',
        'file_task'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ];
}


    public function messages()
    {
        return [
            'class_id.required' => 'Kelas Belum Di-Pilih',
            'class_id.exists' => 'Kelas Tidak Ada',
            'materi_id.required' => 'Materi Belum Di-Pilih',
            'materi_id.exists' => 'Materi Tidak Ada',
            'title_task.required' => 'Judul Tugas Belum Di-Isi',
            'title_task.string' => 'Judul Tugas Harus Berformat String',
            'title_task.unique' => 'Judul Tugas Sudah Ada',
            'description_task.max'=>'Deskripsi Sudah Melebihi Karakter',
            'file_task.mimes' => 'File Tugas Harus Bertipe png,jpg,pdf',
            'file_task.max' => 'File Tugas Melewati batas (Batas: 3MB)',
            'date_collection.required' => 'Tanggal Pengumpulan Belum Di-Isi',
            'date_collection.after' => 'Tanggal Harus Setelah Hari ini',
        ];
    }
}
