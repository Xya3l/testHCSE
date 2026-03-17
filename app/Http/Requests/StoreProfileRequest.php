<?php

namespace App\Http\Requests;

use App\Enums\ProfileStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'picture' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'status' => ['required', Rule::enum(ProfileStatus::class)],
        ];
    }

    public function validatedForModel(?string $picturePath = null, ?string $uuid = null): array
    {
        $data = $this->validated();

        if ($picturePath !== null) {
            $data['picture'] = $picturePath;
            $data['id'] = $uuid;
        }

        return $data;
    }

    public function getPicture(): array|\Illuminate\Http\UploadedFile|null  {
        return $this->file('picture');
    }
}
