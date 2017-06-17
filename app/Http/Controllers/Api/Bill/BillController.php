<?php

namespace App\Http\Controllers\Api\Bill;

use App\Bill;
use App\Payment;
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

            if (!is_null($type)) {
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
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a bill.
     *
     * @param Request $request
     * @return mixed
     */
    public function remove(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->toUser();

            if ($request->has('id')) {
                $id = $request->input('id');

                $bill = $user->receivedBills()
                    ->where('id', '=', $id)
                    ->first();

                if (is_null($bill)) {
                    $bill = $user->sentBills()
                        ->where('id', '=', $id)
                        ->first();
                }

                if (!is_null($bill)) {
                    $bill->delete();
                } else {
                    throw new JWTException();
                }

            } else {
                throw new JWTException();
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json(['message' => 'Счет успешно удален!', 'code' => 200]);
    }

    /**
     * Create a new bill.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $receiverNumber = $request->input('receiver_number');
            $senderNumber = $request->input('sender_number');

            $receiver = Payment::where('number', '=', $receiverNumber)
                ->with('owner')
                ->first();

            $sender = Payment::where('number', '=', $senderNumber)
                ->with('owner')
                ->first();

            $credentials = ['amount', 'notification', 'status'];

            if ($receiver === null) {
                return Response::json([
                    'error' => 'Невозможно выписать счет. Такого получателя не существует или не корректно введены данные.',
                    'code' => 500
                ], 500);
            }

            if ($sender->owner->id === $receiver->owner->id) {
                return Response::json([
                    'error' => 'Нельзя выписать счёт самому себе!',
                    'code' => 500
                ], 500);
            }

            if ($senderNumber[0] !== $receiverNumber[0]) {
                return Response::json([
                    'error' => 'Укажите кошелек получателя с такой же валютой как и у вас.',
                    'code' => 500
                ], 500);
            }

            $bill = new Bill($request->only($credentials));
            $bill->setReceiver($receiver->owner->id);
            $bill->setSourcePayment($sender->id);
            $bill->setDestinationPayment($receiver->id);

            $user = JWTAuth::parseToken()->toUser();
            $user->sentBills()->save($bill);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json([
            'message' => 'Счет успешно выписан!',
            'bill' => $bill,
            'code' => 200
        ]);
    }

    /**
     * Pay the bill.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(Request $request)
    {
        try {
            $billId = (int)$request->input('bill');

            $bill = Bill::where('id', '=', $billId)
                ->with(['sender', 'sourcePayment', 'destinationPayment'])
                ->first();

            if (is_null($bill)) {
                return Response::json(['error' => 'Такого счёта не существует!', 'code' => 500], 500);
            }

            $senderBalance = $bill->sourcePayment->amount;
            $receiverBalance = $bill->destinationPayment->amount;

            $amount = $bill->amount;

            if ($receiverBalance >= $amount) {
                $negativeBalance = $receiverBalance - $amount;
                $positiveBalance = $senderBalance + $amount;

                $bill->destinationPayment->update(['amount' => $negativeBalance]);
                $bill->sourcePayment->update(['amount' => $positiveBalance]);

                $bill->update(['status' => 1]);
            } else {
                return Response::json(['error' => 'Недостаточно средств на кошельке!', 'code' => 500], 500);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Что-то не так!', 'code' => 500], 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }

        return Response::json([
            'message' => 'Счёт оплачен!',
            'balance_negative' => $negativeBalance,
            'balance_positive' => $positiveBalance,
            'sender_id' => $bill->sender->id,
            'code' => 200
        ]);
    }
}