<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\DashboardFormRequest;
use Illuminate\Validation\Rules\File;

class UpdateProfileImageRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'bail',
                'required',
                'image',
                File::types(['jpg', 'jpeg', 'png'])->max(2048)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Gambar wajib diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar tidak sesuai.',
            'image.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ];
    }
}
