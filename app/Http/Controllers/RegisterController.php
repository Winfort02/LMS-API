<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    // constructor
    public function __construct()
    {
        $this->middleware('guest');
    }

    // register and logged in
    public function __invoke(RegisterRequest $request)
    {
        $data = $request->only('username', 'name', 'email', 'user_type', 'is_active', 'password');
        $user = User::create($data);
        // $token = $user->createToken('Laravel Password Grant Client')->plainTextToken;

        // return (new UserResource($user))->additional(compact('token'));
        return (new UserResource($user));
    }
}
