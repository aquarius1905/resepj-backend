<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Reservation;
use App\Http\Requests\RatingRequest;

class RatingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RatingRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(RatingRequest $request)
    {
        $inputs = $request->except(['_token']);
        Rating::create($inputs);
        return response()->json([
            'message' => 'Store successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\Http\Response
     */
    public function show($reservation_id, Rating $rating)
    {
        $reservation = Reservation::find($reservation_id);
        if ($reservation) {
            return response()->json([
                'reservation' => $reservation
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }
    }
}
