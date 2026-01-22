<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriRequest extends FormRequest
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
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',
            'title_materi' => ['required'],
            'file_materi' => ['required', 'mimes:pdf', 'max:10240'],
        ];
    }
    public function messages()
    {
        return [
            'class_id.required' => 'Kelas Belum Di-Pilih',
            'class_id.exists' => 'Kelas Tidak Ada',
            'title_materi.required' => 'Judul Materi Belum Di-isi',
            'file_materi.required' => 'File Materi Belum Di-isi',
            'file_materi.mimes' => 'File Materi Harus Bertipe PDF',
            'file_materi.max' => 'File Materi Harus Dibawah 10 MB',
        ];
    }
}
