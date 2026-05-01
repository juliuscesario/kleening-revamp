<?php

namespace App\Services;

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
        'O' => 'JAM KERJA',
        'P' => 'SAMPAI',
        'Q' => 'TIME',
        'R' => 'DENDA',
    ];

    const COLUMN_WIDTHS = [
        'B' => 5, 'C' => 22, 'D' => 12, 'E' => 10,
        'F' => 14, 'G' => 25, 'H' => 6, 'I' => 10,
        'J' => 10, 'K' => 10, 'L' => 10, 'M' => 8, 'N' => 12,
        'O' => 10, 'P' => 10, 'Q' => 8, 'R' => 8,
    ];

    const TEMPLATE_ROWS_COUNT = 5;

    private int $rowNum = 3;

    public function generate($staff, int $year, int $month, int $period, array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');

        $monthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');
        $periodLabel = "{$monthName} {$year} - Periode {$period}";

        $dateRange = $this->computeDateRange($rows, $monthName, $year);

        $this->writeHeaderInfo($sheet, $staff, $periodLabel, $dateRange);
        $this->writeColumnHeaders($sheet);
        $this->writeDataRows($sheet, $rows);
        $this->writeTemplateRows($sheet);
        $this->writeTotalRows($sheet);
        $this->setColumnWidths($sheet);

        return $spreadsheet;
    }

    private function writeHeaderInfo($sheet, $staff, string $periodLabel, string $dateRange): void
    {
        // Single cell: "NAMA: {staffName}" with dark navy bg, white bold text
        $sheet->setCellValue('B1', 'NAMA: ' . $staff->name);
        $sheet->getStyle('B1:R1')->applyFromArray([
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
        $sheet->getStyle('B2:R2')->applyFromArray([
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
    }

    private function writeDataRows($sheet, array $rows): void
    {
        foreach ($rows as $row) {
            $r = $this->rowNum;
            $rowDate = $row['date'];
            $dayNumber = $rowDate instanceof Carbon ? $rowDate->day : Carbon::parse($rowDate)->day;

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

            // === NEW COLUMNS: JAM KERJA, SAMPAI, TIME, DENDA ===
            // JAM KERJA (O)
            if (!empty($row['work_time'])) {
                $sheet->setCellValue("O{$r}", $row['work_time']);
            } else {
                $sheet->setCellValueExplicit("O{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // SAMPAI (P)
            if (!empty($row['arrival_time'])) {
                $sheet->setCellValue("P{$r}", $row['arrival_time']);
            } else {
                $sheet->setCellValueExplicit("P{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            // TIME (Q) — lateness in minutes
            $lateMinutes = $row['late_minutes'] ?? 0;
            $hasArrival = !empty($row['arrival_time']);
            $sheet->setCellValueExplicit("Q{$r}", (int) $lateMinutes, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            // Apply conditional color to TIME cell (saved for re-application after row bg)
            $timeCellColor = $this->getTimeCellColor($lateMinutes, $hasArrival);

            // DENDA (R) — -10 if late > 15 min, else blank
            if ($lateMinutes > 15) {
                $sheet->setCellValueExplicit("R{$r}", -10, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("R{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }

            $this->applyRowFormatting($sheet, $r, $row['is_split_row'] ?? false);

            // Re-apply TIME cell color after row background override
            if ($timeCellColor !== null) {
                $sheet->getStyle("Q{$r}")->getFill()->applyFromArray([
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

            // Template rows: blank for new columns
            $sheet->setCellValueExplicit("O{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("P{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->setCellValueExplicit("Q{$r}", 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle("Q{$r}")->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::LATE_PINK],
            ]);
            $sheet->setCellValueExplicit("R{$r}", null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);

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
        $sheet->getStyle("B{$this->rowNum}:R{$this->rowNum}")->applyFromArray([
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
        $sheet->setCellValue("R{$this->rowNum}", "=SUM(R3:R{$templateEndRow})");

        $this->applyBorders($sheet, "B{$this->rowNum}:R{$this->rowNum}");
        foreach (['I', 'J', 'K', 'L', 'N', 'R'] as $col) {
            $sheet->getStyle("{$col}{$this->rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $totalRow = $this->rowNum;
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
        $sheet->getStyle("B{$this->rowNum}:R{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::GRAND_TOTAL_ROW_BG],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Grand Total = JAUH + LEMBUR + PARKIR + HARIAN + COM + DENDA
        $sheet->setCellValue("I{$this->rowNum}", "=I{$totalRow}+J{$totalRow}+K{$totalRow}+L{$totalRow}+N{$totalRow}+R{$totalRow}");
        $sheet->getStyle("I{$this->rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'numberFormat' => ['formatCode' => '#,##0'],
        ]);

        $this->applyBorders($sheet, "B{$this->rowNum}:R{$this->rowNum}");
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
        $sheet->getStyle("B{$rowNum}:R{$rowNum}")->getFill()->applyFromArray([
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

        $this->applyBorders($sheet, "B{$rowNum}:R{$rowNum}");

        foreach (['D', 'E', 'F', 'I', 'J', 'K', 'L', 'N', 'R'] as $col) {
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
}
