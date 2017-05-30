<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\User;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    /**
     * Handle a registration API request for the application.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $fields = ['name', 'passport', 'email', 'password'];
        $credentials = $request->only($fields);

        try {
            $user = User::create($credentials);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        $token = JWTAuth::fromUser($user);

        return Response::json(compact('token'));
    }
}
