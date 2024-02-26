<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

// use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Конструктор класса. Здесь мы указываем, что кроме метода login,
    // для всех других методов контроллера необходима аутентификация.
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','refresh']]);
    }

    // Метод для аутентификации пользователя и получения токена
    public function login(Request $request)
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Если валидация не прошла, возвращаем ошибки
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Попытка аутентификации и получения токена
        if (!$token = auth()->attempt($validator->validated())) {
            // Если аутентификация не удалась, возвращаем ошибку
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Если аутентификация прошла успешно, возвращаем токен и данные пользователя
        return $this->respondWithToken($token);
    }

    // Метод для выхода пользователя (отзыв токена)
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    // Метод для обновления токена пользователя
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    // Метод для получения данных о текущем аутентифицированном пользователе
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function register(Request $request)
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Создание пользователя
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Аутентификация и создание токена для пользователя
        // Вместо auth()->login($user), используйте JWTAuth для генерации токена
        $token = JWTAuth::fromUser($user);

        // Ответ с токеном и данными пользователя
        return $this->respondWithToken($token);
    }

    // Вспомогательный метод для создания ответа с новым токеном
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60, // Убедитесь, что используете правильный guard
            'user' => auth()->user()
        ]);
    }

}
