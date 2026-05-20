<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductImportService;

class ProductImportController extends Controller
{
    protected $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showImportForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->store('imports');

        $result = $this->importService->import(storage_path('app/' . $path));

        if ($result['errors']) {
            return redirect()->back()->with('error', 'Import completed with errors: ' . implode('; ', $result['errors']));
        }

        return redirect()->back()->with('success', "Imported {$result['created']} products successfully.");
    }
}
