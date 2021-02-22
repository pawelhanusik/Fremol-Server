<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register() {
        $validated = request()->validate([
            'name' => 'required',
            'email' => 'required|email', //TODO: unique
            'password' => 'required',
            'password2' => 'required|same:password'
        ]);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return [
            'message' => 'User has been registered'
        ];
    }
    public function login() {
        $validated = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token_name' => ''
        ]);
        if (auth()->once($validated)) {
            $tokenName = request()->token_name ?? 'mainToken';
            $token = auth()->user()->createToken($tokenName);

            return [
                'token' => $token->plainTextToken,
                'userName' => auth()->user()->name
            ];
        } else {
            abort(401, "Invalid credentials");
            return null;
        }
    }
    public function check() {
        return [
            'message' => 'OK'
        ];
    }
    public function logout() {
        request()->user()->tokens()->delete();
        return null;
    }
}
