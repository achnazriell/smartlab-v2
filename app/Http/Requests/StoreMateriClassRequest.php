<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',

            'title_materi' => 'required|string|max:255',
            'description' => 'nullable|string',

            'file_materi' => 'required|file|mimes:pdf|max:10240',
        ];
    }
    public function messages()
    {
        return [
            'class_id.required' => 'Kelas harus dipilih.',
            'class_id.array' => 'Format kelas tidak valid.',
            'class_id.*.exists' => 'Kelas yang dipilih tidak valid.',

            'title_materi.required' => 'Judul materi wajib diisi.',
            'title_materi.string' => 'Judul materi harus berupa teks.',
            'title_materi.max' => 'Judul materi maksimal 255 karakter.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'file_materi.required' => 'File materi wajib diunggah.',
            'file_materi.file' => 'File materi harus berupa file yang valid.',
            'file_materi.mimes' => 'File materi harus berformat PDF.',
            'file_materi.max' => 'Ukuran file materi maksimal 10MB.',
        ];
    }
}
