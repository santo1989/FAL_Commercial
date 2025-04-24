<?php

namespace App\Http\Controllers;

use App\Models\SalesExport;
use Illuminate\Http\Request;
use App\Models\SalesImport; 
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SalesImport as ImportSales;
use App\Imports\SalesExport as ImportExports;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class SalesExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesExport  $salesExport
     * @return \Illuminate\Http\Response
     */
    public function show(SalesExport $salesExport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesExport  $salesExport
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesExport $salesExport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesExport  $salesExport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesExport $salesExport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesExport  $salesExport
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesExport $salesExport)
    {
        //
    }

    

    // Download Templates
    public function downloadImportTemplate()
    {
        // return Storage::download('templates/import_template.xlsx');
        return response()->download(storage_path('app/templates/import_template.xlsx')); 
    }

    public function downloadExportTemplate()
    {
        return Storage::download('templates/export_template.xlsx');
    }

    // Handle File Uploads
    public function processImportUpload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $importData = Excel::toArray(new ImportSales, $request->file('file'))[0];
        $headers = array_shift($importData);

        $duplicates = $this->findImportDuplicates($importData);
        Session::put('import_data', $importData);

        return view('imports.confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'import'
        ]);
    }

    public function processExportUpload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $exportData = Excel::toArray(new ImportExports, $request->file('file'))[0];
        $headers = array_shift($exportData);

        $duplicates = $this->findExportDuplicates($exportData);
        Session::put('export_data', $exportData);

        return view('imports.confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'export'
        ]);
    }

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

    private function findExportDuplicates($data)
    {
        return collect($data)->filter(function ($row) {
            return SalesExport::where([
                'contract_id' => $row[0],
                'invoice_no' => $row[1],
                'export_bill_no' => $row[2],
                'amount_usd' => $row[3]
            ])->exists();
        })->values();
    }

    // Confirm Duplicates
    public function confirmImport(Request $request)
    {
        $data = Session::get('import_data');
        $filtered = $this->filterData($data, $request->keep_ids);

        SalesImport::insert($this->mapImportData($filtered));
        Session::forget('import_data');

        return redirect()->route('sales-imports.index')->with('success', 'Data imported successfully');
    }

    public function confirmExport(Request $request)
    {
        $data = Session::get('export_data');
        $filtered = $this->filterData($data, $request->keep_ids);

        SalesExport::insert($this->mapExportData($filtered));
        Session::forget('export_data');

        return redirect()->route('sales-exports.index')->with('success', 'Data imported successfully');
    }

    // Helper Methods
    private function filterData($data, $keepIds)
    {
        return collect($data)->filter(function ($row, $index) use ($keepIds) {
            return !in_array($index, $keepIds ?? []);
        })->values();
    }

    private function mapImportData($data)
    {
        return collect($data)->map(function ($row) {
            return [
                'contract_id' => $row[0],
                'btb_lc_no' => $row[1],
                'date' => $row[2],
                'description' => $row[3],
                'fabric_value' => $row[4],
                'accessories_value' => $row[5],
                'fabric_qty_kg' => $row[6],
                'accessories_qty' => $row[7],
                'print_emb_qty' => $row[8],
                'print_emb_value' => $row[9],
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();
    }

    private function mapExportData($data)
    {
        return collect($data)->map(function ($row) {
            return [
                'contract_id' => $row[0],
                'invoice_no' => $row[1],
                'export_bill_no' => $row[2],
                'amount_usd' => $row[3],
                'realized_value' => $row[4],
                'g_qty_pcs' => $row[5],
                'date_of_realized' => $row[6],
                'due_amount_usd' => $row[7],
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();
    }
}
