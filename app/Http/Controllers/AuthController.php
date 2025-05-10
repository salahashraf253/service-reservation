<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json([
            'data'=>new UserResource($user),
            'message' => 'User registered successfully',
        ], HttpResponse::HTTP_CREATED);
    }

    public function login(LoginUserRequest $request): LoginResource | JsonResponse
    {
        $userCredentials = $request->validated();
        $user = User::where('email', $userCredentials['email'])->first();

        if (! $user || ! Hash::check($userCredentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('api-token');

        return new LoginResource([
            'access_token' => $token->plainTextToken,
            'is_admin' => $user->is_admin,
        ]);
    }

    public function user(Request $request): UserResource | JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], HttpResponse::HTTP_UNAUTHORIZED);
        }
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

}