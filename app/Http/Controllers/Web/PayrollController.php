<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrderSession;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Staff;
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
            ->whereHas('user', function ($q) {
                $q->whereRaw('LOWER(TRIM(role)) = ?', ['staff']);
            })
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

        // Query sessions instead of orders — each session = one job/day
        $sessions = OrderSession::whereHas('staff', function ($q) use ($staffId) {
            $q->withoutGlobalScopes()->where('staff.id', $staffId);
        })
            ->whereIn('status', ['proses', 'done']) // only count sessions that actually happened (exclude booked + cancel)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with([
                'serviceOrder' => function ($q) {
                    $q->withoutGlobalScopes()
                        ->with([
                            'invoice',
                            'customer',
                            'items.service.category',
                            'sessions' => function ($sq) {
                                // Load ALL sessions of the parent order (not just this staff's)
                                // We need this to calculate total QTY (man-days)
                                $sq->whereIn('status', ['proses', 'done'])
                                    ->with(['staff' => fn($q2) => $q2->withoutGlobalScopes()]);
                            },
                        ]);
                },
                'staff' => fn($q) => $q->withoutGlobalScopes(), // staff for THIS session
            ])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->get();

        // Build payroll rows from sessions
        $rows = $this->buildPayrollRows($sessions, $staff);

        // Pre-fetch work photos (before/after) grouped by SO
        $allSoIds = $sessions->pluck('service_order_id')->unique()->toArray();
        $workPhotos = WorkPhoto::withoutGlobalScopes()
            ->whereIn('service_order_id', $allSoIds)
            ->whereIn('type', ['before', 'after'])
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->get()
            ->groupBy('service_order_id')
            ->map(fn($photos) => $photos->pluck('type')->unique()->toArray());

        // Pre-fetch machine attendances for this staff in the payroll date range
        $machineAttendances = \App\Models\MachineAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($att) => $att->date->format('Y-m-d'));

        // Generate Excel
        $generator = new PayrollExcelGenerator();
        $spreadsheet = $generator->generate($staff, (int) $year, (int) $month, (int) $period, $rows, $workPhotos, $machineAttendances);

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

    private function buildPayrollRows($sessions, $staff)
    {
        $rows = [];
        $dayTracker = []; // date => counter (how many jobs on this date)
        $orderTransportWritten = []; // track by service_order_id — transport written only once per order

        foreach ($sessions as $session) {
            $order = $session->serviceOrder;
            if (!$order) {
                continue; // skip orphaned sessions
            }

            $orderId = $order->id;
            $isFirstSessionOfOrder = !isset($orderTransportWritten[$orderId]);

            // QTY = total man-days across ALL sessions of this order
            $allSessions = $order->sessions ?? collect();
            $qty = 0;
            foreach ($allSessions as $s) {
                $qty += $s->staff->count();
            }
            // Fallback: if no sessions loaded or empty, use 1 to avoid division by zero
            if ($qty === 0) {
                $qty = 1;
            }

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

            // Staff names for THIS session (not all sessions)
            $kruNames = $session->staff->pluck('name')->join(', ');

            $rowDate = $session->tanggal instanceof Carbon ? $session->tanggal : Carbon::parse($session->tanggal);
            $dateKey = $rowDate->format('Y-m-d');

            // Track booking order on this date for harian logic
            if (!isset($dayTracker[$dateKey])) {
                $dayTracker[$dateKey] = 1;
            } else {
                $dayTracker[$dateKey]++;
            }
            $bookingOrderOnDay = $dayTracker[$dateKey]; // 1 = first, 2+ = subsequent

            // === Arrival photo data (from parent order) ===
            $arrivalPhoto = WorkPhoto::withoutGlobalScopes()
                ->where('service_order_id', $order->id)
                ->where('type', 'arrival')
                ->orderBy('created_at', 'asc')
                ->first();

            // Parse work_time safely — use session's jam field, fallback to order's work_time
            $workTimeStr = null;
            $workMinutes = null;
            $timeToUse = $session->jam ?? $order->work_time;
            if ($timeToUse) {
                try {
                    $workCarbon = Carbon::createFromFormat('H:i:s', $timeToUse);
                    $workTimeStr = $workCarbon->format('H:i');
                    $workMinutes = $workCarbon->hour * 60 + $workCarbon->minute;
                } catch (\Exception $e) {
                    $workTimeStr = null;
                    $workMinutes = null;
                }
            }

            $arrivalTimeStr = $arrivalPhoto ? Carbon::parse($arrivalPhoto->created_at)->format('H:i') : null;

            $lateMinutes = 0;
            if ($workMinutes !== null && $arrivalTimeStr) {
                $arrivalMinutes = (int) Carbon::parse($arrivalPhoto->created_at)->format('H') * 60 + (int) Carbon::parse($arrivalPhoto->created_at)->format('i');

                // Handle midnight crossing: e.g. work_time 23:00, arrival 00:30 next day
                if ($arrivalMinutes < $workMinutes && ($workMinutes - $arrivalMinutes) > 720) {
                    $arrivalMinutes += 1440;
                }

                $lateMinutes = max(0, $arrivalMinutes - $workMinutes);
            }

            // === Split-job logic ===
            if (count($groups) === 1) {
                // === Single com-rate group ===
                $group = $groups[$sortedKeys[0]];

                if ($hasInvoice && $invoiceGrandTotal > 0) {
                    $omset = $invoiceGrandTotal; // FULL grand_total, NOT divided by sessions
                } else {
                    $omset = $group['item_total'];
                }

                // Transport only on first session of this order (ever)
                $transport = null;
                if ($isFirstSessionOfOrder && $hasTransport) {
                    $transport = $transportFee;
                    $orderTransportWritten[$orderId] = true;
                } elseif ($isFirstSessionOfOrder) {
                    $orderTransportWritten[$orderId] = true;
                }

                // Customer name: plain name + category shorthand (NO session suffix)
                $customerName = $order->customer?->name ?? '';

                $rows[] = [
                    'date' => $rowDate,
                    'customer_name' => $customerName,
                    'omset' => $omset,
                    'transport' => $transport,
                    'kru' => $kruNames,
                    'qty' => $qty,
                    'harian' => $bookingOrderOnDay === 1 ? (int) $staff->base_harian : ($staff->harian_tambahan ?? 25),
                    'com_rate' => $group['com_rate'],
                    'show_tgl' => true,
                    'is_split_row' => false,
                    'service_order_id' => $orderId,
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

                // Calculate omset per group (sorted ascending)
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
                        // First row: add transport only on first session of this order
                        $omset = $groupItemTotal + ($isFirstSessionOfOrder && $hasTransport ? $transportFee : 0);
                        $transport = ($isFirstSessionOfOrder && $hasTransport) ? $transportFee : null;
                    } else {
                        $omset = $groupItemTotal;
                        $transport = null;
                    }

                    // Harian only on first group
                    $harian = $isFirstGroup ? ($bookingOrderOnDay === 1 ? (int) $staff->base_harian : ($staff->harian_tambahan ?? 25)) : null;

                    // TGL only on first group of first session
                    $showTgl = $isFirstGroup && $isFirstSessionOfOrder;

                    // Customer name: first row as-is, subsequent append category shorthand
                    $customerName = $order->customer?->name ?? '';
                    if (!$isFirstGroup) {
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
                        'is_split_row' => !$isFirstGroup,
                        'service_order_id' => $orderId,
                        'work_time' => $isFirstGroup ? $workTimeStr : null,
                        'arrival_time' => $isFirstGroup ? $arrivalTimeStr : null,
                        'late_minutes' => $isFirstGroup ? $lateMinutes : 0,
                    ];

                    $isFirstGroup = false;
                }

                // Mark transport as written after processing all groups
                if ($isFirstSessionOfOrder) {
                    $orderTransportWritten[$orderId] = true;
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
