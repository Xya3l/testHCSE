<?php

namespace App\Http\Requests;

use App\Enums\ProfileStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'last_name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'picture' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'status' => ['sometimes', Rule::enum(ProfileStatus::class)],
        ];
    }

    public function hasPicture(): bool
    {
        return $this->hasFile('picture');
    }

    public function getPicture(): ?\Illuminate\Http\UploadedFile
    {
        return $this->file('picture');
    }
}
