<?php

namespace App\Services;

use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HadirrService
{
    private string $baseUrl;
    private string $accessKey;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.hadirr.base_url') ?: '';
        $this->accessKey = config('services.hadirr.access_key') ?: '';
        $this->secretKey = config('services.hadirr.secret_key') ?: '';
    }

    /**
     * Authenticate with Hadirr API and get JWT token.
     * Caches token for 25 minutes to avoid re-authenticating every call.
     */
    public function authenticate(): string
    {
        return Cache::remember('hadirr_jwt_token', 25 * 60, function () {
            $credentials = base64_encode($this->accessKey . ':' . $this->secretKey);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $credentials,
            ])->post($this->baseUrl . '/auth');

            if (!$response->successful()) {
                throw new \Exception('Hadirr authentication failed: ' . $response->status());
            }

            $token = $response->header('X-Access-Token');

            if (empty($token)) {
                throw new \Exception('Hadirr authentication failed: No X-Access-Token in response header');
            }

            return $token;
        });
    }

    /**
     * Fetch all attendance records for a single date.
     * Paginates automatically with limit/offset.
     */
    public function getAttendances(string $date): array
    {
        $token = $this->authenticate();
        $allRecords = [];
        $offset = 0;
        $limit = 100;

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($this->baseUrl . '/attendances', [
                'date' => $date,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            // Retry once on 401 (token expired)
            if ($response->status() === 401) {
                $this->clearTokenCache();
                $token = $this->authenticate();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($this->baseUrl . '/attendances', [
                    'date' => $date,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);
            }

            if (!$response->successful()) {
                throw new \Exception("Hadirr API error for date {$date}: " . $response->status());
            }

            $body = $response->json();
            $list = $body['data']['list'] ?? [];
            $allRecords = array_merge($allRecords, $list);
            $offset += $limit;

            // If we got fewer than $limit results, we've reached the end
        } while (count($list) >= $limit);

        return $allRecords;
    }

    /**
     * Sync attendance for a single date. Upserts into staff_attendances table.
     * Returns number of records upserted.
     */
    public function syncDate(string $date): int
    {
        $records = $this->getAttendances($date);
        $count = 0;

        foreach ($records as $record) {
            // Parse short status code from full status string
            // e.g. "PW (Present at Working Day)" → "PW"
            $shortStatus = null;
            if (!empty($record['status'])) {
                preg_match('/^([A-Z]+)/', $record['status'], $matches);
                $shortStatus = $matches[1] ?? $record['status'];
            }

            StaffAttendance::updateOrCreate(
                [
                    'nik' => $record['nik'],
                    'tanggal' => $record['date'],
                ],
                [
                    'nama' => $record['name'],
                    'clock_in' => !empty($record['clock_in']) ? $record['clock_in'] : null,
                    'clock_out' => !empty($record['clock_out']) ? $record['clock_out'] : null,
                    'status' => $shortStatus,
                    'raw_status' => !empty($record['status']) ? $record['status'] : null,
                    'notes' => !empty($record['notes']) ? $record['notes'] : null,
                    'clock_in_location' => !empty($record['clock_in_location']) ? $record['clock_in_location'] : null,
                    'clock_out_location' => !empty($record['clock_out_location']) ? $record['clock_out_location'] : null,
                    'hadirr_raw' => $record,
                    'synced_at' => now(),
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Sync attendance for a date range. Loops per date.
     * Returns ['synced' => int, 'failed_dates' => array]
     */
    public function syncPeriod(Carbon $from, Carbon $to): array
    {
        $synced = 0;
        $failedDates = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->format('Y-m-d');

            try {
                $synced += $this->syncDate($dateStr);
            } catch (\Exception $e) {
                Log::error("Hadirr sync failed for {$dateStr}: " . $e->getMessage());
                $failedDates[] = $dateStr;
            }

            // Small delay to avoid rate limiting
            usleep(200000); // 200ms

            $current->addDay();
        }

        return [
            'synced' => $synced,
            'failed_dates' => $failedDates,
        ];
    }

    /**
     * Clear cached JWT token (useful if auth fails mid-session).
     */
    public function clearTokenCache(): void
    {
        Cache::forget('hadirr_jwt_token');
    }
}
