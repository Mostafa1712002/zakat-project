<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UxAnalysisController extends Controller
{
    public function index()
    {
        $analysisDir = base_path('gallery/ux-analysis');

        // Get all analysis files
        $modules = [
            'dashboard' => 'لوحة التحكم',
            'invoices' => 'الفواتير',
            'purchases' => 'المشتريات',
            'employees' => 'الموظفين',
            'customers' => 'العملاء',
            'products' => 'المنتجات',
            'suppliers' => 'الموردين',
            'warehouses' => 'المخازن',
            'sales' => 'المبيعات',
            'expenses' => 'المصروفات',
            'reports' => 'التقارير',
        ];

        $analyses = [];
        foreach ($modules as $key => $name) {
            $file = $analysisDir . '/' . $key . '-analysis.md';
            if (File::exists($file)) {
                $analyses[$key] = [
                    'name' => $name,
                    'content' => File::get($file),
                ];
            }
        }

        // Get comprehensive docs
        $docs = [
            'user-journey' => [
                'name' => 'خريطة رحلة المستخدم',
                'file' => 'complete-user-journey.md',
            ],
            'crud-guide' => [
                'name' => 'دليل عمليات CRUD',
                'file' => 'crud-operations-guide.md',
            ],
            'architecture' => [
                'name' => 'بنية النظام',
                'file' => 'system-architecture.md',
            ],
        ];

        $documentation = [];
        foreach ($docs as $key => $doc) {
            $file = $analysisDir . '/' . $doc['file'];
            if (File::exists($file)) {
                $documentation[$key] = [
                    'name' => $doc['name'],
                    'content' => File::get($file),
                ];
            }
        }

        return view('ux-analysis.index', compact('analyses', 'documentation'));
    }

    public function show($module)
    {
        $analysisDir = base_path('gallery/ux-analysis');

        $modules = [
            'dashboard' => 'لوحة التحكم',
            'invoices' => 'الفواتير',
            'purchases' => 'المشتريات',
            'employees' => 'الموظفين',
            'customers' => 'العملاء',
            'products' => 'المنتجات',
            'suppliers' => 'الموردين',
            'warehouses' => 'المخازن',
            'sales' => 'المبيعات',
            'expenses' => 'المصروفات',
            'reports' => 'التقارير',
            'user-journey' => 'خريطة رحلة المستخدم',
            'crud-guide' => 'دليل عمليات CRUD',
            'architecture' => 'بنية النظام',
        ];

        $fileName = match($module) {
            'user-journey' => 'complete-user-journey.md',
            'crud-guide' => 'crud-operations-guide.md',
            'architecture' => 'system-architecture.md',
            default => $module . '-analysis.md',
        };

        $file = $analysisDir . '/' . $fileName;

        if (!File::exists($file)) {
            abort(404);
        }

        $content = File::get($file);
        $title = $modules[$module] ?? $module;

        return view('ux-analysis.show', compact('content', 'title', 'module'));
    }
}
