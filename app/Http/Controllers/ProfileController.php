<?php

namespace App\Http\Controllers;

use App\DTO\ProfileData;
use App\Enums\ProfileStatus;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Récupère tous les profils avec le statut "actif".
     */
    public function index(PaginatorRequest $request): AnonymousResourceCollection
    {
        $request->validated();

        return ProfileResource::collection(
            Profile::active()->paginate(perPage: $request->perPage(maxResults: 50), page: $request->page())
        );
    }

    /**
     * Crée un nouveau profil avec upload d'image
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        $this->authorize('create', Profile::class);

        $validated = $request->validated();

        $data = new ProfileData(
            lastName: $validated['last_name'],
            firstName: $validated['first_name'],
            picture: '',
            status: ProfileStatus::from($validated['status']),
        );

        $profile = $this->profileService->create($data, $request->getPicture());

        return (new ProfileResource($profile))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Met à jour un profil existant
     */
    public function update(UpdateProfileRequest $request, Profile $profile): ProfileResource
    {
        $this->authorize('update', $profile);

        $validated = $request->validated();

        $data = new ProfileData(
            lastName: $validated['last_name'] ?? $profile->last_name,
            firstName: $validated['first_name'] ?? $profile->first_name,
            picture: $profile->picture,
            status: isset($validated['status'])
                ? ProfileStatus::from($validated['status'])
                : $profile->status,
        );

        $updatedProfile = $this->profileService->update(
            $profile,
            $data,
            $request->getPicture()
        );

        return new ProfileResource($updatedProfile);
    }

    /**
     * Supprime un profil existant
     */
    public function destroy(Profile $profile): Response
    {
        $this->authorize('delete', $profile);

        $this->profileService->delete($profile);

        return response()->noContent();
    }
}
