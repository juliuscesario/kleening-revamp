
<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
        }
        .info-block {
            margin-bottom: 15px;
        }
        .info-block p {
            margin: 0;
        }
        .text-right {
            text-align: right;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.1);
            font-weight: bold;
            text-transform: uppercase;
            z-index: -1000;
        }
        .paid {
            color: rgba(0, 128, 0, 0.1);
        }
        .overdue {
            color: rgba(255, 0, 0, 0.1);
        }
        .sent {
            color: rgba(128, 128, 128, 0.1);
        }
    </style>
</head>
<body>
    @if ($invoice->status === 'paid')
        <div class="watermark paid">Paid</div>
    @elseif ($invoice->status === 'overdue')
        <div class="watermark overdue">Overdue</div>
    @elseif ($invoice->status === 'sent')
        <div class="watermark sent">Sent</div>
    @endif

    <div class="header">
        <img src="{{ public_path('storage/logo_kleening.png') }}" style="width: 150px; float: left;">
        <div style="clear: both;"></div>
        <h1>Invoice</h1>
        <h2>{{ $invoice->invoice_number }}</h2>
    </div>

    <div class="info-block">
        <p><strong>Customer:</strong>
            @if ($invoice->serviceOrder->customer)
                {{ $invoice->serviceOrder->customer->name }}
                @if ($invoice->serviceOrder->customer->trashed())
                    <span class="badge-danger">Archived</span>
                @endif
            @else
                N/A
            @endif
        </p>
        <p><strong>Address:</strong>
            @if ($invoice->serviceOrder->address)
                {{ $invoice->serviceOrder->address->full_address }}
                @if ($invoice->serviceOrder->address->trashed())
                    <span class="badge-danger">Archived</span>
                @endif
            @else
                N/A
            @endif
        </p>
        <p><strong>Area:</strong>
            @if ($invoice->serviceOrder->address && $invoice->serviceOrder->address->area)
                {{ $invoice->serviceOrder->address->area->name }}
            @else
                N/A
            @endif
        </p>
        <p><strong>Issue Date:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}</p>
        <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
    </div>

    <h3>Ordered Services</h3>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Quantity</th>
                <th class="text-right">Price/Unit</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->serviceOrder->items as $item)
            <tr>
                <td>{{ $item->service->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Subtotal</th>
                <th class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Discount</th>
                <th class="text-right">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Transport Fee</th>
                <th class="text-right">Rp {{ number_format($invoice->transport_fee, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Grand Total</th>
                <th class="text-right">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Down Payment</th>
                <th class="text-right">- Rp {{ number_format($invoice->dp_value, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Total After DP</th>
                <th class="text-right">Rp {{ number_format($invoice->total_after_dp, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Paid Amount</th>
                <th class="text-right">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Amount Due</th>
                <th class="text-right">Rp {{ number_format($invoice->grand_total - $invoice->paid_amount - $invoice->dp_value, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div style="position: fixed; bottom: 0; width: 100%; text-align: center;">
        <p>PT Kilau Elok Indonesia</p>
    </div>
</body>
</html>
