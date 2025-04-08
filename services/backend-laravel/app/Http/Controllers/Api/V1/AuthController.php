<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\HttpResponse;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->validated());

            DB::commit();

            return HttpResponse::sendResponse([
                'name' => $user->name,
                'email' => $user->email,
            ], 'User registered successfully.', 201);

        } catch (\Exception $e) {
            DB::rollback();
            return HttpResponse::sendResponse([], $e->getMessage(), 500);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return HttpResponse::sendResponse([], 'Invalid credentials.', 401);
            }

            return HttpResponse::sendResponse([
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'token' => auth()->user()->createToken('Api Token')->plainTextToken,
            ], 'User logged in successfully.');

        } catch (\Exception $e) {
            return HttpResponse::sendResponse([], $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            if(!auth()->user()){
                return HttpResponse::sendResponse([], 'No authenticated user found.', 401);
            }

            auth()->user()->currentAccessToken()->delete();

            return HttpResponse::sendResponse([], 'User logged out successfully.');

        } catch (\Exception $e) {
            return HttpResponse::sendResponse([], $e->getMessage(), 500);
        }
    }
}
