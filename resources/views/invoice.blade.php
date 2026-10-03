<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->reference }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f9f9f9; }
        .invoice-box { max-width: 800px; margin: 40px auto; padding: 30px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #06b6d4; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #06b6d4; font-size: 32px; }
        .company-details { text-align: right; }
        .details { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .details h3 { margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        table th, table td { padding: 12px; border: 1px solid #eee; text-align: left; }
        table th { background: #f8f9fa; color: #333; font-weight: bold; }
        table td.right, table th.right { text-align: right; }
        .totals { width: 50%; float: right; }
        .totals table th { background: none; }
        .totals table td, .totals table th { border: none; padding: 8px 12px; }
        .totals table tr.total-row th, .totals table tr.total-row td { border-top: 2px solid #eee; font-weight: bold; font-size: 1.2em; color: #06b6d4; }
        .footer { clear: both; text-align: center; color: #777; font-size: 0.9em; margin-top: 50px; border-top: 1px solid #eee; padding-top: 20px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef08a; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        @media print {
            body { background: #fff; margin: 0; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; }
            .btn-print { display: none; }
        }
        .btn-print { display: block; width: 200px; margin: 20px auto; padding: 10px; background: #06b6d4; color: #fff; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Print Invoice</button>
    <div class="invoice-box">
        <div class="header">
            <div>
                <h1>INVOICE</h1>
                <p><strong>Reference:</strong> {{ $sale->reference }}<br>
                <strong>Date:</strong> {{ $sale->created_at->format('M d, Y') }}</p>
                <p>
                    <strong>Payment Status:</strong>
                    @if($sale->payment_status === 'paid')
                        <span class="badge badge-success">Paid</span>
                    @elseif($sale->payment_status === 'partial')
                        <span class="badge badge-warning">Partial</span>
                    @else
                        <span class="badge badge-gray">Pending</span>
                    @endif
                </p>
            </div>
            <div class="company-details">
                <strong>Albareck Enterprise</strong><br>
                123 Business Avenue<br>
                Industrial District, TX 75001<br>
                Email: support@albareck.com
            </div>
        </div>

        <div class="details">
            <div>
                <h3>Billed To:</h3>
                <strong>{{ $sale->customer_name ?: 'Valued Customer' }}</strong><br>
                <em>Sales Rep: {{ $sale->user->name ?? 'N/A' }}</em>
            </div>
            <div style="text-align: right;">
                <h3>Fulfillment Location:</h3>
                {{ $sale->storageLocation->name ?? 'Main Warehouse' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item / Description</th>
                    <th class="right">Qty</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->lines as $line)
                <tr>
                    <td>
                        <strong>{{ $line->product->name ?? 'Unknown Product' }}</strong><br>
                        <small style="color: #777;">SKU: {{ $line->product->sku ?? 'N/A' }}</small>
                    </td>
                    <td class="right">{{ $line->quantity }}</td>
                    <td class="right">Br{{ number_format($line->unit_price, 2) }}</td>
                    <td class="right">Br{{ number_format($line->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <th class="right">Subtotal:</th>
                    <td class="right">Br{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <th class="right">Discount:</th>
                    <td class="right">Br{{ number_format($sale->discount, 2) }}</td>
                </tr>
                <tr>
                    <th class="right">Tax:</th>
                    <td class="right">Br{{ number_format($sale->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <th class="right">Total:</th>
                    <td class="right">Br{{ number_format($sale->total, 2) }}</td>
                </tr>
                <tr>
                    <th class="right">Paid Amount:</th>
                    <td class="right" style="color: #166534;">Br{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <th class="right">Balance Due:</th>
                    <td class="right" style="color: #991b1b; font-weight: bold;">Br{{ number_format($sale->total - $sale->paid_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p><small>If you have any questions concerning this invoice, contact our support team.</small></p>
        </div>
    </div>
</body>
</html>

