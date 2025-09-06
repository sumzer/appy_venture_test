<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return (new AuthTokenResource(['user' => $user, 'token' => $token]))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return (new AuthTokenResource(['user' => $user, 'token' => $token]))->response();
    }

    public function logout()
    {
        auth()->user()?->currentAccessToken()?->delete();

        return response()->json([
            'data' => ['logout' => true],
        ]);
    }
}
