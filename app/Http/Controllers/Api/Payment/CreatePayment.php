<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Payment;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class CreatePayment extends Controller
{
    /**
     * Create a new payment.
     *
     * @param CreatePaymentRequest $request
     * @return mixed
     */
    public function create(CreatePaymentRequest $request)
    {
        $fields = ['name', 'number', 'currency'];
        $credentials = $request->only($fields);

        try {
            $user = JWTAuth::parseToken()->toUser();
            $payment = new Payment($credentials);
            $user->payments()->save($payment);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['payment' => $payment, 'code' => 200]);
    }
}
