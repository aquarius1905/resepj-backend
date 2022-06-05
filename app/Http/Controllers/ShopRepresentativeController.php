<?php

namespace App\Http\Controllers;

use App\Auth\Events\RepresetativeRegistered;
use App\Http\Requests\ShopRepresentativeRegisterRequest;
use App\Models\ShopRepresentative;
use App\Models\Area;
use App\Models\Genre;
use App\Models\Shop;
use App\Models\Reservation;
use App\Models\Rating;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Datetime;


class ShopRepresentativeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ShopRepresentativeRegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopRepresentativeRegisterRequest $request)
    {
        //店舗代表者登録
        $inputs = $request->except(['_token']);
        Log::Debug($inputs);
        $inputs['password'] = Hash::make($inputs['password']);
        $shop_represetative = ShopRepresentative::create($inputs);
        $token = $shop_represetative->createToken('auth_token')->plainTextToken;
        // event(new RepresetativeRegistered($shop_represetative));
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
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
        //
    }
}
