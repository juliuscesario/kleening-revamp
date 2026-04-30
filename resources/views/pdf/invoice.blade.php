<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* Reset */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
            padding: 0;
            margin: 0;
        }

        /* ============ WATER WAVE DECORATIONS (UNCHANGED) ============ */
        .wave-top-right {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 0;
        }

        .wave-bottom-left {
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: 0;
        }

        /* ============ MAIN CONTENT AREA ============ */
        .content-area {
            position: relative;
            z-index: 1;
            padding: 25px 30px 20px 30px;
        }

        /* ============ HEADER ============ */
        .logo-text {
            font-size: 18pt;
            font-weight: bold;
            color: #0a3d62;
            letter-spacing: 2px;
        }

        .logo-tagline {
            font-size: 6.5pt;
            color: #888;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .invoice-title {
            font-size: 26pt;
            font-weight: bold;
            color: #0a3d62;
            letter-spacing: 3px;
        }

        /* ============ CUSTOMER SECTION ============ */
        .customer-table td {
            vertical-align: top;
            padding: 0;
        }

        .invoice-to-label {
            font-size: 7pt;
            font-weight: bold;
            color: #1B9CFC;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .customer-name {
            font-size: 12pt;
            font-weight: bold;
            color: #0a3d62;
            margin-bottom: 3px;
        }

        .customer-detail {
            font-size: 8pt;
            color: #555;
            line-height: 1.6;
        }

        .customer-detail strong {
            color: #333;
        }

        .invoice-meta-line {
            font-size: 8pt;
            color: #444;
            line-height: 1.6;
        }

        .invoice-meta-line strong {
            color: #333;
            font-weight: bold;
        }

        /* ============ ITEMS TABLE (UNCHANGED) ============ */
        .items-table thead th {
            background-color: #1B9CFC;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }

        .items-table thead th:last-child,
        .items-table thead th:nth-child(3) {
            text-align: right;
        }

        .items-table thead th:nth-child(4) {
            text-align: center;
        }

        .items-table tbody td {
            padding: 7px 10px;
            font-size: 8.5pt;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .items-table tbody td:last-child,
        .items-table tbody td:nth-child(3) {
            text-align: right;
        }

        .items-table tbody td:nth-child(4) {
            text-align: center;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #1B9CFC;
        }

        .service-name {
            font-weight: bold;
            color: #0a3d62;
        }

        /* ============ SUMMARY SECTION (UNCHANGED) ============ */
        .summary-left {
            width: 45%;
            padding-right: 15px;
        }

        .payment-heading {
            font-size: 10pt;
            font-weight: bold;
            color: #1B9CFC;
            margin-bottom: 8px;
        }

        .payment-note {
            font-size: 7pt;
            color: #888;
            font-style: italic;
            margin-top: 5px;
        }

        /* ============ FOOTER (fixed at bottom) ============ */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 2;
            padding: 8px 30px 20px 30px;
            background-color: #fff;
        }

        .footer-table td {
            border: none;
            text-align: center;
            padding: 4px 10px;
            font-size: 7pt;
            color: #666;
        }

        .footer-disclaimer {
            text-align: center;
            font-size: 6.5pt;
            color: #999;
            line-height: 1.5;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        /* ============ UTILITIES ============ */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .section-spacer { height: 16px; }

    </style>
</head>

<body>
    @php
        $discountAmount = $invoice->discount_type === 'percentage'
            ? ($invoice->subtotal * ($invoice->discount ?? 0)) / 100
            : ($invoice->discount ?? 0);
        $dpAmount = $invoice->dp_type === 'percentage'
            ? ($invoice->grand_total * ($invoice->dp_value ?? 0)) / 100
            : ($invoice->dp_value ?? 0);
        $amountDue = max($invoice->grand_total - $invoice->paid_amount, 0);

        $customer = $invoice->serviceOrder->customer ?? null;
        $address = $invoice->serviceOrder->address ?? null;
        $area = $address ? $address->area : null;

        // Watermark text per status (removed — no watermark needed)
    @endphp

    <!-- ========== WATER WAVE: TOP-RIGHT (UNCHANGED) ========== -->
    <svg class="wave-top-right" width="260" height="160" viewBox="0 0 260 160" xmlns="http://www.w3.org/2000/svg">
        <!-- Deepest layer - darkest -->
        <path d="M260,0 L260,65 C220,50 180,72 140,57 C100,42 60,58 30,48 L0,0 Z" fill="#0a3d62" opacity="0.55"/>
        <!-- Middle layer -->
        <path d="M260,0 L260,100 C215,80 170,110 130,90 C90,70 50,95 20,80 L0,0 Z" fill="#1B9CFC" opacity="0.35"/>
        <!-- Lightest/top layer -->
        <path d="M260,0 L260,130 C225,110 185,135 150,115 C115,95 75,120 40,105 C20,95 0,75 0,55 L0,0 Z" fill="#1B9CFC" opacity="0.15"/>
        <!-- Accent splash wave -->
        <path d="M260,0 L260,45 C240,35 210,50 185,40 C160,30 130,42 105,35 L85,0 Z" fill="#1B9CFC" opacity="0.7"/>
    </svg>

    <!-- ========== WATER WAVE: BOTTOM-LEFT (UNCHANGED) ========== -->
    <svg class="wave-bottom-left" width="260" height="130" viewBox="0 0 260 130" xmlns="http://www.w3.org/2000/svg">
        <!-- Deepest layer -->
        <path d="M0,130 L0,25 C30,38 70,12 110,33 C150,54 190,28 230,42 L260,130 Z" fill="#0a3d62" opacity="0.55"/>
        <!-- Middle layer -->
        <path d="M0,130 L0,50 C40,62 80,35 120,55 C160,75 200,48 260,65 L260,130 Z" fill="#1B9CFC" opacity="0.35"/>
        <!-- Lightest layer -->
        <path d="M0,130 L0,70 C35,80 75,55 115,72 C155,89 195,65 235,78 L260,130 Z" fill="#1B9CFC" opacity="0.15"/>
        <!-- Accent splash wave -->
        <path d="M0,130 L0,90 C25,100 55,82 85,95 C115,108 145,92 175,102 L0,130 Z" fill="#1B9CFC" opacity="0.7"/>
    </svg>

    <!-- ========== CONTENT ========== -->
    <div class="content-area">

        <!-- ========== HEADER — Logo + tagline | INVOICE title ========== -->
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:60%;padding:0;vertical-align:middle;">
                    {{-- Logo from AppSetting --}}
                    @php
                        $logoPath = \App\Models\AppSetting::get('app_logo');
                    @endphp
                    @if ($logoPath)
                        <img src="{{ public_path('storage/' . $logoPath) }}"
                             style="height: 65px; width: auto;">
                    @endif
                    <div class="logo-tagline" style="margin-top:4px;">Professional Cleaning Service</div>
                </td>
                <td style="width:40%;padding:0;vertical-align:middle;text-align:left;padding-left:10px;">
                    <div class="invoice-title">INVOICE</div>
                </td>
            </tr>
        </table>

        <div class="section-spacer"></div> </br>

        <!-- ========== Customer info + invoice metadata shared rows ========== -->
        <table style="width:100%;border-collapse:collapse;">
            <!-- Row: Customer Name (right column empty) -->
            <tr>
                <td style="width:60%;vertical-align:top;padding:0;padding-right:15px;">
                    <div class="invoice-to-label">Invoice To</div>
                </td>
                <td style="width:40%;vertical-align:top;padding:0;padding-left:10px;"></td>
            </tr>
            <!-- Row: Customer Name (right column empty) -->
            <tr>
                <td style="vertical-align:top;padding:0;padding-right:15px;padding-top:0;">
                    <div class="customer-name">
                        {{ $customer ? \Illuminate\Support\Str::title($customer->name) : 'N/A' }}
                        @if ($customer && $customer->trashed())
                            <span style="color:#dc3545;font-size:7pt;">[Archived]</span>
                        @endif
                    </div>
                </td>
                <td style="vertical-align:top;padding:0;padding-left:10px;padding-top:8px;"></td>
            </tr>
            <!-- Row: Phone → Invoice No -->
            <tr>
                <td style="vertical-align:top;padding:0;padding-right:15px;">
                    <div class="customer-detail">
                        @if ($customer && $customer->phone_number)
                            <strong>Phone:</strong> {{ $customer->phone_number }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align:top;padding:0;padding-left:10px;">
                    <div class="invoice-meta-line"><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</div>
                </td>
            </tr>
            <!-- Row: Area → Tanggal -->
            <tr>
                <td style="vertical-align:top;padding:0;padding-right:15px;">
                    <div class="customer-detail">
                        @if ($area)
                            <strong>Area:</strong> {{ $area->name }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align:top;padding:0;padding-left:10px;">
                    <div class="invoice-meta-line"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}</div>
                </td>
            </tr>
            <!-- Row: Address → Jatuh Tempo -->
            <tr>
                <td style="vertical-align:top;padding:0;padding-right:15px;">
                    <div class="customer-detail">
                        @if ($address)
                            <strong>Address:</strong>
                            {{ \Illuminate\Support\Str::title($address->full_address) }}
                            @if ($address->trashed())
                                <span style="color:#dc3545;font-size:7pt;">[Archived]</span>
                            @endif
                        @endif
                    </div>
                </td>
                <td style="vertical-align:top;padding:0;padding-left:10px;">
                    <div class="invoice-meta-line"><strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
                </td>
            </tr>
        </table>

        <div class="section-spacer"></div>

        <!-- ========== LINE ITEMS TABLE (UNCHANGED) ========== -->
        <table class="items-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center">No.</th>
                    <th>Service Description</th>
                    <th style="width:90px;">Price/Unit</th>
                    <th style="width:50px;">Qty</th>
                    <th style="width:100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($invoice->serviceOrder->items as $item)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="service-name">{{ $item->service->name }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-spacer"></div>

        <!-- ========== SUMMARY + PAYMENT (UNCHANGED) ========== -->
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td class="summary-left" style="vertical-align:top;padding:0;padding-right:15px;">
                    <div class="payment-heading">Payment Method</div>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;font-weight:bold;color:#333;width:100px;">Bank</td>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;">: {{ \App\Models\AppSetting::get('bank_name', 'BCA') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;font-weight:bold;color:#333;">Account No</td>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;">: {{ \App\Models\AppSetting::get('bank_account_no', '5933068888') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;font-weight:bold;color:#333;">Account Name</td>
                            <td style="border:none;padding:2px 0;font-size:8pt;color:#555;">: {{ \App\Models\AppSetting::get('bank_account_name', 'PT. Kilau Elok Indonesia') }}</td>
                        </tr>
                    </table>
                    <div class="payment-note">
                        Pembayaran paling lambat pada tanggal {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}.
                    </div>

                    @if ($invoice->notes)
                        <div style="margin-top:10px;">
                            <div class="payment-heading">Notes</div>
                            <div style="font-size:8pt;color:#555;">{{ $invoice->notes }}</div>
                        </div>
                    @endif
                </td>
                <td style="width:55%;vertical-align:top;padding:0;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;color:#666;text-align:left;">Subtotal</td>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;font-weight:bold;color:#333;text-align:right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;color:#666;text-align:left;">
                                Discount
                                @if ($invoice->discount_type === 'percentage')
                                    ({{ $invoice->discount }}%)
                                @endif
                            </td>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;font-weight:bold;color:#333;text-align:right;">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;color:#666;text-align:left;">Transport Fee</td>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;font-weight:bold;color:#333;text-align:right;">Rp {{ number_format($invoice->transport_fee, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:5px 8px;font-size:10pt;font-weight:bold;color:#0a3d62;text-align:left;border-top:2px solid #0a3d62;">Grand Total</td>
                            <td style="border:none;padding:5px 8px;font-size:10pt;font-weight:bold;color:#0a3d62;text-align:right;border-top:2px solid #0a3d62;">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;color:#666;text-align:left;">
                                Down Payment
                                @if ($invoice->dp_type === 'percentage')
                                    ({{ $invoice->dp_value }}%)
                                @endif
                            </td>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;font-weight:bold;color:#333;text-align:right;">- Rp {{ number_format($dpAmount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;color:#666;text-align:left;">Total After DP</td>
                            <td style="border:none;padding:3px 8px;font-size:8.5pt;font-weight:bold;color:#333;text-align:right;">Rp {{ number_format($invoice->total_after_dp, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="background-color:#1B9CFC;color:#fff;padding:8px 12px;text-align:right;">
                                <div style="font-size:7pt;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.85);">Amount Due</div>
                                <div style="font-size:13pt;font-weight:bold;color:#fff;">Rp {{ number_format($amountDue, 0, ',', '.') }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ========== WORK PHOTOS ========== -->
        @if($photoArrival && $photoBefore && $photoAfter)
        <div style="{{ $invoice->serviceOrder->items->count() > 5 ? 'page-break-before:always;' : '' }} page-break-inside:avoid;margin-top:16px;">
            <p style="margin-bottom:12px;font-weight:bold;font-size:10pt;color:#0a3d62;text-transform:uppercase;letter-spacing:0.5px;">Work Photos</p>
            <table width="100%" cellspacing="0" cellpadding="6" style="border-collapse:collapse;table-layout:fixed;">
                <tr>
                    <td width="33%" style="text-align:center;vertical-align:top;padding:0 6px;">
                        <img src="{{ storage_path('app/public/' . $photoArrival->file_path) }}"
                             style="width:100%;height:180px;border:1px solid #dee2e6;display:block;"
                             alt="Arrival">
                        <div style="margin-top:6px;font-size:10px;color:#6c757d;font-weight:600;">Arrival</div>
                    </td>
                    <td width="33%" style="text-align:center;vertical-align:top;padding:0 6px;">
                        <img src="{{ storage_path('app/public/' . $photoBefore->file_path) }}"
                             style="width:100%;height:180px;border:1px solid #dee2e6;display:block;"
                             alt="Before">
                        <div style="margin-top:6px;font-size:10px;color:#6c757d;font-weight:600;">Before</div>
                    </td>
                    <td width="33%" style="text-align:center;vertical-align:top;padding:0 6px;">
                        <img src="{{ storage_path('app/public/' . $photoAfter->file_path) }}"
                             style="width:100%;height:180px;border:1px solid #dee2e6;display:block;"
                             alt="After">
                        <div style="margin-top:6px;font-size:10px;color:#6c757d;font-weight:600;">After</div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="height:32px;"></div>
        @endif

    </div><!-- .content-area -->

    <!-- ========== FIX 3: FOOTER — updated contact values + @ icon ========== -->
    <div class="footer">
        <table class="footer-table" style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="border:none;text-align:center;padding:4px 10px;font-size:7pt;color:#666;">
                    <div style="font-size:10pt;margin-bottom:2px;">&#9742;</div>
                    {{ \App\Models\AppSetting::get('app_phone', '+62 899 8846 843') }}
                </td>
                <td style="border:none;text-align:center;padding:4px 10px;font-size:7pt;color:#666;">
                    <div style="font-size:10pt;margin-bottom:2px;">&#9993;</div>
                    {{ \App\Models\AppSetting::get('app_email', 'kleening.id@gmail.com') }}
                </td>
                <td style="border:none;text-align:center;padding:4px 10px;font-size:7pt;color:#666;">
                    <div style="font-size:18pt;font-weight:bold;margin-bottom:2px;color:#666;line-height:1;">&#64;</div>
                    {{ \App\Models\AppSetting::get('app_website', '@kleening.id') }}
                </td>
            </tr>
        </table>
        <div class="footer-disclaimer">
            <sup>*</sup> This invoice is generated automatically by the Kleening.id service platform; no physical signature is required.<br>

            <strong>Kleening.id</strong>
        </div>
    </div>

</body>
</html>