<?php

namespace App\Services;

use App\Models\AppSetting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollExcelGenerator
{
    const HEADER_ROW_BG = '1F3864';
    const COLUMN_HEADER_BG = '2E75B6';
    const COLUMN_HEADER_ALT_BG = '1A4F8A';
    const PRIMARY_ROW_BG = 'EBF3FB';
    const SPLIT_ROW_BG = 'F7F7F7';
    const TOTAL_ROW_BG = '2E75B6';
    const GRAND_TOTAL_ROW_BG = '1F3864';
    const YELLOW_BG = 'FFFFE6';
    const BLUE_TEXT = '0000FF';
    const LATE_PINK = 'FFF0F3';
    const LATE_ORANGE = 'FFA500';
    const LATE_RED = 'FF6B6B';
    const LATE_YELLOW = 'FFFF00';

    const HEADERS = [
        'B' => 'TGL',
        'C' => 'NAMA',
        'D' => 'OMSET',
        'E' => 'TRANS',
        'F' => 'OMS - TRNS',
        'G' => 'KRU',
        'H' => 'QTY',
        'I' => 'JAUH',
        'J' => 'LEMBUR',
        'K' => 'PARKIR',
        'L' => 'HARIAN',
        'M' => '% COM',
        'N' => 'COM',
        'O' => '',
        'P' => 'JAM KERJA',
        'Q' => 'ARRIVAL',
        'R' => 'TIME',
        'S' => 'DENDA',
        'T' => 'BEFORE',
        'U' => 'DENDA',
        'V' => 'AFTER',
        'W' => 'DENDA',
        'X' => 'MESIN PERGI',
        'Y' => 'DENDA',
        'Z' => 'MESIN PULANG',
        'AA' => 'DENDA',
    ];

    const COLUMN_WIDTHS = [
        'A'  => 13.0,
        'B'  => 6.33,   'C' => 38.33,   'D' => 8.66,    'E' => 13.0,
        'F'  => 10.83,  'G' => 30.0,    'H' => 8.66,    'I' => 13.0,
        'J'  => 13.0,   'K' => 13.0,    'L' => 13.0,    'M' => 13.0,
        'N'  => 10.83,  'O' => 4.33,    'P' => 11.83,   'Q' => 10.33,
        'R'  => 8.66,   'S' => 13.0,    'T' => 13.0,    'U' => 13.0,
        'V'  => 13.0,   'W' => 13.0,    'X' => 13.0,    'Y' => 13.0,
        'Z'  => 13.0,   'AA' => 13.0,
    ];

    const TEMPLATE_ROWS_COUNT = 5;

    private int $rowNum = 3;

    public function generate($staff, int $year, int $month, int $period, array $rows, $workPhotos = null, $machineAttendances = null): Spreadsheet
    {
        // Load denda settings from app_settings (fallback to defaults)
        $dendaTelat = (int) AppSetting::get('denda_telat_amount', 10);
        $dendaThreshold = (int) AppSetting::get('denda_telat_threshold', 15);
        $dendaBefore = (int) AppSetting::get('denda_before_photo_amount', 10);
        $dendaAfter = (int) AppSetting::get('denda_after_photo_amount', 10);
        $dendaMesinPergi = (int) AppSetting::get('denda_mesin_pergi_amount', 10);
        $dendaMesinPulang = (int) AppSetting::get('denda_mesin_pulang_amount', 10);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');

        $monthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');
        $periodLabel = "{$monthName} {$year} - Periode {$period}";

        $dateRange = $this->computeDateRange($rows, $monthName, $year);

        $this->writeHeaderInfo($sheet, $staff, $periodLabel, $dateRange);
        $this->writeColumnHeaders($sheet);
        $this->writeDataRows($sheet, $rows, $workPhotos ?? collect(), $machineAttendances ?? collect(), $dendaTelat, $dendaThreshold, $dendaBefore, $dendaAfter, $dendaMesinPergi, $dendaMesinPulang);
        $this->writeTemplateRows($sheet);
        $this->writeTotalRows($sheet);
        $this->setColumnWidths($sheet);
        $this->applyGlobalStyling($sheet);

        return $spreadsheet;
    }

    private function writeHeaderInfo($sheet, $staff, string $periodLabel, string $dateRange): void
    {
        // Single cell: "NAMA: {staffName}" with dark navy bg, white bold text
        $sheet->setCellValue('B1', 'NAMA: ' . $staff->name);
        $sheet->getStyle('B1:AA1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::HEADER_ROW_BG],
            ],
        ]);

        // PERIODE in E1
        $sheet->setCellValue('E1', 'PERIODE: ' . $dateRange);
        $sheet->getStyle('E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ]);
    }

    private function computeDateRange(array $rows, string $monthName, int $year): string
    {
        $dates = [];
        foreach ($rows as $row) {
            $d = $row['date'] ?? null;
            if ($d !== null) {
                $dates[] = $d instanceof Carbon ? $d->day : Carbon::parse($d)->day;
            }
        }

        if (empty($dates)) {
            return '';
        }

        $minDay = min($dates);
        $maxDay = max($dates);

        return sprintf('%02d - %02d %s %d', $minDay, $maxDay, $monthName, $year);
    }

    private function writeColumnHeaders($sheet): void
    {
        foreach (self::HEADERS as $col => $label) {
            $sheet->setCellValue("{$col}2", $label);
        }

        // All column headers: blue bg, white bold text, font size 14
        $sheet->getStyle('B2:AA2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLUMN_HEADER_BG],
            ],
        ]);

        // OMS-TRNS (col F) and COM (col N): darker blue
        foreach (['F', 'N'] as $col) {
            $sheet->getStyle("{$col}2")->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLUMN_HEADER_ALT_BG],
            ]);
        }

        // Separator column O: narrow, keep same bg but no text
        // O is already empty in HEADERS, styled by the B2:AA2 range
    }

    private function writeDataRows($sheet, array $rows, $workPhotos, $machineAttendances, int $dendaTelat, int $dendaThreshold, int $dendaBefore, int $dendaAfter, int $dendaMesinPergi, int $dendaMesinPulang): void
    {
        // Track first row of each SO and first row of each date for photo/mesin checks
        $seenSoIds = [];
        $seenDates = [];

        foreach ($rows as $row) {
            $r = $this->rowNum;
            $rowDate = $row['date'];
            $dayNumber = $rowDate instanceof Carbon ? $rowDate->day : Carbon::parse($rowDate)->day;
            $dateKey = $rowDate instanceof Carbon ? $rowDate->format('Y-m-d') : Carbon::parse($rowDate)->format('Y-m-d');

            // TGL — only on first row of split booking
            if ($row['show_tgl'] ?? true) {
                $sheet->setCellValue("B{$r}", $dayNumber);
            } else {
                $sheet->setCellValueExplicit("B{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            $sheet->setCellValue("C{$r}", $row['customer_name']);

            // OMSET — cast to int to avoid 295.0 text issues
            $omsetVal = (int) round($row['omset'] / 1000);
            $sheet->setCellValueExplicit("D{$r}", $omsetVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            // TRANS — truly empty if null, else numeric
            if ($row['transport'] !== null) {
                $transVal = (int) round($row['transport'] / 1000);
                $sheet->setCellValueExplicit("E{$r}", $transVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("E{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // OMS - TRNS: safe formula for blank transport
            $sheet->setCellValue("F{$r}", "=D{$r}-IF(E{$r}=\"\",0,E{$r})");
            $sheet->getStyle("F{$r}")->getFont()->getColor()->setRGB(self::BLUE_TEXT);

            $sheet->setCellValue("G{$r}", $row['kru']);
            $sheet->setCellValue("H{$r}", (int) $row['qty']);

            // JAUH, LEMBUR, PARKIR — empty + yellow
            $this->setManualInputCells($r, $sheet);

            // HARIAN
            if ($row['harian'] !== null) {
                $sheet->setCellValueExplicit("L{$r}", (int) $row['harian'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("L{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // % COM
            $sheet->setCellValueExplicit("M{$r}", $row['com_rate'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            // COM formula
            $sheet->setCellValue("N{$r}", "=IF(H{$r}>0,ROUND(F{$r}*M{$r}/H{$r},0),0)");
            $sheet->getStyle("N{$r}")->getFont()->getColor()->setRGB(self::BLUE_TEXT);

            // === SHIFTED COLUMNS: JAM KERJA→P, ARRIVAL→Q, TIME→R, DENDA→S ===
            // JAM KERJA (P)
            if (!empty($row['work_time'])) {
                $sheet->setCellValue("P{$r}", $row['work_time']);
            } else {
                $sheet->setCellValueExplicit("P{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // ARRIVAL (Q)
            if (!empty($row['arrival_time'])) {
                $sheet->setCellValue("Q{$r}", $row['arrival_time']);
            } else {
                $sheet->setCellValueExplicit("Q{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // TIME (R) — lateness in minutes
            $lateMinutes = $row['late_minutes'] ?? 0;
            $hasArrival = !empty($row['arrival_time']);
            $sheet->setCellValueExplicit("R{$r}", (int) $lateMinutes, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            // Apply conditional color to TIME cell (saved for re-application after row bg)
            $timeCellColor = $this->getTimeCellColor($lateMinutes, $hasArrival);

            // DENDA (S) — -dendaTelat if late > $dendaThreshold min, else blank
            if ($lateMinutes > $dendaThreshold) {
                $sheet->setCellValueExplicit("S{$r}", -$dendaTelat, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("S{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // === NEW COLUMNS T–AA ===
            // Track SO-first-row and date-first-row
            $soId = $row['service_order_id'] ?? null;
            $isFirstRowOfSo = ($soId !== null && !isset($seenSoIds[$soId]));
            if ($soId !== null) {
                $seenSoIds[$soId] = true;
            }
            $isFirstRowOfDay = !isset($seenDates[$dateKey]);
            if ($isFirstRowOfDay) {
                $seenDates[$dateKey] = true;
            }

            // BEFORE check (T/U) — only on first row of each SO
            if ($isFirstRowOfSo) {
                $photoTypes = $workPhotos[$soId] ?? [];
                $hasBefore = in_array('before', $photoTypes);
                $sheet->setCellValue("T{$r}", $hasBefore ? '✓' : '✗');
                $sheet->setCellValueExplicit("U{$r}", $hasBefore ? null : -$dendaBefore, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("T{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                $sheet->setCellValueExplicit("U{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // AFTER check (V/W) — only on first row of each SO
            if ($isFirstRowOfSo) {
                $photoTypes = $workPhotos[$soId] ?? [];
                $hasAfter = in_array('after', $photoTypes);
                $sheet->setCellValue("V{$r}", $hasAfter ? '✓' : '✗');
                $sheet->setCellValueExplicit("W{$r}", $hasAfter ? null : -$dendaAfter, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("V{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                $sheet->setCellValueExplicit("W{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // MESIN PERGI/PULANG (X/Y/Z/AA) — only on first row of each date
            if ($isFirstRowOfDay) {
                $attendance = $machineAttendances[$dateKey] ?? null;
                $hasPergi = $attendance && !empty($attendance->photo_pergi);
                $sheet->setCellValue("X{$r}", $hasPergi ? '✓' : '✗');
                $sheet->setCellValueExplicit("Y{$r}", $hasPergi ? null : -$dendaMesinPergi, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $hasPulang = $attendance && !empty($attendance->photo_pulang);
                $sheet->setCellValue("Z{$r}", $hasPulang ? '✓' : '✗');
                $sheet->setCellValueExplicit("AA{$r}", $hasPulang ? null : -$dendaMesinPulang, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("X{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                $sheet->setCellValueExplicit("Y{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                $sheet->setCellValueExplicit("Z{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                $sheet->setCellValueExplicit("AA{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            $this->applyRowFormatting($sheet, $r, $row['is_split_row'] ?? false);

            // Re-apply TIME cell color after row background override
            if ($timeCellColor !== null) {
                $sheet->getStyle("R{$r}")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $timeCellColor],
                ]);
            }

            $this->rowNum++;
        }
    }

    private function writeTemplateRows($sheet): void
    {
        for ($i = 0; $i < self::TEMPLATE_ROWS_COUNT; $i++) {
            $r = $this->rowNum;
            $sheet->setCellValueExplicit("B{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("C{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("D{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("E{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);

            // OMS-TRNS: show blank if D is empty, else compute safely
            $sheet->setCellValue("F{$r}", "=IF(D{$r}=\"\",\"\",D{$r}-IF(E{$r}=\"\",0,E{$r}))");
            $sheet->getStyle("F{$r}")->getFont()->getColor()->setRGB(self::BLUE_TEXT);

            $sheet->setCellValueExplicit("G{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("H{$r}", 1, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $this->setManualInputCells($r, $sheet);
            $sheet->setCellValueExplicit("L{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("M{$r}", 0.1, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            // COM: show 0 if F is blank, else compute
            $sheet->setCellValue("N{$r}", "=IF(F{$r}=\"\",0,ROUND(F{$r}*M{$r}/H{$r},0))");
            $sheet->getStyle("N{$r}")->getFont()->getColor()->setRGB(self::BLUE_TEXT);

            // Template rows: blank for shifted + new columns
            $sheet->setCellValueExplicit("O{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("P{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("Q{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("R{$r}", 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle("R{$r}")->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::LATE_PINK],
            ]);
            $sheet->setCellValueExplicit("S{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            // New columns T–AA: empty for template rows
            foreach (['T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'] as $col) {
                $sheet->setCellValueExplicit("{$col}{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            $this->applyRowFormatting($sheet, $r, false); // template rows = not split

            $this->rowNum++;
        }
    }

    private function writeTotalRows($sheet): void
    {
        $templateEndRow = $this->rowNum - 1;

        // No extra blank rows — Total row comes immediately after last data row

        // TOTAL — label in column G (title case), blue bg + white bold
        $sheet->setCellValue("G{$this->rowNum}", "Total");
        $sheet->getStyle("G{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::TOTAL_ROW_BG],
            ],
        ]);

        // Apply blue bg + white text to entire Total row
        $sheet->getStyle("B{$this->rowNum}:AA{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::TOTAL_ROW_BG],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue("I{$this->rowNum}", "=SUM(I3:I{$templateEndRow})");
        $sheet->setCellValue("J{$this->rowNum}", "=SUM(J3:J{$templateEndRow})");
        $sheet->setCellValue("K{$this->rowNum}", "=SUM(K3:K{$templateEndRow})");
        $sheet->setCellValue("L{$this->rowNum}", "=SUM(L3:L{$templateEndRow})");
        $sheet->setCellValue("N{$this->rowNum}", "=SUM(N3:N{$templateEndRow})");

        // DENDA subtotals
        $totalRow = $this->rowNum;
        $sheet->setCellValue("S{$totalRow}", "=SUM(S3:S{$templateEndRow})");   // arrival denda
        $sheet->setCellValue("U{$totalRow}", "=SUM(U3:U{$templateEndRow})");   // before denda
        $sheet->setCellValue("W{$totalRow}", "=SUM(W3:W{$templateEndRow})");   // after denda
        $sheet->setCellValue("Y{$totalRow}", "=SUM(Y3:Y{$templateEndRow})");   // mesin pergi denda
        $sheet->setCellValue("AA{$totalRow}", "=SUM(AA3:AA{$templateEndRow})"); // mesin pulang denda

        // Total Denda label in P, grand sum in Q
        $sheet->setCellValue("P{$totalRow}", "Total Denda");
        $sheet->setCellValue("Q{$totalRow}", "=S{$totalRow}+U{$totalRow}+W{$totalRow}+Y{$totalRow}+AA{$totalRow}");

        $this->applyBorders($sheet, "B{$this->rowNum}:AA{$this->rowNum}");
        foreach (['I', 'J', 'K', 'L', 'N', 'S', 'U', 'W', 'Y', 'AA', 'Q'] as $col) {
            $sheet->getStyle("{$col}{$this->rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $this->rowNum++;

        // GRAND TOTAL — label in column G (title case), dark navy bg + white bold, font size 14
        $sheet->setCellValue("G{$this->rowNum}", "Grand Total");
        $sheet->getStyle("G{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::GRAND_TOTAL_ROW_BG],
            ],
        ]);

        // Apply dark blue bg + white text to entire Grand Total row
        $sheet->getStyle("B{$this->rowNum}:AA{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::GRAND_TOTAL_ROW_BG],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Grand Total = JAUH + LEMBUR + PARKIR + HARIAN + COM + Total Denda (Q is negative, so adding = subtracting fines)
        $sheet->setCellValue("I{$this->rowNum}", "=I{$totalRow}+J{$totalRow}+K{$totalRow}+L{$totalRow}+N{$totalRow}+Q{$totalRow}");
        $sheet->getStyle("I{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'numberFormat' => ['formatCode' => '#,##0'],
        ]);

        $this->applyBorders($sheet, "B{$this->rowNum}:AA{$this->rowNum}");
    }

    private function setManualInputCells(int $rowNum, $sheet): void
    {
        $fill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => self::YELLOW_BG],
        ];
        foreach (['I', 'J', 'K'] as $col) {
            $sheet->setCellValue("{$col}{$rowNum}", '');
            $sheet->getStyle("{$col}{$rowNum}")->getFill()->applyFromArray($fill);
        }
    }

    private function getTimeCellColor(int $lateMinutes, bool $hasArrival): ?string
    {
        if (!$hasArrival) {
            return self::LATE_PINK;
        }
        if ($lateMinutes > 300) {
            return self::LATE_YELLOW;
        }
        if ($lateMinutes > 15) {
            return self::LATE_RED;
        }
        if ($lateMinutes > 0) {
            return self::LATE_ORANGE;
        }
        // On time or early — no special color
        return null;
    }

    private function applyRowFormatting($sheet, int $rowNum, bool $isSplitRow = false): void
    {
        // Row background: primary = PRIMARY_ROW_BG, split = SPLIT_ROW_BG
        $bgColor = $isSplitRow ? self::SPLIT_ROW_BG : self::PRIMARY_ROW_BG;
        $sheet->getStyle("B{$rowNum}:AA{$rowNum}")->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $bgColor],
        ]);

        // Re-apply yellow background for manual input columns (after row bg override)
        $yellowFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => self::YELLOW_BG],
        ];
        foreach (['I', 'J', 'K'] as $col) {
            $sheet->getStyle("{$col}{$rowNum}")->getFill()->applyFromArray($yellowFill);
        }

        // Customer name (col C): bold on primary rows, normal on split rows
        if (!$isSplitRow) {
            $sheet->getStyle("C{$rowNum}")->getFont()->setBold(true);
        }

        $this->applyBorders($sheet, "B{$rowNum}:AA{$rowNum}");

        foreach (['D', 'E', 'F', 'I', 'J', 'K', 'L', 'N', 'S', 'U', 'W', 'Y', 'AA'] as $col) {
            $sheet->getStyle("{$col}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }
        $sheet->getStyle("M{$rowNum}")->getNumberFormat()->setFormatCode('0%');
        $sheet->getStyle("H{$rowNum}")->getNumberFormat()->setFormatCode('0');
    }

    private function applyBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    private function setColumnWidths($sheet): void
    {
        foreach (self::COLUMN_WIDTHS as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function applyGlobalStyling($sheet): void
    {
        $lastRow = $this->rowNum;

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(28);
        for ($r = 3; $r <= $lastRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // Font: Calibri 14 for all cells (bold overrides already set per-cell)
        $sheet->getStyle("B1:AA{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 14,
            ],
        ]);

        // Vertical center for all cells
        $sheet->getStyle("B1:AA{$lastRow}")->applyFromArray([
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Horizontal center for data columns (except C=NAMA and G=KRU which stay left-aligned)
        $centerCols = 'B,D,E,F,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,AA';
        foreach (explode(',', $centerCols) as $col) {
            $sheet->getStyle("{$col}1:{$col}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }
    }
}
