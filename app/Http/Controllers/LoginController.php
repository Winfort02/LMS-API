<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Http\Resources\UserResource;
use App\Http\Resources\LogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginController extends Controller
{
    // login as guest
    public function __construct()
    {
        $this->middleware('guest');
    }
    // login
    public function __invoke(Request $request)
    {
        $input = $request->all();
  
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
  
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt(array($fieldType => $input['username'], 'password' => $input['password']))) {
            $user = Auth::user();

            if ($user->is_active == true) {
                $token = $user->createToken('token-name')->plainTextToken;
                UserLog::create([
                    'user_id' => $user->id,
                    'logs' => 'Signed in',
                    'remarks' => '-',
                    'date' => Carbon::now()->format('Y-m-d'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()

                ]);
                return (new UserResource($user))->additional(compact('token'));
            } else {
                return response()->json([
                    'message' => 'User is deactivated',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return response()->json([
            'message' => 'Email or password is incorrect',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
