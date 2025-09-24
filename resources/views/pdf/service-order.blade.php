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
                    case 'baru': $statusBadgeClass = 'badge-info'; break;
                    case 'proses': $statusBadgeClass = 'badge-warning'; break;
                    case 'batal': $statusBadgeClass = 'badge-danger'; break;
                    case 'selesai': $statusBadgeClass = 'badge-success'; break;
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

    <div style="margin-top: 50px; width: 100%;">
        <div style="width: 48%; float: left; text-align: center;">
            <p>Customer Signature,</p>
            <br><br><br>
            <p>(_________________________)</p>
        </div>
        <div style="width: 48%; float: right; text-align: center;">
            <p>Staff Signature,</p>
            <br><br><br>
            <p>(_________________________)</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>