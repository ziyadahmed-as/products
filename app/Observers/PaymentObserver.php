<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\ActivityLogger;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $sale = $payment->sale;
        ActivityLogger::log(
            module: 'Sales',
            actionType: 'payment',
            description: "Payment of Br" . number_format($payment->amount, 2) . " received via " . ($payment->method ?? 'Cash') . " for invoice #" . ($sale?->reference ?? $payment->sale_id) . ".",
            subject: $sale ?? $payment,
            properties: [
                'payment_id' => $payment->id,
                'sale_id'    => $payment->sale_id,
                'reference'  => $sale?->reference,
                'amount'     => $payment->amount,
                'method'     => $payment->method,
                'date'       => $payment->date,
                'branch_id'  => $sale?->branch_id,
            ]
        );
    }
}
