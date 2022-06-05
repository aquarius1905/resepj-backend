<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Like;
use App\Models\Genre;
use App\Models\Area;
use Datetime;
use Log;

class UserController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Create a new registered user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
     * @return @return \Illuminate\Http\Response
     */
    public function store(Request $request,
                          CreatesNewUsers $creator)
    {
        Log::Debug($request);
        event(new Registered($user = $creator->create($request->all())));
        return response()->json([
            'message' => 'Store sucessfully!'
            ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $today = new DateTime();
        $today = $today->format('Y-m-d');
        //予約一覧を取得
        $reservations = Reservation::with(['user', 'shop', 'course'])
        ->where('user_id', $user->id)
        ->where('date', '>=', $today)
        ->orderBy('date', 'desc')
        ->get();
        //評価店舗一覧を取得
        $ratingTargets = Reservation::with(['user', 'shop', 'course'])
        ->where('user_id', $user->id)
        ->where('rating_flg', true)
        ->orderBy('date', 'desc')
        ->get();
        //お気に入り店舗を取得
        $likes = Like::with(['user', 'shop'])
        ->where('user_id', $user->id)
        ->get();
        return response()->json([
            'reservations' => $reservations, 
            'ratingTargets' => $ratingTargets, 
            'user' => $user, 
            'likes' => $likes,
        ], 200);
    }
}
