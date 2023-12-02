<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @param  AuthLoginRequest  $request
     * @return JsonResponse
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $user = Auth::user();
            $data = [
                'token' => $user->createToken(env('APP_NAME'))->plainTextToken,
                'user' => new UserResource($user),
            ];
            return $this->returnSuccess($data, 'Login successful.');
        } else {
            return $this->returnError([], 'Invalid credentials.', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        $user = Auth::user();
        $data = [
            'user' => new UserResource($user),
        ];
        return $this->returnSuccess($data);
    }

    public function register(AuthRegisterRequest $request)
    {
        $userData = $request->only(['email', 'name', 'password']);
        $userData['password'] = Hash::make($userData['password']);
        $user = User::create($userData);
        if ($user instanceof User) {
            $results = [
                'token' => $user->createToken(env('APP_NAME'))->plainTextToken,
                'user' => new UserResource($user),
            ];
            return $this->returnSuccess($results, 'Registration successful.');
        } else {
            return $this->returnError([], 'Failed to create user, please contact administrator.');
        }
    }

    public function logout(){
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return $this->returnSuccess([], 'Successfully Logged Out');
    }
}
