<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\WorkPhoto;
use App\Services\PayrollExcelGenerator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // Com rates loaded from DB, with 0.10 fallback

    public function index()
    {
        $now = Carbon::now();

        // Auto-select period based on today's date
        $day = $now->day;
        if ($day <= 10) {
            $autoPeriod = 1;
        } elseif ($day <= 20) {
            $autoPeriod = 2;
        } else {
            $autoPeriod = 3;
        }

        $staff = Staff::withoutGlobalScopes()
            ->where('is_active', true)
            ->with('area')
            ->orderBy('name')
            ->get();

        return view('pages.payroll.index', compact('staff', 'now', 'autoPeriod'));
    }

    public function download($staffId, $year, $month, $period)
    {
        // Determine date range based on period
        [$startDate, $endDate] = $this->getDateRange((int) $year, (int) $month, (int) $period);

        $staff = Staff::withoutGlobalScopes()->findOrFail($staffId);

        $orders = ServiceOrder::withoutGlobalScopes()
            ->whereHas('staff', function ($q) use ($staffId) {
                $q->withoutGlobalScopes()->where('staff.id', $staffId);
            })
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with([
                'customer',
                'items.service.category',
                'staff',
                'invoice',
            ])
            ->orderBy('work_date')
            ->orderBy('work_time')
            ->get();

        // Build payroll rows
        $rows = $this->buildPayrollRows($orders, $staff);

        // Generate Excel
        $generator = new PayrollExcelGenerator();
        $spreadsheet = $generator->generate($staff, (int) $year, (int) $month, (int) $period, $rows);

        // Write output
        $writer = new Xlsx($spreadsheet);
        $fileName = $this->getFileName($staff, (int) $year, (int) $month, (int) $period);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function getDateRange($year, $month, $period)
    {
        if ($period === 1) {
            return [
                Carbon::create($year, $month, 1)->format('Y-m-d'),
                Carbon::create($year, $month, 10)->format('Y-m-d'),
            ];
        } elseif ($period === 2) {
            return [
                Carbon::create($year, $month, 11)->format('Y-m-d'),
                Carbon::create($year, $month, 20)->format('Y-m-d'),
            ];
        } else {
            return [
                Carbon::create($year, $month, 21)->format('Y-m-d'),
                Carbon::create($year, $month)->endOfMonth()->format('Y-m-d'),
            ];
        }
    }

    private function getComRate($categoryName)
    {
        // Load from DB, fallback to 0.10
        static $comRates = null;
        if ($comRates === null) {
            $comRates = ServiceCategory::pluck('commission_rate', 'name')
                ->mapWithKeys(fn($rate, $name) => [$name => $rate / 100])
                ->toArray();
        }
        return $comRates[$categoryName] ?? 0.10;
    }

    private function buildPayrollRows($orders, $staff)
    {
        $rows = [];
        $dayTracker = []; // date => counter (how many bookings on this date)

        foreach ($orders as $order) {
            // Step 1 — Group items by com rate (use string keys to avoid float→int coercion)
            $groups = [];
            foreach ($order->items as $item) {
                $categoryName = $item->service?->category?->name ?? 'Unknown';
                $comRate = $this->getComRate($categoryName);
                $comRateKey = number_format($comRate, 2); // "0.10", "0.15", "0.30"

                if (!isset($groups[$comRateKey])) {
                    $groups[$comRateKey] = [
                        'com_rate' => $comRate,
                        'item_total' => 0,
                        'categories' => [], // track categories for shorthand
                    ];
                }
                $groups[$comRateKey]['item_total'] += (float) $item->total;
                $groups[$comRateKey]['categories'][] = $categoryName;
            }

            // Invoice data
            $invoice = $order->invoice;
            $hasInvoice = $invoice !== null;
            $invoiceGrandTotal = $hasInvoice ? (float) $invoice->grand_total : 0;
            $transportFee = $hasInvoice ? (float) ($invoice->transport_fee ?? 0) : 0;
            $hasTransport = $transportFee > 0;

            // Sort groups: 0.10 first, then 0.15, then 0.30
            $sortedKeys = array_keys($groups);
            sort($sortedKeys);

            // Staff names for this order
            $kruNames = $order->staff->pluck('name')->join(', ');
            $qty = $order->staff->count();

            $rowDate = $order->work_date instanceof Carbon ? $order->work_date : Carbon::parse($order->work_date);
            $dateKey = $rowDate->format('Y-m-d');

            // Track booking order on this date for harian logic
            if (!isset($dayTracker[$dateKey])) {
                $dayTracker[$dateKey] = 1;
            } else {
                $dayTracker[$dateKey]++;
            }
            $bookingOrderOnDay = $dayTracker[$dateKey]; // 1 = first, 2+ = subsequent

            // === Arrival photo data ===
            $arrivalPhoto = WorkPhoto::withoutGlobalScopes()
                ->where('service_order_id', $order->id)
                ->where('type', 'arrival')
                ->orderBy('created_at', 'asc')
                ->first();

            $workTimeStr = $order->work_time ? Carbon::createFromFormat('H:i:s', $order->work_time)->format('H:i') : null;
            $arrivalTimeStr = $arrivalPhoto ? Carbon::parse($arrivalPhoto->created_at)->format('H:i') : null;

            $lateMinutes = 0;
            if ($workTimeStr && $arrivalTimeStr) {
                $workMinutes = (int) Carbon::createFromFormat('H:i', $workTimeStr)->format('H') * 60 + (int) Carbon::createFromFormat('H:i', $workTimeStr)->format('i');
                $arrivalMinutes = (int) Carbon::parse($arrivalPhoto->created_at)->format('H') * 60 + (int) Carbon::parse($arrivalPhoto->created_at)->format('i');
                $lateMinutes = max(0, $arrivalMinutes - $workMinutes);
            }

            // Step 2 — Check if split is needed
            if (count($groups) === 1) {
                // === Single com-rate group: omset = invoice grand_total ===
                $group = $groups[$sortedKeys[0]];

                if ($hasInvoice && $invoiceGrandTotal > 0) {
                    $omset = $invoiceGrandTotal;
                } else {
                    // Fallback: sum of item totals
                    $omset = $group['item_total'];
                }

                $transport = $hasTransport ? $transportFee : null;

                $rows[] = [
                    'date' => $rowDate,
                    'customer_name' => $order->customer?->name ?? '',
                    'omset' => $omset,
                    'transport' => $transport,
                    'kru' => $kruNames,
                    'qty' => $qty,
                    'harian' => $bookingOrderOnDay === 1 ? (int) $staff->base_harian : ($staff->harian_tambahan ?? 25),
                    'com_rate' => $group['com_rate'],
                    'show_tgl' => true, // TGL on single-row bookings
                    'work_time' => $workTimeStr,
                    'arrival_time' => $arrivalTimeStr,
                    'late_minutes' => $lateMinutes,
                ];
            } else {
                // === Multiple com-rate groups (split jobs) ===
                $subtotal = $hasInvoice ? (float) $invoice->subtotal : 0;
                $discountVal = $hasInvoice ? (float) ($invoice->discount ?? 0) : 0;

                // Calculate discount amount
                if ($hasInvoice && $invoice->discount_type === 'percentage' && $subtotal > 0) {
                    $discountAmount = $subtotal * ($discountVal / 100);
                } else {
                    $discountAmount = $hasInvoice ? $discountVal : 0;
                }

                // Discount ratio based on subtotal
                $discountRatio = ($subtotal > 0) ? $discountAmount / $subtotal : 0;

                // Step 3 — Calculate omset per group (sorted ascending)
                // groupTakes = discounted item totals (without transport)
                // Transport is added separately on first row
                $groupTakes = [];
                if ($hasInvoice && $invoiceGrandTotal > 0) {
                    $sumAfterDiscount = 0;
                    foreach ($sortedKeys as $key) {
                        $group = $groups[$key];
                        $adjusted = (int) round($group['item_total'] * (1 - $discountRatio));
                        $groupTakes[$key] = $adjusted;
                        $sumAfterDiscount += $adjusted;
                    }

                    // Fix rounding: adjust last group so sum matches discounted subtotal
                    $discountedSubtotal = (int) round($subtotal * (1 - $discountRatio));
                    $diff = $discountedSubtotal - $sumAfterDiscount;
                    $lastKey = $sortedKeys[count($sortedKeys) - 1];
                    $groupTakes[$lastKey] += $diff;
                } else {
                    // No invoice: use item totals directly
                    foreach ($sortedKeys as $key) {
                        $groupTakes[$key] = (int) round($groups[$key]['item_total']);
                    }
                }

                // Build split rows
                $isFirstGroup = true;
                foreach ($sortedKeys as $key) {
                    $group = $groups[$key];
                    $groupItemTotal = $groupTakes[$key];

                    if ($isFirstGroup) {
                        // First row: add transport to omset
                        $omset = $groupItemTotal + ($hasTransport ? $transportFee : 0);
                        $transport = $hasTransport ? $transportFee : null;
                    } else {
                        // Subsequent rows: just discounted total
                        $omset = $groupItemTotal;
                        $transport = null;
                    }

                    // Harian only on first row of booking
                    $harian = $isFirstGroup ? ($bookingOrderOnDay === 1 ? (int) $staff->base_harian : ($staff->harian_tambahan ?? 25)) : null;

                    // TGL only on first row of booking
                    $showTgl = $isFirstGroup;

                    // Customer name: first row as-is, subsequent append category shorthand
                    $customerName = $order->customer?->name ?? '';
                    if (!$isFirstGroup) {
                        // Get unique categories for this group
                        $uniqueCats = array_unique($group['categories']);
                        $shorthand = $this->getCategoryShorthand($uniqueCats);
                        $customerName .= ' ' . $shorthand;
                    }

                    $rows[] = [
                        'date' => $rowDate,
                        'customer_name' => $customerName,
                        'omset' => $omset,
                        'transport' => $transport,
                        'kru' => $kruNames,
                        'qty' => $qty,
                        'harian' => $harian,
                        'com_rate' => $group['com_rate'],
                        'show_tgl' => $showTgl,
                        'work_time' => $isFirstGroup ? $workTimeStr : null,
                        'arrival_time' => $isFirstGroup ? $arrivalTimeStr : null,
                        'late_minutes' => $isFirstGroup ? $lateMinutes : 0,
                    ];

                    $isFirstGroup = false;
                }
            }
        }

        return $rows;
    }

    private function getFileName($staff, $year, $month, $period)
    {
        $monthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');
        // Capitalize first letter for file name
        $monthName = ucfirst($monthName);
        $staffName = strtoupper(str_replace(' ', '', $staff->name));

        return "Payroll_{$staffName}_{$monthName}_{$year}_P{$period}.xlsx";
    }

    /**
     * Get category shorthand for split-row customer names.
     * General Cleaning → GC, Deep Cleaning → DC, Poles → Poles, Poles Lantai → Poles Lantai, others as-is.
     */
    private function getCategoryShorthand(array $categories): string
    {
        $mapping = [
            'General Cleaning' => 'GC',
            'Deep Cleaning' => 'DC',
            'Poles' => 'Poles',
            'Poles Lantai' => 'Poles Lantai',
        ];

        $shorts = [];
        foreach ($categories as $cat) {
            $shorts[] = $mapping[$cat] ?? $cat;
        }

        return implode(', ', array_unique($shorts));
    }
}
