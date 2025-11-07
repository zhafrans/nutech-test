<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseCode;
use App\Enums\TransactionType;
use App\Helpers\CodeHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\GetHistoryRequest;
use App\Http\Requests\Api\V1\Transaction\StoreRequest;
use App\Http\Requests\Api\V1\Transaction\TopupRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function topup(TopupRequest $request)
    {
        $user = auth('jwt')->user();
        $amount = $request->top_up_amount;

        try {
            DB::beginTransaction();

            if (!$user->balance) {
                $user->balance()->create([
                    'amount' => $amount,
                ]);
            } else {
                $user->balance()->increment('amount', $amount);
            }

            Transaction::create([
                'invoice_number' => CodeHelper::generateTopupInvoiceCode(),
                'transaction_type' => TransactionType::TOPUP,
                'total_amount' => $amount,
                'user_id' => $user->id,
            ]);

            DB::commit();
            $user->load('balance');

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Top up Balance berhasil',
                data: [
                    'balance' => $user->balance->amount,
                ]
            );
        } catch (Throwable $th) {
            DB::rollBack();

            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Gagal melakukan top up',
                data: [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }

    public function store(StoreRequest $request)
    {
        $user = auth('jwt')->user();

        try {
            DB::beginTransaction();

            $serviceProduct = DB::table('service_products')
                ->where('service_code', $request->service_code)
                ->first();

            if (is_null($serviceProduct)) {
                return ResponseHelper::generate(
                    responseCode: ResponseCode::ValidationError,
                    message: 'Service tidak ditemukan'
                );
            }

            $balance = $user->balance()->lockForUpdate()->first();

            if ($balance->amount < $serviceProduct->service_tariff) {
                DB::rollBack();
                return ResponseHelper::generate(
                    responseCode: ResponseCode::ValidationError,
                    message: 'Saldo tidak mencukupi'
                );
            }

            $transaction = Transaction::create([
                'invoice_number' => CodeHelper::generateInvoiceCode(),
                'service_product_id' => $serviceProduct->id,
                'transaction_type' => TransactionType::PAYMENT,
                'total_amount' => $serviceProduct->service_tariff,
                'user_id' => $user->id,
            ]);

            $balance->decrement('amount', $serviceProduct->service_tariff);

            DB::commit();

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Transaksi berhasil',
                data: [
                    'invoice_number' => $transaction->invoice_number,
                    'service_code' => $serviceProduct->service_code,
                    'service_name' => $serviceProduct->service_name,
                    'transaction_type' => $transaction->transaction_type->getName(),
                    'total_amount' => (float) $transaction->total_amount,
                    'created_on' => $transaction->created_at->toISOString(),
                ]
            );
        } catch (Throwable $th) {
            DB::rollBack();

            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Gagal membuat transaksi',
                data: [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }

    public function getHistory(GetHistoryRequest $request)
    {
        $user = auth('jwt')->user();

        try {
            $offset = $request->offset;
            $limit  = $request->limit;

            $transactions = Transaction::with('serviceProduct')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'invoice_number'   => $item->invoice_number,
                        'transaction_type' => $item->transaction_type->getName(),
                        'description'      => $item->serviceProduct->service_name ?? 'Top Up Balance',
                        'total_amount'     => $item->total_amount,
                        'created_on'       => $item->created_at,
                    ];
                });

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Riwayat transaksi ditemukan',
                data: [
                    'offset'  => $offset,
                    'limit'   => $limit,
                    'records' => $transactions,
                ]
            );
        } catch (Throwable $th) {
            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Gagal mengambil riwayat transaksi',
                data: [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }
}
