<?php

namespace App\Http\Controllers\Api\Bill;

use Illuminate\Http\Request;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class BillController
{
    /**
     * Getting bills for authorized user.
     *
     * @param Request $request
     * @param null $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function bills(Request $request, $type = null)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();

            if(!is_null($type)) {
                switch ($type) {
                    case 'received':
                        $bills = $user->receivedBills()
                            ->with(['sourcePayment', 'destinationPayment'])
                            ->get();
                        break;
                    case 'sent':
                        $bills = $user->sentBills()
                            ->with(['sourcePayment', 'destinationPayment'])
                            ->get();
                        break;
                    default:
                        throw new JWTException();
                        break;
                }
            } else {
                $bills = $user->receivedBills()
                    ->with(['sourcePayment', 'destinationPayment'])
                    ->get();
            }

            return Response::json(['bills' => $bills, 'code' => 200]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }
}