<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ShopRegisterRequest;
use App\Http\Requests\ShopUpdateRequest;
use App\Models\Shop;
use App\Models\Area;
use App\Models\Genre;
use App\Models\Course;
use Log;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::Debug("ShopController::index");
        $user_id = Auth::id();
        $items = Shop::with(['area', 'genre', 'likes' => function($query) use($user_id) {
        $query->where('user_id', $user_id);
        }])->get();
        $areas = Area::all();
        $genres = Genre::all();
        $inputs = null;
        $payment_flg = false;
        return response()->json([
            'items' => $items,
            'areas' => $areas,
            'genres' => $genres,
            'inputs' => $inputs,
            'payment_flg' => $payment_flg
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ShopRegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopRegisterRequest $request)
    {
        Log::Debug("ShopController::store");
        //店舗の追加
        $shop_inputs = $request->except(['_token', 'course_names', 'course_prices']);
        $course_arrays = $request->only(['course_names', 'course_prices']);
        $img = $request->file('img');
        if(app()->isLocal() || app()->runningUnitTests())
        {
            $path = $img->store('public');
            $shop_inputs['img_filename'] = pathinfo($path, PATHINFO_BASENAME);
        }
        else
        {
            $path = Storage::disk('s3')->putFile('/', $img);
            $shop_inputs['img_filename'] = $path;
        }
        $shop = Shop::create($shop_inputs);
        $course_inputs = [];
        foreach(array_map(NULL, $course_arrays['course_names'], $course_arrays['course_prices']) as [$name, $price]) {
            array_push($course_inputs, ['shop_id' => $shop->id, 'name'=> $name, 'price' => $price]);
        }
        $result = Course::insert($course_inputs);
        if($shop && $result) {
            $courses = Course::where('shop_id', $shop->id)->get()->toArray();
            return response()->json([
                'shop' => $shop,
                'courses' => $courses
            ], 201);
        } else {
            return response()->json([
                'message' => 'Insert failed'
            ], 404);
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
        Log::Debug("ShopController::show");
        $item = Shop::with(['courses'])->find($id);
        if($item) {
            $payment_flg = false;
            return response()->json([
                'item' => $item,
                'payment_flg' => $payment_flg
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ShopUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ShopUpdateRequest $request, $id)
    {
        $file = $request->file('img');
        $result = false;
        if($file) {
            $inputs = $request->only([
                'name','area_id','genre_id','overview','img_filename'
            ]);
            $path = $file->store('public');
            $inputs['img_filename'] = pathinfo($path, PATHINFO_BASENAME);
            $result = Shop::where('id', $id)->update($inputs);
        } else {
            $inputs = $request->only([
                'name','area_id','genre_id','overview'
            ]);
            $result = Shop::where('id', $id)->update($inputs);
        }
        //コースの更新
        $course_ids = Course::where('shop_id', $id)->get(['id'])->toArray();
        $course = $request->only(['course_names', 'course_prices']);
        foreach(array_map(NULL, $course['course_names'], $course['course_prices'], $course_ids) as [$name, $price, $course_id]) {
            $result = Course::where('id', $course_id['id'])->update([
                'name'=> $name, 'price' => $price
            ]);
        }
        if($result) {
            return response()->json([
                'message' => 'Update successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }
    }

    /**
     * serach shops
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        Log::Debug("ShopController::search");
        $area = $request->area;
        $genre = $request->genre;
        $shop_name = $request->shop_name;
        $items = null;
        $user_id = Auth::id();
        if($user_id) {
            $items = Shop::with(['area', 'genre', 'likes' => function($like_query) use($user_id) {
                $like_query->where('user_id', $user_id);
            }])
            ->whereArea($area)
            ->whereGenre($genre)
            ->whereShopName($shop_name)
            ->get();
        } else {
            $items = Shop::with(['area', 'genre'])
            ->whereArea($area)
            ->whereGenre($genre)
            ->whereShopName($shop_name)
            ->get();
        }
        $areas = Area::all();
        $genres = Genre::all();
        $inputs = $request->except(['_token']);
        return response()->json([
            'items' => $items,
            'areas' => $areas,
            'genres' => $genres,
            'inputs' => $inputs
        ], 200);
    }
}
