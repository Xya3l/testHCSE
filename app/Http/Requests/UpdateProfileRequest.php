<?php

namespace App\Http\Requests;

use App\Enums\ProfileStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'picture' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'status' => ['sometimes', Rule::enum(ProfileStatus::class)],
            'delete' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Vérifie si la requête demande une suppression
     *
     * @return bool
     */
    public function wantsDelete(): bool
    {
        return $this->isMethod('delete') || $this->boolean('delete');
    }

    /**
     * Retourne les données validées sans le champ 'delete'
     *
     * @return array
     */
    public function validatedForModel(?string $picturePath = null): array
    {
        $data = $this->safe()->except('delete');

        if ($picturePath !== null) {
            $data['picture'] = $picturePath;
        }
        return $data;
    }

    public function hasPicture(): bool {
        return $this->has('picture');
    }

    public function getPicture(): array|\Illuminate\Http\UploadedFile|null
    {
        return $this->file('picture');
    }

    /**
     * Définit le chemin de l'image uploadée dans les données validées
     * Permet d'éviter la condition dans le controller
     *
     * @param string $picturePath
     * @return void
     */
    public function setPicturePath(string $picturePath): void
    {
        $this->merge(['picture' => $picturePath]);
    }
}
