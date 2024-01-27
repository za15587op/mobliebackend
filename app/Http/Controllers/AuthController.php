<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $filed = $request->validate([
            "name" => "required|string",
            "email" => "required|string|unique:users,email",
            "password" => "required|string|confirmed",
            "address" => "required|string",
            "telephone" => "required|string",
            "role" => "required|integer",
        ]);

        $user = User::create([
            "name" => $filed["name"],
            "email" => $filed["email"],
            "password" => bcrypt($filed["password"]),
            "address" => $filed["address"],
            "telephone" => $filed["telephone"],
            "role" => $filed["role"],

        ]);

        $token = $user->createToken($request->userAgent(), [$filed["role"]])->plainTextToken;

        $reponse = [
            'user' => $user,
            'token' => $token
        ];
        return response($reponse, 201);
    }


    public function login(Request $request)
    {
        $filed = $request->validate([
            "email" => "required|string",
            "password" => "required|string"
        ]);

        $user = User::where("email", $filed["email"])->first();

        if (!$user || !Hash::check($filed["password"], $user->password)) {
            return response([
                "massage" => "Invalid Login"
            ], 401);
        } else {
            $user->tokens()->delete();
            $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

            $reponse = [
                "user" => $user,
                "token" => $token
            ];
            return response($reponse, 200);
        }
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response([
            "message" => "Logged Out"
        ],200);
    }

}
