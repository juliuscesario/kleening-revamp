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

        /* Prevent table rows from breaking across pages awkwardly */
        tr {
            page-break-inside: avoid;
        }

        th,
        td {
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

        .footer-note {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 8pt;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            background-color: #fff;
        }

        .billing-payment-section {
             margin-top: 30px;
             page-break-inside: avoid;
             width: 100%;
             background-color: #fff;
        }

        /* Borderless table for billing info */
        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .billing-table td {
            border: none;
            padding: 2px 5px;
            vertical-align: top;
        }
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
        @if(\App\Models\AppSetting::get('app_logo'))
            <img src="{{ public_path('storage/' . \App\Models\AppSetting::get('app_logo')) }}"
                style="width: 150px; float: left;">
        @else
            <img src="{{ public_path('storage/logo_kleening.png') }}" style="width: 150px; float: left;">
        @endif
        <div style="clear: both;"></div>
        <h1>Invoice</h1>
        <h2>{{ $invoice->invoice_number }}</h2>
    </div>

    <div class="info-block">
        <p><strong>Customer:</strong>
            @if ($invoice->serviceOrder->customer)
                {{ \Illuminate\Support\Str::title($invoice->serviceOrder->customer->name) }}
                @if ($invoice->serviceOrder->customer->trashed())
                    <span class="badge-danger">Archived</span>
                @endif
            @else
                N/A
            @endif
        </p>
        <p><strong>Address:</strong>
            @if ($invoice->serviceOrder->address)
                {{ \Illuminate\Support\Str::title($invoice->serviceOrder->address->full_address) }}
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

    <div class="info-block">
        <p><strong>Catatan Invoice:</strong></p>
        <p>{{ $invoice->serviceOrder->work_notes ?? 'Tidak ada catatan.' }}</p>
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
        @php
            $discountAmount = $invoice->discount_type === 'percentage'
                ? ($invoice->subtotal * ($invoice->discount ?? 0)) / 100
                : ($invoice->discount ?? 0);
            $dpAmount = $invoice->dp_type === 'percentage'
                ? ($invoice->grand_total * ($invoice->dp_value ?? 0)) / 100
                : ($invoice->dp_value ?? 0);
            $amountDue = max($invoice->grand_total - $invoice->paid_amount, 0);
        @endphp
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Subtotal</th>
                <th class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">
                    Discount
                    @if ($invoice->discount_type === 'percentage')
                        ({{ $invoice->discount }}%)
                    @endif
                </th>
                <th class="text-right">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</th>
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
                <th colspan="3" class="text-right">
                    Down Payment
                    @if ($invoice->dp_type === 'percentage')
                        ({{ $invoice->dp_value }}%)
                    @endif
                </th>
                <th class="text-right">- Rp {{ number_format($dpAmount, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Total After DP</th>
                <th class="text-right">Rp {{ number_format($invoice->total_after_dp, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Amount Due</th>
                <th class="text-right">Rp {{ number_format($amountDue, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="billing-payment-section">
        {!! \App\Models\AppSetting::get('invoice_footer_text', '
        <h3>Billing Information</h3>
        <table class="billing-table">
            <tr>
                <td style="width: 120px; font-weight: bold;">Bank</td>
                <td style="width: 10px;">:</td>
                <td>BCA</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Account No.</td>
                <td>:</td>
                <td>5933068888</td>
            </tr>
             <tr>
                <td style="font-weight: bold;">Account Name</td>
                <td>:</td>
                <td>PT. Kilau Elok Indonesia</td>
            </tr>
        </table>
        <br>
        <p>Jangan lupa konfirmasi dengan melampirkan bukti transfer 😊</p>
        <p>Terima kasih telah memilih @kleening.id sebagai jasa cleaning kepercayaan Anda ✨🙏🏻</p>') !!}
    </div>

    <div class="footer-note">
        <p><sup>*</sup> This invoice is generated automatically by the
            {{ \App\Models\AppSetting::get('app_name', config('app.name')) }} service platform, therefore no physical
            signature is required.
        </p>
        <p><sup>**</sup> Keep this invoice digital—please avoid printing it to reduce paper waste and help us protect
            the earth.</p>
        <p><strong>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</strong></p>
    </div>
</body>

</html>