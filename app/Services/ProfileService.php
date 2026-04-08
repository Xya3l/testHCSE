<?php

namespace App\Services;

use App\DTO\ProfileData;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileService
{
    public function __construct(
        private readonly ImageService $imageService
    ) {}

    /**
     * Crée un nouveau profil
     */
    public function create(ProfileData $data, UploadedFile $picture): Profile
    {
        return DB::transaction(function () use ($data, $picture): Profile {
            $profileId = Str::uuid()->toString();

            $picturePath = $this->imageService->predictPath(
                $picture,
                Profile::class,
                $profileId,
            );

            $profileData = $data->toArray();
            $profileData['picture'] = $picturePath;
            $profileData['id'] = $profileId;

            $profile = Profile::create($profileData);

            $this->imageService->store(
                $picture,
                Profile::class,
                $profile->id
            );

            return $profile;
        });
    }

    /**
     * Met à jour un profil existant
     */
    public function update(Profile $profile, ProfileData $data, ?UploadedFile $picture = null): Profile
    {
        if ($picture === null) {
            $profile->update($data->toArray());
            return $profile;
        }

        return DB::transaction(function () use ($profile, $data, $picture): Profile {
            $oldPath = $profile->picture;

            $updatedPath = $this->imageService->predictPath(
                $picture,
                Profile::class,
                $profile->id,
            );

            $profileData = $data->toArray();
            $profileData['picture'] = $updatedPath;

            $profile->update($profileData);

            $this->imageService->store(
                $picture,
                Profile::class,
                $profile->id,
                $oldPath,
            );

            return $profile;
        });
    }

    /**
     * Supprime un profil
     */
    public function delete(Profile $profile): void
    {
        DB::transaction(function () use ($profile): void {
            $picturePath = $profile->picture;
            $profile->delete();
            $this->imageService->delete($picturePath);
        });
    }
}
