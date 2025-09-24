<!DOCTYPE html>
<html>
<head>
    <title>Service Order {{ $serviceOrder->so_number }}</title>
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
        .badge-danger {
            color: #fff;
            background-color: #dc3545;
            padding: .25em .4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .signature-box {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            margin-top: 20px;
            min-height: 100px; /* Ensure some height even if no signature */
        }
        .signature-box img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .page-break {
            page-break-before: always;
        }
        .work-photo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .work-photo-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Service Order</h1>
        <h2>{{ $serviceOrder->so_number }}</h2>
    </div>

    <div class="info-block">
        <p><strong>Customer:</strong>
            @if ($serviceOrder->customer)
                {{ $serviceOrder->customer->name }}
                @if ($serviceOrder->customer->trashed())
                    <span class="badge-danger">Archived</span>
                @endif
            @else
                N/A
            @endif
        </p>
        <p><strong>Address:</strong>
            @if ($serviceOrder->address)
                {{ $serviceOrder->address->full_address }}
                @if ($serviceOrder->address->trashed())
                    <span class="badge-danger">Archived</span>
                @endif
            @else
                N/A
            @endif
        </p>
        <p><strong>Area:</strong>
            @if ($serviceOrder->address && $serviceOrder->address->area)
                {{ $serviceOrder->address->area->name }}
            @else
                N/A
            @endif
        </p>
        <p><strong>Work Date:</strong> {{ \Carbon\Carbon::parse($serviceOrder->work_date)->format('d M Y') }}</p>
        <p><strong>Status:</strong>
            @php
                $statusBadgeClass = '';
                switch ($serviceOrder->status) {
                    case 'booked': $statusBadgeClass = 'badge-primary'; break;
                    case 'proses': $statusBadgeClass = 'badge-warning'; break;
                    case 'cancelled': $statusBadgeClass = 'badge-danger'; break;
                    case 'done': $statusBadgeClass = 'badge-success'; break;
                    case 'invoiced': $statusBadgeClass = 'badge-secondary'; break;
                    default: $statusBadgeClass = 'badge-secondary'; break;
                }
            @endphp
            <span class="badge {{ $statusBadgeClass }}">{{ ucfirst($serviceOrder->status) }}</span>
        </p>
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
            @foreach($serviceOrder->items as $item)
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
                <th colspan="3" class="text-right">Total Keseluruhan</th>
                <th class="text-right">Rp {{ number_format($serviceOrder->items->sum('total'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <h3>Assigned Staff</h3>
    @if($serviceOrder->staff->isNotEmpty())
        <ul>
            @foreach($serviceOrder->staff as $staff)
                <li>{{ $staff->name }}</li>
            @endforeach
        </ul>
    @else
        <p>No staff assigned.</p>
    @endif

    <h3>Notes</h3>
    <p><strong>Work Notes:</strong> {{ $serviceOrder->work_notes ?? 'N/A' }}</p>

    <div style="margin-top: 20px;">
        <h4>Signatures</h4>
        <div style="width: 100%;">
            @if($serviceOrder->customer_signature_image)
                <div style="width: 30%; float: left; text-align: center; margin-bottom: 20px;">
                    <p>Customer Signature: <br/>
                    {{ $serviceOrder->customer->name }}</p>
                    <div class="signature-box">
                        <img src="{{ $serviceOrder->customer_signature_image }}" alt="Customer Signature" style="max-height: 80px;">
                    </div>
                </div>
            @endif

            @foreach($serviceOrder->staff as $staff)
                @if($staff->pivot->signature_image)
                    <div style="width: 30%; float: left; text-align: center; margin-bottom: 20px;">
                        <p>Staff Signature: <br/>{{ $staff->name }}</p>
                        <div class="signature-box">
                            <img src="{{ $staff->pivot->signature_image }}" alt="Staff Signature" style="max-height: 80px;">
                        </div>
                    </div>
                @endif
            @endforeach
            <div style="clear: both;"></div> <!-- Clear floats -->
        </div>
    </div>
</body>
</html>