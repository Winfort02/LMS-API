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
    }

    public function check_token() {
        $user = Auth::User();
        $user_token = $user->currentAccessToken();
        if($user_token->tokenable !== null) return response()->json(['authorized' => true]);
    }


    public function user_list_by_user_types(Request $request)
    {
        $user_types = $request->all();
        $users = User::where(function ($query) use ($user_types) {
            foreach ($user_types as $user_type) {
                $query->orWhere('user_type', $user_type);
            }
        })
            ->where('is_active', true)
            ->get();

        return UserResource::collection($users);
    }



    public function get_all_users_is_active()
    {
        $users = User::with('user_rights')->where('is_active', true)
            ->get();

        $user_list = array();
        foreach ($users as $user) {
            array_push($user_list, $user);
        }

        return UserResource::collection($user_list);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterRequest $request)
    {
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
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
    }


    public function change_password(Request $request, $id): UserResource
    {

        $user = User::find($id);

        // if (Hash::check($request->password, $user->password)) {
        //             $user->password = bcrypt($request->password);
        //             $user->save();
        //         }
        $user->password = $request->new_password;
        $user->save();

        return new UserResource($user->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
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
    }
}
