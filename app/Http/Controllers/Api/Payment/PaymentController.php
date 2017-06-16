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
     * Update a payment.
     *
     * @param Request $request
     * @param null $number
     * @return mixed
     */
    public function update(Request $request, $number = null)
    {
        try {
            if (!is_null($number)) {
                $payment = Payment::where('number', '=', $number);
                $payment->update($request->all());
            } else {
                throw new JWTException();
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['payment' => $payment, 'code' => 200]);
    }

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
     * @param null $change
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(Request $request, $change = null)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();

            if (is_null($change)) {
                $payments = $user->payments()
                    ->get();
            } else {
                $payments = $user->payments()
                    ->where('currency', '!=', $change)
                    ->get();
            }


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

    /**
     * Transfer amount from payment to payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfer(Request $request)
    {
        try {
            $receiverNumber = $request->input('receiver_number');
            $senderNumber = $request->input('sender_number');

            $amount = $request->input('amount');

            $receiver = Payment::where('number', '=', $receiverNumber)
                ->with('owner')
                ->first();

            $sender = Payment::where('number', '=', $senderNumber)
                ->with('owner')
                ->first();

            if ($receiver === null || $senderNumber === $receiverNumber) {
                return Response::json([
                    'error' => 'Невозможно перевести деньги. Такого получателя не существует или не корректно введены данные.',
                    'code' => 500
                ], 500);
            }

            if ($senderNumber[0] !== $receiverNumber[0]) {
                return Response::json([
                    'error' => 'Укажите кошелек получателя с такой же валютой как и у вас.',
                    'code' => 500
                ], 500);
            }

            $sum = $receiver->amount + $amount;
            $receiver->update(['amount' => $sum]);

            $sum = $sender->amount - $amount;
            $sender->update(['amount' => $sum]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json([
            'message' => 'Деньги переведены!',
            'balance' => $sum,
            'code' => 200
        ]);
    }

    /**
     * Exchange currency.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exchange(Request $request)
    {
        try {
            $receiverNumber = $request->input('receiver_number');
            $senderNumber = $request->input('sender_number');

            $amount = $request->input('amount');
            $amount_plus = $request->input('amount_plus');

            $receiver = Payment::where('number', '=', $receiverNumber)
                ->with('owner')
                ->first();

            $sender = Payment::where('number', '=', $senderNumber)
                ->with('owner')
                ->first();

            if ($receiver === null || $senderNumber === $receiverNumber) {
                return Response::json([
                    'error' => 'Невозможно перевести деньги. Такого получателя не существует или не корректно введены данные.',
                    'code' => 500
                ], 500);
            }

            if ($senderNumber[0] === $receiverNumber[0]) {
                return Response::json([
                    'error' => 'Укажите кошелек получателя с другой валютой.',
                    'code' => 500
                ], 500);
            }

            $sum_pos = $receiver->amount + $amount_plus;
            $receiver->update(['amount' => $sum_pos]);

            $sum = $sender->amount - $amount;
            $sender->update(['amount' => $sum]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Something went wrong!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json([
            'message' => 'Деньги переведены!',
            'balance' => $sum,
            'balance_positive' => $sum_pos,
            'code' => 200
        ]);
    }

}