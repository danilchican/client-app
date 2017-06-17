<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['only' => 'login']);
    }

    /**
     * Authenticate user in system.
     *
     * @param Request $request
     * @return mixed|json
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return Response::json(['error' => 'Неверный логин и пароль!', 'code' => 401], 401);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        }

        return Response::json(['token' => $token, 'code' => 200]);
    }

    /**
     * Get user info.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUser() {
        try {
            if (! $user = JWTAuth::parseToken()->toUser()) {
                return Response::json(['error' => 'Пользователь не найден!', 'code' => 404], 404);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        }

        return Response::json(['user' => $user, 'code' => 200]);
    }

    /**
     * Logout user from system.
     *
     * @return mixed|json
     */
    public function logout() {
        $token = JWTAuth::getToken();

        try {
            if (! JWTAuth::invalidate($token)) {
                return Response::json(['error' => 'Ошибка выхода из аккаунта!', 'code' => 401], 401);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        }

        return Response::json([
            'success' => 'Юзер разлогинен.',
            'code' => 200
        ]);
    }
}
