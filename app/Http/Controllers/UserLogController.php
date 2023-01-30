<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Http\Resources\LogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class UserLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function show_all_logs_by_user(Request $request, $user_id) 
    {
        $keywords = $request->keywords;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $logs = UserLog::where('user_id', $user_id)->where(function ($query) use ($keywords) {
            if ($keywords) {
                $query->where('logs', 'like', '%' . $keywords . '%');
            }
        })
            ->where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->with(['user'])
            ->orderBy('id', 'DESC')
            ->paginate();

        return LogResource::collection($logs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
