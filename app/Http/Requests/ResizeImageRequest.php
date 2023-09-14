<?php

namespace App\Http\Requests;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;

class ResizeImageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        $rules = [
            'image' => ['required'],
            'w' => ['required', 'regex:/^\d+(\.\d+)?%?$/'], // 50, 50%, 50.124, 50.123%
            'h' => ['regex:/^\d+(\.\d+)?%?$/'],
            'album_id' => ['exists:\App\Models\Album,id']
        ];

        $image = $this->all()['image'] ?? false;

        if ($image && $image instanceof UploadedFile) {
            $rules['image'][] = 'image';
        } else {
            $rules['image'][] = 'url';
        }

        return $rules;
    }
}
