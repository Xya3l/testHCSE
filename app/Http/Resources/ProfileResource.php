<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var \App\Models\Profile $profile */
        $profile = $this->resource;

        return [
            'id' => $profile->id,
            'last_name' => $profile->last_name,
            'first_name' => $profile->first_name,
            'picture' => $profile->picture ? asset("storage/{$profile->picture}") : null,
            'status' => $this->when(auth()->check(), $profile->status?->value),
            'created_at' => $profile->created_at?->toIso8601String(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
        ];
    }
}
