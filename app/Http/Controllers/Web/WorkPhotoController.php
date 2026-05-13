<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WorkPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class WorkPhotoController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $photos = WorkPhoto::whereIn('type', ['arrival', 'before', 'after'])
            ->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->with('serviceOrder.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by service_order_id
        $groups = [];
        foreach ($photos as $photo) {
            $soId = $photo->service_order_id;
            if (!isset($groups[$soId])) {
                $groups[$soId] = [
                    'so_number' => $photo->serviceOrder->so_number ?? 'N/A',
                    'customer_name' => $photo->serviceOrder->customer->name ?? 'Unknown',
                    'created_at' => $photo->created_at,
                    'arrival' => [],
                    'before' => [],
                    'after' => [],
                ];
            }
            $groups[$soId][$photo->type][] = $photo;
        }

        // Sort groups by created_at descending
        uasort($groups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return view('pages.work-photos.index', compact('groups', 'dateFrom', 'dateTo'));
    }

    public function download(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $photos = WorkPhoto::whereIn('type', ['arrival', 'before', 'after'])
            ->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->with('serviceOrder.customer')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'No work photos found for this period.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'work_photos_') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Group by customer, then order photos by type
        $typeOrder = ['arrival' => '1. Arrival', 'before' => '2. Before', 'after' => '3. After'];

        // Build customer groups: preserve first-seen customer name per service_order_id
        $customerNames = [];
        $customerPhotos = [];
        foreach ($photos as $photo) {
            $soId = $photo->service_order_id;
            if (!isset($customerNames[$soId])) {
                $customerNames[$soId] = $photo->serviceOrder->customer->name ?? 'Unknown';
            }
            $customerPhotos[$soId][] = $photo;
        }

        // Build customer folders: key = sanitized name
        $customerFolders = [];
        foreach ($customerPhotos as $soId => $photosList) {
            $customerName = $customerNames[$soId];
            $sanitizedName = $this->sanitizeFileName($customerName);
            if (!isset($customerFolders[$sanitizedName])) {
                $customerFolders[$sanitizedName] = [
                    'original_name' => $customerName,
                    'photos' => [],
                ];
            }
            // Merge photos into this customer's collection
            foreach ($photosList as $photo) {
                $customerFolders[$sanitizedName]['photos'][] = $photo;
            }
        }

        // Sort photos within each customer by type order, then by created_at
        foreach ($customerFolders as &$folder) {
            usort($folder['photos'], function ($a, $b) use ($typeOrder) {
                $orderA = $typeOrder[$a->type] ?? 9;
                $orderB = $typeOrder[$b->type] ?? 9;
                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }
                return $a->created_at->timestamp <=> $b->created_at->timestamp;
            });
        }
        unset($folder);

        // Add files to ZIP
        foreach ($customerFolders as $folderName => $folder) {
            $nameCounter = [];
            foreach ($folder['photos'] as $photo) {
                $typeLabel = $typeOrder[$photo->type] ?? ucfirst($photo->type);
                $ext = pathinfo($photo->file_path, PATHINFO_EXTENSION) ?: 'jpg';

                $key = $typeLabel;
                $nameCounter[$key] = ($nameCounter[$key] ?? 0) + 1;

                $fileName = "{$typeLabel}.{$ext}";
                if ($nameCounter[$key] > 1) {
                    $fileName = "{$typeLabel} ({$nameCounter[$key]}).{$ext}";
                }

                $zipFileName = "{$folderName}/{$fileName}";

                $fullPath = storage_path('app/public/' . $photo->file_path);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $zipFileName);
                }
            }
        }

        $zip->close();

        $downloadName = "Work Photos {$dateFrom} to {$dateTo}.zip";

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    public function downloadSingle(\App\Models\ServiceOrder $serviceOrder)
    {
        $photos = $serviceOrder->workPhotos()
            ->whereIn('type', ['arrival', 'before', 'after'])
            ->orderByRaw("CASE type WHEN 'arrival' THEN 1 WHEN 'before' THEN 2 WHEN 'after' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'asc')
            ->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'No work photos found for this service order.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'work_photos_') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $nameCounter = [];
        $customerName = $serviceOrder->customer->name ?? 'Unknown';
        $sanitizedName = $this->sanitizeFileName($customerName);

        $typeOrder = ['arrival' => '1. Arrival', 'before' => '2. Before', 'after' => '3. After'];

        foreach ($photos as $photo) {
            $typeLabel = $typeOrder[$photo->type] ?? ucfirst($photo->type);
            $ext = pathinfo($photo->file_path, PATHINFO_EXTENSION) ?: 'jpg';
            $baseName = "{$typeLabel} - {$sanitizedName}";

            $key = "{$typeLabel}";
            $nameCounter[$key] = ($nameCounter[$key] ?? 0) + 1;

            if ($nameCounter[$key] > 1) {
                $baseName .= " ({$nameCounter[$key]})";
            }

            $zipFileName = "{$baseName}.{$ext}";

            $fullPath = storage_path('app/public/' . $photo->file_path);
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $zipFileName);
            }
        }

        $zip->close();

        $downloadName = "Work Photos - {$sanitizedName}.zip";

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function sanitizeFileName(string $name): string
    {
        // Remove special characters, keep letters, numbers, spaces, hyphens, underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-_]/u', '', $name);
        // Collapse multiple spaces
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        return trim($sanitized);
    }
}
