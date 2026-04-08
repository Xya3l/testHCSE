<?php

namespace App\DTO;

use App\Enums\ProfileStatus;

final readonly class ProfileData
{
    public function __construct(
        public string $lastName,
        public string $firstName,
        public string $picture,
        public ProfileStatus $status,
        public ?string $id = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'last_name' => $this->lastName,
            'first_name' => $this->firstName,
            'picture' => $this->picture,
            'status' => $this->status,
        ];
    }
}
