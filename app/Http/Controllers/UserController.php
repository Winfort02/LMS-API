<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            
            $keywords = $request->keywords;
            $users = User::where(function ($query) use ($keywords) {
                if ($keywords) {
                    $query->where('username', 'like', '%' . $keywords . '%')
                        ->Orwhere('name', 'like', '%' . $keywords . '%')
                        ->Orwhere('email', 'like', '%' . $keywords . '%')
                        ->Orwhere('user_type', 'like', '%' . $keywords . '%');
                }
            })
                ->orderBy('id', 'DESC')
                ->paginate();

            return UserResource::collection($users);

        } catch (Exception $e) {
            
            return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function check_token() 
    {
        try {
            
            $user = Auth::User();
            $user_token = $user->currentAccessToken();
            if($user_token->tokenable !== null) {
                return response()->json(['authorized' => true]);
            } else
                return response()->json(['authorized' => false]);

        } catch (Exception $e) {
            
            return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterRequest $request)
    {
        try {
            
            //create User
            $current_user = Auth::user();
            $data = $request->only('username', 'name', 'email', 'user_type', 'is_active', 'password');
            $user = User::create($data);
            UserLog::create([
                'user_id' => $current_user->id,
                'logs' => 'User Management',
                'remarks' => 'Added new user ' . $request->name,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return new UserResource($user);

        } catch (Exception $e) {
            
            return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            
            $user = User::findOrFail($id);

            return new UserResource($user);
        
        } catch (Exception $e) {
            if($e->getCode() == 0) {

                return response()->json(['message' => 'USER NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else

                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            
            $current_user = Auth::user();
            $user = User::find($id);
            $user->name = $request->name;
            $user->user_type = $request->user_type;
            $user->username = $request->username;
            $user->email = $request->email;
            // $user->password =   $request->password;
            $user->is_active = $request->is_active;
            $user->save();
            UserLog::create([
                'user_id' => $current_user->id,
                'logs' => 'User Management',
                'remarks' => 'Update user details',
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return new UserResource($user->refresh());

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'USER NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function change_password(Request $request, $id): UserResource
    {

        try {
            
            $user = User::find($id);
            $user->password = $request->new_password;
            $user->save();

            return new UserResource($user->refresh());

        } catch (Exception $e) {
            
            return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            
            $current_user = Auth::user();
            $user = User::findOrFail($id);
            $user_name = $user->name;
            $user->delete();
            UserLog::create([
                'user_id' => $current_user->id,
                'logs' => 'User Management',
                'remarks' => 'Deleted user ' . $user_name,
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            

            return response()->noContent();

        } catch (Exception $e) {
            
            if($e->getCode() == 0) {
                return response()->json(['message' => 'USER NOT FOUND'], Response::HTTP_NOT_FOUND);
            } else
                return response()->json(['message' => 'SERVER ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
