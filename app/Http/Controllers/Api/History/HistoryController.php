<?php

namespace App\Http\Controllers\Api\History;

use Illuminate\Http\Request;
use App\History;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;


class HistoryController
{
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
}