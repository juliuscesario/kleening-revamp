<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FormOrderParser
{
    /**
     * Indonesian month name mapping (full and abbreviated)
     */
    protected array $indonesianMonths = [
        'januari' => 1, 'jan' => 1,
        'februari' => 2, 'feb' => 2,
        'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5,
        'juni' => 6, 'jun' => 6,
        'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'ags' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9,
        'oktober' => 10, 'okt' => 10,
        'november' => 11, 'nov' => 11,
        'desember' => 12, 'des' => 12, 'dec' => 12,
    ];

    /**
     * Known field identifiers to stop service collection
     */
    protected array $knownFieldPatterns = [
        '/^(notes|catatan)\s*:/i',
        '/^tanggal\s*kerja\s*:/i',
        '/^jam\s*(kedatangan)?\s*:/i',
        '/^nama\s*:/i',
        '/^no\s*hp\s*:/i',
        '/^daya\s*listrik/i',
        '/^alamat\s*(lengkap)?\s*:/i',
        '/^google\s*maps?\s*:/i',
    ];

    /**
     * Parse raw WhatsApp form order text into structured data.
     */
    public function parse(string $rawText): array
    {
        $lines = explode("\n", $rawText);
        $lines = array_map(fn($line) => $this->cleanUnicode($line), $lines);

        $result = [
            'tanggal_kerja' => null,
            'tanggal_kerja_raw' => null,
            'jam' => null,
            'nama' => null,
            'no_hp' => null,
            'alamat' => null,
            'google_maps' => '',
            'services_raw' => '',
            'notes' => '',
            'geocoding_success' => false,
        ];

        $inServicesSection = false;
        $serviceLines = [];
        $notesValue = null;
        $dayaListrik = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and header
            if (empty($trimmed) || stripos($trimmed, 'form order kleening') !== false) {
                continue;
            }

            // Check if we're in services section and hit a known field
            if ($inServicesSection && $this->isKnownField($trimmed)) {
                $inServicesSection = false;
            }

            if ($inServicesSection) {
                if (!empty($trimmed)) {
                    $serviceLines[] = $trimmed;
                }
                continue;
            }

            // Parse each field
            if ($this->matchField($trimmed, 'tanggal kerja', $value)) {
                $parsed = $this->parseDate($value);
                if ($parsed) {
                    $result['tanggal_kerja'] = $parsed['display'];
                    $result['tanggal_kerja_raw'] = $parsed['raw'];
                }
            } elseif ($this->matchField($trimmed, 'jam kedatangan', $value) || $this->matchField($trimmed, 'jam', $value)) {
                $result['jam'] = $this->parseTime($value);
            } elseif ($this->matchField($trimmed, 'nama', $value)) {
                $result['nama'] = $this->cleanName($value);
            } elseif ($this->matchField($trimmed, 'no hp', $value) || $this->matchField($trimmed, 'nohp', $value) || $this->matchField($trimmed, 'hp', $value) || $this->matchField($trimmed, 'phone', $value) || $this->matchField($trimmed, 'telp', $value) || $this->matchField($trimmed, 'telepon', $value) || $this->matchField($trimmed, 'wa', $value) || $this->matchField($trimmed, 'whatsapp', $value)) {
                $result['no_hp'] = $this->normalizePhone($value);
            } elseif ($this->matchFieldFlexible($trimmed, 'daya listrik', $value) || $this->matchFieldFlexible($trimmed, 'watt', $value)) {
                $dayaListrik = $this->extractNumeric($value);
            } elseif ($this->matchField($trimmed, 'alamat lengkap', $value) || $this->matchField($trimmed, 'alamat', $value)) {
                $result['alamat'] = trim($value);
            } elseif ($this->matchField($trimmed, 'google maps', $value) || $this->matchField($trimmed, 'maps', $value)) {
                $result['google_maps'] = trim($value);
            } elseif ($this->matchField($trimmed, 'notes', $value) || $this->matchField($trimmed, 'catatan', $value)) {
                $notesValue = trim($value);
            } elseif (stripos($trimmed, 'service') !== false && Str::endsWith($trimmed, ':')) {
                $inServicesSection = true;
            }
        }

        // Compile services
        $result['services_raw'] = implode("\n", $serviceLines);

        // Compile notes with daya listrik
        $result['notes'] = $this->compileNotes($notesValue, $dayaListrik);

        // NOTE: Geocoding is NOT done here — it's done by the controller
        // only when the customer is confirmed as NEW.

        return $result;
    }

    /**
     * Enrich parsed data with geocoding.
     * Called by the controller when customer is NOT found.
     */
    public function enrichWithGeocoding(array $result): array
    {
        if (empty($result['alamat'])) {
            return $result;
        }

        $geoResult = $this->geocodeAddress($result['alamat']);
        if ($geoResult) {
            $result['alamat'] = $geoResult['formatted_address'];
            $result['google_maps'] = $geoResult['google_maps_url'];
            $result['geocoding_success'] = true;
        }

        return $result;
    }

    /**
     * Clean invisible Unicode characters from text.
     */
    protected function cleanUnicode(string $text): string
    {
        // Remove zero-width space, zero-width non-joiner, zero-width joiner, word joiner, byte order mark
        $text = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}]/u', '', $text);
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Match a field line and extract value after colon.
     */
    protected function matchField(string $line, string $fieldName, ?string &$value = null): bool
    {
        $pattern = '/^' . preg_quote($fieldName, '/') . '\s*:\s*(.*)$/i';
        if (preg_match($pattern, $line, $matches)) {
            $value = trim($matches[1]);
            return true;
        }
        return false;
    }

    /**
     * Match a field line where extra text may appear between the field name and colon.
     * E.g. "Daya Listrik( watt ) : 2200" — matches "daya listrik", captures "2200".
     */
    protected function matchFieldFlexible(string $line, string $fieldName, ?string &$value = null): bool
    {
        $pattern = '/^' . preg_quote($fieldName, '/') . '.*:\s*(.*)$/i';
        if (preg_match($pattern, $line, $matches)) {
            $value = trim($matches[1]);
            return true;
        }
        return false;
    }

    /**
     * Check if a line matches any known field pattern.
     */
    protected function isKnownField(string $line): bool
    {
        foreach ($this->knownFieldPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse date string into Carbon instance.
     * Handles Indonesian month names and various formats.
     */
    protected function parseDate(string $dateString): ?array
    {
        $dateString = trim($dateString);

        if (empty($dateString)) {
            return null;
        }

        try {
            // Try to replace Indonesian month names with English equivalents for Carbon parsing
            $normalizedDate = $dateString;
            foreach ($this->indonesianMonths as $indo => $monthNum) {
                $pattern = '/\b' . preg_quote($indo, '/') . '\b/i';
                if (preg_match($pattern, $normalizedDate)) {
                    $normalizedDate = preg_replace($pattern, str_pad((string) $monthNum, 2, '0', STR_PAD_LEFT), $normalizedDate, 1);
                    break;
                }
            }

            // Check if replacement happened (Indonesian month found)
            $hasIndonesianMonth = ($normalizedDate !== $dateString);

            if ($hasIndonesianMonth) {
                // Format is now like "28 04 2026" or "28 04 26"
                // Rebuild as Y-m-d for parsing
                $parts = preg_split('/[\s\/\-]+/', $normalizedDate);
                if (count($parts) >= 3) {
                    $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $month = $parts[1];
                    $year = $parts[2];

                    // Handle 2-digit year
                    if (strlen($year) === 2) {
                        $year = '20' . $year;
                    }

                    $carbon = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
                } else {
                    $carbon = Carbon::parse($dateString, 'Asia/Jakarta');
                }
            } elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $dateString, $m)) {
                // Explicit DD/MM/YYYY or DD-MM-YYYY handling
                $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                $year = $m[3];
                if (strlen($year) === 2) {
                    $year = '20' . $year;
                }
                $carbon = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
            } else {
                // Try standard Carbon parsing
                $carbon = Carbon::parse($dateString, 'Asia/Jakarta');
            }

            return [
                'display' => $carbon->locale('id')->isoFormat('D MMMM YYYY'),
                'raw' => $carbon->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse time string into HH:mm format.
     * Handles ranges (takes first value), dots, and single digits.
     */
    protected function parseTime(string $timeString): string
    {
        $timeString = trim($timeString);

        if (empty($timeString)) {
            return '';
        }

        // Handle range: "09.00-10.00" or "09.00 - 10.00" or "9-10"
        if (preg_match('/^([\d\.]+)\s*[-–—]\s*[\d\.]+/', $timeString, $matches)) {
            $timeString = $matches[1];
        }

        // Normalize dot to colon
        $timeString = str_replace('.', ':', $timeString);

        // Handle single digit hour: "9" -> "09:00"
        if (preg_match('/^(\d{1,2})$/', $timeString, $matches)) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':00';
        }

        // Handle "HH:mm" or "H:mm"
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeString, $matches)) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
        }

        return $timeString;
    }

    /**
     * Clean and title-case a name.
     */
    protected function cleanName(string $name): string
    {
        return ucwords(strtolower(trim($name)));
    }

    /**
     * Normalize phone number to digits only, starting with 62.
     */
    protected function normalizePhone(string $phone): string
    {
        // Strip all non-digits
        $phone = preg_replace('/\D/', '', $phone);

        if (empty($phone)) {
            return '';
        }

        // Normalize prefix
        if (Str::startsWith($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (Str::startsWith($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Extract numeric value from string.
     */
    protected function extractNumeric(string $value): ?int
    {
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Compile notes, merging with daya listrik if needed.
     */
    protected function compileNotes(?string $notesValue, ?int $dayaListrik): string
    {
        if ($dayaListrik === null) {
            return $notesValue ?? '';
        }

        $listrikText = 'Listrik ' . $dayaListrik;

        if (empty($notesValue)) {
            return $listrikText;
        }

        // Check if notes already contains this listrik value
        if (stripos($notesValue, $listrikText) !== false) {
            return $notesValue;
        }

        // Also check if any "listrik" + number pattern exists
        if (preg_match('/listrik\s*\d+/i', $notesValue)) {
            return $notesValue;
        }

        return $notesValue . ', ' . $listrikText;
    }

    /**
     * Geocode address using Google Geocoding API.
     * Public so the controller can call it separately.
     */
    public function geocodeAddress(string $address): ?array
    {
        $apiKey = config('services.google.geocoding_key');

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey,
                'region' => 'id',
                'language' => 'id',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['results']) || $data['status'] !== 'OK') {
                return null;
            }

            $result = $data['results'][0];
            $lat = $result['geometry']['location']['lat'];
            $lng = $result['geometry']['location']['lng'];

            return [
                'formatted_address' => $result['formatted_address'],
                'google_maps_url' => "https://www.google.com/maps?q={$lat},{$lng}",
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
