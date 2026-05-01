<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PayrollExcelGenerator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollExcelGeneratorTest extends TestCase
{
    /**
     * Helper: generate Excel with given rows and return the spreadsheet.
     */
    private function generateWithRows(array $rows): Spreadsheet
    {
        $staff = (object) ['name' => 'Test Staff'];
        $generator = new PayrollExcelGenerator();
        return $generator->generate($staff, 2026, 5, 1, $rows);
    }

    /**
     * Helper: build a single row with controlled time values.
     */
    private function makeRow(string $workTime, ?string $arrivalTime, int $lateMinutes): array
    {
        return [
            'date' => Carbon::create(2026, 5, 1),
            'customer_name' => 'Test Customer',
            'omset' => 100000,
            'transport' => null,
            'kru' => 'Test Staff',
            'qty' => 1,
            'harian' => 80,
            'com_rate' => 0.10,
            'show_tgl' => true,
            'work_time' => $workTime,
            'arrival_time' => $arrivalTime,
            'late_minutes' => $lateMinutes,
        ];
    }

    // ─── Test: No arrival photo → pink fill, denda blank ───
    public function test_no_arrival_photo_is_pink_and_no_denda(): void
    {
        $rows = [$this->makeRow('08:00', null, 0)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        // Q3 = late_minutes = 0
        $this->assertEquals(0, $sheet->getCell('Q3')->getValue());

        // Q3 fill should be pink
        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(Fill::FILL_SOLID, $fill->getFillType());
        $this->assertEquals(PayrollExcelGenerator::LATE_PINK, $fill->getStartColor()->getRGB());

        // R3 = denda should be null/empty
        $this->assertNull($sheet->getCell('R3')->getValue());
    }

    // ─── Test: Late 5 minutes (1-15) → orange fill, denda blank ───
    public function test_late_5_minutes_is_orange_and_no_denda(): void
    {
        $rows = [$this->makeRow('08:00', '08:05', 5)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(5, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(Fill::FILL_SOLID, $fill->getFillType());
        $this->assertEquals(PayrollExcelGenerator::LATE_ORANGE, $fill->getStartColor()->getRGB());

        $this->assertNull($sheet->getCell('R3')->getValue());
    }

    // ─── Test: Late 16 minutes (>15) → red fill, denda = 10 ───
    public function test_late_16_minutes_is_red_and_denda_10(): void
    {
        $rows = [$this->makeRow('08:00', '08:16', 16)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(16, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(Fill::FILL_SOLID, $fill->getFillType());
        $this->assertEquals(PayrollExcelGenerator::LATE_RED, $fill->getStartColor()->getRGB());

        // R3 = denda = 10
        $this->assertEquals(10, $sheet->getCell('R3')->getValue());
    }

    // ─── Test: Late 20 minutes → red fill, denda = 10 ───
    public function test_late_20_minutes_is_red_and_denda_10(): void
    {
        $rows = [$this->makeRow('09:00', '09:20', 20)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(20, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(PayrollExcelGenerator::LATE_RED, $fill->getStartColor()->getRGB());

        $this->assertEquals(10, $sheet->getCell('R3')->getValue());
    }

    // ─── Test: Late >300 minutes → yellow fill (overrides red), denda = 10 ───
    public function test_late_301_minutes_is_yellow_and_denda_10(): void
    {
        $rows = [$this->makeRow('08:00', '13:01', 301)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(301, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(PayrollExcelGenerator::LATE_YELLOW, $fill->getStartColor()->getRGB());

        $this->assertEquals(10, $sheet->getCell('R3')->getValue());
    }

    // ─── Test: On time (0 minutes late) → no fill, denda blank ───
    public function test_on_time_is_no_fill_and_no_denda(): void
    {
        $rows = [$this->makeRow('08:00', '08:00', 0)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(0, $sheet->getCell('Q3')->getValue());

        // No fill means fill type is none
        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(Fill::FILL_NONE, $fill->getFillType());

        $this->assertNull($sheet->getCell('R3')->getValue());
    }

    // ─── Test: Early (arrived before work time) → no fill, denda blank ───
    public function test_early_is_no_fill_and_no_denda(): void
    {
        $rows = [$this->makeRow('08:00', '07:50', 0)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(0, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(Fill::FILL_NONE, $fill->getFillType());

        $this->assertNull($sheet->getCell('R3')->getValue());
    }

    // ─── Test: Exactly 15 minutes → orange (not red, since >15 not >=15) ───
    public function test_late_exactly_15_minutes_is_orange_not_red(): void
    {
        $rows = [$this->makeRow('08:00', '08:15', 15)];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals(15, $sheet->getCell('Q3')->getValue());

        $fill = $sheet->getStyle('Q3')->getFill();
        $this->assertEquals(PayrollExcelGenerator::LATE_ORANGE, $fill->getStartColor()->getRGB());

        // Denda only if > 15
        $this->assertNull($sheet->getCell('R3')->getValue());
    }

    // ─── Test: Multiple rows — each row has correct color ───
    public function test_multiple_rows_each_have_correct_color(): void
    {
        $rows = [
            $this->makeRow('08:00', null, 0),       // row 3: pink
            $this->makeRow('09:00', '09:10', 10),   // row 4: orange
            $this->makeRow('10:00', '10:20', 20),   // row 5: red + denda 10
            $this->makeRow('11:00', '11:00', 0),    // row 6: no fill
        ];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        // Row 3: pink
        $this->assertEquals(PayrollExcelGenerator::LATE_PINK, $sheet->getStyle('Q3')->getFill()->getStartColor()->getRGB());
        $this->assertNull($sheet->getCell('R3')->getValue());

        // Row 4: orange
        $this->assertEquals(PayrollExcelGenerator::LATE_ORANGE, $sheet->getStyle('Q4')->getFill()->getStartColor()->getRGB());
        $this->assertNull($sheet->getCell('R4')->getValue());

        // Row 5: red + denda 10
        $this->assertEquals(PayrollExcelGenerator::LATE_RED, $sheet->getStyle('Q5')->getFill()->getStartColor()->getRGB());
        $this->assertEquals(10, $sheet->getCell('R5')->getValue());

        // Row 6: no fill
        $this->assertEquals(Fill::FILL_NONE, $sheet->getStyle('Q6')->getFill()->getFillType());
        $this->assertNull($sheet->getCell('R6')->getValue());
    }

    // ─── Test: DENDA total row sums correctly ───
    public function test_denda_total_row_sums_correctly(): void
    {
        $rows = [
            $this->makeRow('08:00', '08:20', 20),   // denda 10
            $this->makeRow('09:00', '09:00', 0),     // denda blank
            $this->makeRow('10:00', '10:30', 30),   // denda 10
        ];
        $spreadsheet = $this->generateWithRows($rows);
        $sheet = $spreadsheet->getActiveSheet();

        // Data rows: 3, 4, 5. Template rows: 6-10. TOTAL row: 11. GRAND TOTAL: 12.
        $totalFormula = $sheet->getCell('R11')->getValue();
        $this->assertEquals('=SUM(R3:R10)', $totalFormula);

        $grandTotalFormula = $sheet->getCell('M12')->getValue();
        $this->assertStringContainsString('+R11', $grandTotalFormula);
    }
}
