<?php

namespace App\Http\Controllers;

use App\Models\SalesExport;
use Illuminate\Http\Request;
use App\Models\SalesImport; 
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SalesImport as ImportSales;
use App\Imports\SalesExport as ImportExports;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class SalesExportController extends Controller
{
    
    public function index()
    {
        $salesExports = SalesExport::with('contract')->paginate(10);
        return view('sales-exports.index', compact('salesExports'));
    }

    
    public function create()
    {
        return view('sales-exports.create');
    }

   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contract_id' => 'required|exists:sales_contracts,id',
            'invoice_no' => 'nullable|string|max:255',
            'export_bill_no' => 'nullable|string|max:255',
            'amount_usd' => 'required|numeric',
            'realized_value' => 'required|numeric',
            'g_qty_pcs' => 'required|integer',
            'date_of_realized' => 'nullable|date',
            'due_amount_usd' => 'required|numeric',
        ]);

        SalesExport::create($validatedData);

        return redirect()->route('sales-exports.index')->with('success', 'Sales Export created successfully.');
    }

   
    public function show(SalesExport $salesExport)
    {
        return view('sales-exports.show', compact('salesExport'));
    }

   
    public function edit(SalesExport $salesExport)
    {
        return view('sales-exports.edit', compact('salesExport'));
    }

  
    public function update(Request $request, SalesExport $salesExport)
    {
        $validatedData = $request->validate([
            'contract_id' => 'required|exists:sales_contracts,id',
            'invoice_no' => 'nullable|string|max:255',
            'export_bill_no' => 'nullable|string|max:255',
            'amount_usd' => 'required|numeric',
            'realized_value' => 'required|numeric',
            'g_qty_pcs' => 'required|integer',
            'date_of_realized' => 'nullable|date',
            'due_amount_usd' => 'required|numeric',
        ]);

        $salesExport->update($validatedData);

        return redirect()->route('sales-exports.index')->with('success', 'Sales Export updated successfully.');
    }

    
    public function destroy(SalesExport $salesExport)
    {
       
        $salesExport->delete();
        return redirect()->back()->withMessage('Sales Export deleted successfully.');
    }

    

    // Download Templates
    public function downloadImportTemplate()
    {
        
        //find the file prom public\templates\import_template.xlsx and download it
        return response()->download(public_path('templates/import_template.xlsx'));
    }

  

    // Handle File Uploads
    public function processImportUpload(Request $request, $contractId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $importData = Excel::toArray(new ImportSales, $request->file('file'))[0];

        $duplicates = collect($importData)->filter(function ($row) use ($contractId) {
            return SalesImport::where([
                'contract_id' => $contractId,
                'btb_lc_no' => $row['btb_lc_no'] ?? null,
                'date' => isset($row['date']) ? Carbon::parse($row['date']) : null,
                'fabric_value' => $row['fabric_value'] ?? 0,
                'accessories_value' => $row['accessories_value'] ?? 0,
                'fabric_qty_kg' => $row['fabric_qty_in_kgs'] ?? 0,
                'accessories_qty' => $row['accessories_qty'] ?? 0,
                'print_emb_qty' => $row['printing_embroidery_qty'] ?? 0,
                'print_emb_value' => $row['printing_embroidery_value'] ?? 0
            ])->exists();
        });

        Session::put('import_data', $importData);
        Session::put('contract_id', $contractId);

        return view('imports.confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'import'
        ]);
    }

    private function mapImportData($data, $contractId)
    {
        return $data->map(function ($row) use ($contractId) {
            return [
                'contract_id' => $contractId,
                'btb_lc_no' => $row['btb_lc_no'] ?? null,
                'date' => isset($row['date']) ? Carbon::parse($row['date']) : null,
                'description' => $row['description'] ?? 'No description',
                'fabric_value' => $row['fabric_value'] ?? 0,
                'accessories_value' => $row['accessories_value'] ?? 0,
                'fabric_qty_kg' => $row['fabric_qty_in_kgs'] ?? 0,
                'accessories_qty' => $row['accessories_qty'] ?? 0,
                'print_emb_qty' => $row['printing_embroidery_qty'] ?? 0,
                'print_emb_value' => $row['printing_embroidery_value'] ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();
    }

    public function downloadExportTemplate()
    {
        return response()->download(public_path('templates/export_template.xlsx'));
    }

    // public function processExportUpload(Request $request)
    // {
    //     $request->validate(['file' => 'required|mimes:xlsx,xls']);

    //     $exportData = Excel::toArray(new ImportExports, $request->file('file'))[0];
    //     $headers = array_shift($exportData);

    //     $duplicates = $this->findExportDuplicates($exportData);
    //     Session::put('export_data', $exportData);

    //     return view('imports.confirm-duplicates', [
    //         'duplicates' => $duplicates,
    //         'type' => 'export'
    //     ]);
    // }

    // Duplicate Handling
    private function findImportDuplicates($data)
    {
        return collect($data)->filter(function ($row) {
            return SalesImport::where([
                'contract_id' => $row[0],
                'btb_lc_no' => $row[1],
                'date' => $row[2],
                'fabric_value' => $row[4]
            ])->exists();
        })->values();
    }

    // private function findExportDuplicates($data)
    // {
    //     return collect($data)->filter(function ($row) {
    //         return SalesExport::where([
    //             'contract_id' => $row[0],
    //             'invoice_no' => $row[1],
    //             'export_bill_no' => $row[2],
    //             'amount_usd' => $row[3]
    //         ])->exists();
    //     })->values();
    // }

    // public function confirmExport(Request $request)
    // {
    //     $data = Session::get('export_data');
    //     $filtered = $this->filterData($data, $request->keep_ids);

    //     SalesExport::insert($this->mapExportData($filtered));
    //     Session::forget('export_data');

    //     return redirect()->route('sales-exports.index')->with('success', 'Data imported successfully');
    // }

    // Helper Methods
    private function filterData($data, $keepIds)
    {
        return collect($data)->filter(function ($row, $index) use ($keepIds) {
            return !in_array($index, $keepIds ?? []);
        })->values();
    }

    // private function mapImportData($data)
    // {
    //     return collect($data)->map(function ($row) {
    //         return [
    //             'contract_id' => $row[0],
    //             'btb_lc_no' => $row[1],
    //             'date' => $row[2],
    //             'description' => $row[3],
    //             'fabric_value' => $row[4],
    //             'accessories_value' => $row[5],
    //             'fabric_qty_kg' => $row[6],
    //             'accessories_qty' => $row[7],
    //             'print_emb_qty' => $row[8],
    //             'print_emb_value' => $row[9],
    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ];
    //     })->toArray();
    // }

    // private function mapExportData($data)
    // {
    //     return collect($data)->map(function ($row) {
    //         return [
    //             'contract_id' => $row[0],
    //             'invoice_no' => $row[1],
    //             'export_bill_no' => $row[2],
    //             'amount_usd' => $row[3],
    //             'realized_value' => $row[4],
    //             'g_qty_pcs' => $row[5],
    //             'date_of_realized' => $row[6],
    //             'due_amount_usd' => $row[7],
    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ];
    //     })->toArray();
    // }

 

    public function confirmImport(Request $request)
    {
        $data = Session::get('import_data');
        $contractId = Session::get('contract_id');

        $filtered = collect($data)->reject(function ($row, $index) use ($request) {
            return in_array($index, $request->keep_ids ?? []);
        });

        SalesImport::insert($this->mapImportData($filtered, $contractId));

        Session::forget(['import_data', 'contract_id']);
        return redirect()->route('sales-contracts.show', $contractId);
    }

    // Update processExportUpload method
    public function processExportUpload(Request $request, $contractId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $exportData = Excel::toArray(new ImportExports, $request->file('file'))[0];

        $duplicates = $this->findExportDuplicates($exportData, $contractId);
        Session::put('export_data', $exportData);
        Session::put('contract_id', $contractId);

        return view('imports.confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'export'
        ]);
    }

    // Update findExportDuplicates method
    private function findExportDuplicates($data, $contractId)
    {
        return collect($data)->filter(function ($row) use ($contractId) {
            return SalesExport::where([
                'contract_id' => $contractId,
                'invoice_no' => $row['invoice_no'] ?? null,
                'export_bill_no' => $row['export_bill_no'] ?? null,
                'amount_usd' => $row['amount_usd_of_export_goods'] ?? 0,
            ])->exists();
        })->values();
    }

    // Update mapExportData method
    private function mapExportData($data, $contractId)
    {
        return collect($data)->map(function ($row) use ($contractId) {
            return [
                'contract_id' => $contractId,
                'invoice_no' => $row['invoice_no'] ?? null,
                'export_bill_no' => $row['export_bill_no'] ?? null,
                'amount_usd' => $row['amount_usd_of_export_goods'] ?? 0,
                'realized_value' => $row['amount_usd_realised'] ?? 0,
                'g_qty_pcs' => $row['g_qty_pcs'] ?? 0,
                'date_of_realized' => isset($row['date_of_realised']) ? Carbon::parse($row['date_of_realised']) : null,
                'due_amount_usd' => $row['due_amount_usd'] ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();
    }

    // Update confirmExport method
    public function confirmExport(Request $request)
    {
        $data = Session::get('export_data');
        $contractId = Session::get('contract_id');
        $filtered = $this->filterData($data, $request->keep_ids);

        SalesExport::insert($this->mapExportData($filtered, $contractId));
        Session::forget(['export_data', 'contract_id']);

        return redirect()->route('sales-contracts.show', $contractId)->with('success', 'Export data imported successfully.');
    }
}
