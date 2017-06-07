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

            if ($request->has('number')) {
                $number = $request->input('number');

                if (is_array($number)) {
                    $user->payments()
                        ->whereIn('number', $number)
                        ->delete();
                } else {
                    $payment = $user->payments()
                        ->where('number', '=', $number)
                        ->first();

                    $payment->delete();
                }
            } else {
                $user->payments()->delete();
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['message' => 'Кошельки удалёны!', 'code' => 200]);
    }

    /**
     * Get payments list.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(Request $request)
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

    /**
     * Get payment by it number.
     *
     * @param Request $request
     * @param null $number
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentByNumber(Request $request, $number = null)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();

            if (!is_null($number)) {
                $payment = $user->payments()
                    ->where('number', '=', $number)
                    ->first();

                return Response::json(['payment' => $payment, 'code' => 200]);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['message' => 'Кошелек не найден', 'code' => 400]);
    }
}