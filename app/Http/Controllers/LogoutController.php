<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogoutController extends Controller
{
    // logout
    public function __invoke(Request $request)
    {

        $user = Auth::user();
        // Revoke the user's current token...
        $request->user()->currentAccessToken()->delete();
        UserLog::create([
            'user_id' => $user->id,
            'logs' => 'Logout',
            'remarks' => '-',
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return response()->noContent();
    }
}
