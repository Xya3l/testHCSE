<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileController extends Controller
{

    public function __construct(
        private readonly ImageService $imageService
    ) {}

    /**
     * Récupère tous les profils avec le statut "actif".
     * Endpoint public
     */
    public function getActiveProfiles(PaginatorRequest $request)
    {
        $request->validated();

        return ProfileResource::collection(
            Profile::active()->paginate(perPage: $request->perPage(maxResults: 50), page: $request->page())
        );
    }

    /**
     * Crée un nouveau profil avec upload d'image
     */
    public function createProfile(StoreProfileRequest $request): JsonResponse
    {
        $profile = DB::transaction(function () use ($request): Profile {
            $profileId = Str::uuid()->toString();

            $picturePath = $this->imageService->predictPath(
                $request->getPicture(),
                Profile::class,
                $profileId,
            );

            $profile = Profile::create($request->validatedForModel($picturePath, $profileId));

            $this->imageService->store(
                $request->getPicture(),
                Profile::class,
                $profile->id
            );
            return $profile;
        });

        return (new ProfileResource($profile))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Met à jour ou supprime un profil existant (un seul endpoint pour les deux actions dans la consigne)
     */
    public function updateOrDeleteProfile(UpdateProfileRequest $request, Profile $profile)
    {
        if ($request->wantsDelete()) {
            return $this->deleteProfile($profile);
        }

        return $this->updateProfile($request, $profile);
    }

    /**
     * Supprime un profil existant
     */
    private function deleteProfile(Profile $profile)
    {
        DB::transaction(function () use ($profile): void {
            $picturePath = $profile->picture;
            $profile->delete();
            $this->imageService->delete($picturePath);
        });

        return response()->noContent();
    }

    /**
     * Met à jour un profil existant
     */
    private function updateProfile(UpdateProfileRequest $request, Profile $profile)
    {
        if (!$request->hasPicture()) {
            $profile->update($request->validatedForModel());
            return new ProfileResource($profile);
        }

        $profile = DB::transaction(function () use ($request, $profile): Profile {
            $oldPath = $profile->picture;

            // l'extension du fichier peut changer
            $updatedPath = $this->imageService->predictPath(
                $request->getPicture(),
                Profile::class,
                $profile->id,
            );

            $profile->update($request->validatedForModel($updatedPath));

            $this->imageService->store(
                $request->getPicture(),
                Profile::class,
                $profile->id,
                $oldPath,
            );

            return $profile;
        });

        return new ProfileResource($profile);
    }
}
