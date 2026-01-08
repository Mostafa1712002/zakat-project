<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PortfolioController extends Controller
{
    public function index()
    {
        $galleryPath = public_path('gallery');

        // Get all screenshots
        $screenshots = collect(File::glob($galleryPath . '/*.png'))
            ->map(function ($file) {
                $filename = basename($file);
                preg_match('/^(\d+)-(.+?)-(\d+x\d+)\.png$/', $filename, $matches);

                return [
                    'filename' => $filename,
                    'path' => 'gallery/' . $filename,
                    'order' => $matches[1] ?? 0,
                    'name' => $matches[2] ?? '',
                    'size' => $matches[3] ?? '',
                ];
            })
            ->sortBy('order')
            ->values();

        // Group by viewport size
        $viewports = [
            '1920x1080' => 'Desktop',
            '1366x768' => 'Laptop',
            '768x1024' => 'Tablet',
            '375x812' => 'Mobile',
        ];

        // Check if video exists
        $videoPath = 'gallery/crm-walkthrough.webm';
        $hasVideo = File::exists(public_path($videoPath));

        return view('portfolio.index', compact('screenshots', 'viewports', 'hasVideo', 'videoPath'));
    }
}
