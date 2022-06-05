<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReservationRequest;
use App\Mail\ReservationCompletionMail;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\Administrator;
use App\Models\Course;
use App\Models\Payment;
use Mail;

class ReservationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReservationRequest $request)
    {
        //予約を登録
        $inputs = $request->except(['_token', 'payment_id']);
        $inputs['user_id'] = Auth::id();
        $inputs['rating_flg'] = false;
        $reservation = Reservation::create($inputs);

        //決済テーブルにreservation_idを登録
        Payment::where('id', $request->payment_id)
        ->update(['reservation_id' => $reservation->id]);

        //予約完了メールを送信
        $course = Course::find($reservation->course_id);
        $admins = Administrator::all();
        $from_email = $admins[0]->email;
        $to_email = Auth::user()->email;
        Mail::to($to_email)->send
        (
            new ReservationCompletionMail($reservation, $from_email, $course, false)
        );
        return response()->json([
            'message' => 'Store successfully'
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReservationRequest $request, $id)
    {
        //更新
        $inputs = $request->except(['_token']);
        $result = Reservation::where('id', $id)->update($inputs);
        //メール送信
        if($result) {
            $reservation = Reservation::find($id);
            $course = Course::find($reservation->course_id);
            $admins = Administrator::all();
            $from_email = $admins[0]->email;
            $to_email = Auth::user()->email;
            Mail::to($to_email)->send
            (
                new ReservationCompletionMail($reservation, $from_email, $course, true)
            );
            return response()->json([
                'message' => 'Updated successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
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
        $reservation = Reservation::find($id)->delete();
        $payment = Payment::where('reservation_id', $id)->delete();
        if ($reservation && $payment) {
            return response()->json([
                'message' => 'Deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }
    }
}
