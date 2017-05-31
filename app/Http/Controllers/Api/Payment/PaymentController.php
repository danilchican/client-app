<?php

namespace App\Http\Controllers\Api\Payment;

use Illuminate\Http\Request;
use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Payment;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;

class PaymentController
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

    /**
     * Remove a payment.
     *
     * @param Request $request
     * @return mixed
     */
    public function remove(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();
            $payment = $user->payments()
                ->where('number', '=', $request->input('number'))
                ->first();

            $payment->delete();
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['message' => 'Кошелек успешно удалён!', 'code' => 200]);
    }

    /**
     * Get payments list.
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(\Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();
            $payments = $user->payments()->get();

            return Response::json(['payments' => $payments, 'code' => 200]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

    }
}