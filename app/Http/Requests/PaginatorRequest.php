<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginatorRequest extends FormRequest
{
    const int DEFAULT_PER_PAGE = 15;
    const int MAX_PER_PAGE = 100;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
        ];
    }

    public function page(): int
    {
        return max(1, $this->integer('page', 1));
    }

    /** Nombre d'items avec une limite maximale par pages pour la pagination, afin d'éviter une surcharge */
    public function perPage(int $maxResults = self::MAX_PER_PAGE): int
    {
        $maxResults = max(1, $maxResults);
        return max(1, min($this->integer('perPage', self::DEFAULT_PER_PAGE), $maxResults));
    }
}
