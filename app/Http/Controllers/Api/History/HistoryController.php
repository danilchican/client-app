<?php

namespace App\Http\Controllers\Api\History;

use App\Payment;
use Illuminate\Http\Request;
use App\History;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;


class HistoryController
{
    /**
     * Create a new history.
     *
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        $fields = ['owner_id', 'message', 'type', 'amount', 'balance'];
        $credentials = $request->only($fields);

        try {
            $payment = Payment::where('number', '=', $request->input('number'))->first();

            if($payment) {
                $history = new History($credentials);
                $history->save($payment);

                return Response::json(['history' => $history, 'code' => 200]);
            }

            throw new JWTException();
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Getting history for authorized user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history()
    {
        try {
            $user = JWTAuth::parseToken()->toUser();
            $history = $user->history()->with('payment')->get();

            return Response::json(['history' => $history, 'code' => 200]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Clear a history.
     *
     * @return mixed
     */
    public function clear()
    {
        try {
            $user = JWTAuth::parseToken()->toUser();
            $user->history()->delete();
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['message' => 'История очищена!', 'code' => 200]);
    }
}