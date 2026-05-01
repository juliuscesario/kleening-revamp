<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollExcelGenerator
{
    const HEADER_BG = 'D9E2F3';
    const BLUE_TEXT = '0000FF';
    const YELLOW_BG = 'FFFFE6';

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
    ];

    const COLUMN_WIDTHS = [
        'B' => 5, 'C' => 22, 'D' => 12, 'E' => 10,
        'F' => 14, 'G' => 25, 'H' => 6, 'I' => 10,
        'J' => 10, 'K' => 10, 'L' => 10, 'M' => 8, 'N' => 12,
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

        $this->writeHeaderInfo($sheet, $staff, $periodLabel);
        $this->writeColumnHeaders($sheet);
        $this->writeDataRows($sheet, $rows);
        $this->writeTemplateRows($sheet);
        $this->writeTotalRows($sheet);
        $this->setColumnWidths($sheet);

        return $spreadsheet;
    }

    private function writeHeaderInfo($sheet, $staff, string $periodLabel): void
    {
        $sheet->setCellValue('B1', 'NAMA:');
        $sheet->setCellValue('C1', $staff->name);
        $sheet->setCellValue('F1', 'BULAN:');
        $sheet->setCellValue('G1', $periodLabel);

        foreach (['B1', 'C1', 'F1', 'G1'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
    }

    private function writeColumnHeaders($sheet): void
    {
        foreach (self::HEADERS as $col => $label) {
            $sheet->setCellValue("{$col}2", $label);
        }

        $sheet->getStyle('B2:N2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::HEADER_BG],
            ],
        ]);

        // Blue text for formula columns
        $sheet->getStyle('F2')->getFont()->getColor()->setRGB(self::BLUE_TEXT);
        $sheet->getStyle('N2')->getFont()->getColor()->setRGB(self::BLUE_TEXT);

        // Yellow background for manual input columns
        $manualFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => self::YELLOW_BG],
        ];
        foreach (['I', 'J', 'K'] as $col) {
            $sheet->getStyle("{$col}2")->getFill()->applyFromArray($manualFill);
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

            $this->applyRowFormatting($sheet, $r);

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

            $this->applyRowFormatting($sheet, $r);

            $this->rowNum++;
        }
    }

    private function writeTotalRows($sheet): void
    {
        $templateEndRow = $this->rowNum - 1;

        // TOTAL
        $sheet->setCellValue("K{$this->rowNum}", "TOTAL");
        $sheet->getStyle("K{$this->rowNum}")->getFont()->setBold(true);

        $sheet->setCellValue("I{$this->rowNum}", "=SUM(I3:I{$templateEndRow})");
        $sheet->setCellValue("J{$this->rowNum}", "=SUM(J3:J{$templateEndRow})");
        $sheet->setCellValue("L{$this->rowNum}", "=SUM(L3:L{$templateEndRow})");
        $sheet->setCellValue("N{$this->rowNum}", "=SUM(N3:N{$templateEndRow})");

        foreach (['I', 'J', 'L', 'N'] as $col) {
            $sheet->getStyle("{$col}{$this->rowNum}")->getFont()->setBold(true);
        }

        $this->applyBorders($sheet, "B{$this->rowNum}:N{$this->rowNum}");
        foreach (['I', 'J', 'L', 'N'] as $col) {
            $sheet->getStyle("{$col}{$this->rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $totalRow = $this->rowNum;
        $this->rowNum++;

        // GRAND TOTAL
        $sheet->setCellValue("J{$this->rowNum}", "GRAND TOTAL");
        $sheet->getStyle("J{$this->rowNum}")->getFont()->setBold(true);
        $sheet->setCellValue("M{$this->rowNum}", "=I{$totalRow}+J{$totalRow}+L{$totalRow}+N{$totalRow}");
        $sheet->getStyle("M{$this->rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("M{$this->rowNum}")->getNumberFormat()->setFormatCode('#,##0');

        $this->applyBorders($sheet, "B{$this->rowNum}:N{$this->rowNum}");
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

    private function applyRowFormatting($sheet, int $rowNum): void
    {
        $this->applyBorders($sheet, "B{$rowNum}:N{$rowNum}");

        foreach (['D', 'E', 'F', 'I', 'J', 'K', 'L', 'N'] as $col) {
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
