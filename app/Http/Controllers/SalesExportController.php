<?php

namespace App\Http\Controllers;

use App\Models\SalesExport;
use Illuminate\Http\Request;
use App\Models\SalesImport; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
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

    

   // Excel upload Handling
    public function downloadExportTemplate()
    {
        return response()->download(public_path('templates/export_template.xlsx'));
    }

    
   

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

    public function showExportConfirmation()
    {
        // Retrieve session data
        $duplicates = Session::get('duplicate_rows', []);
        $contractId = Session::get('contract_id');

        // Validate session data
        if (!$contractId || !Session::has('export_data')) {
            return redirect()->back()->with('error', 'Session expired. Please upload the file again.');
        }

        return view('imports.confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'export'
        ]);
    }



    public function processExportUpload(Request $request, $contractId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $exportData = Excel::toArray(new ImportExports, $request->file('file'))[0];

        // Normalize keys before processing
        $exportData = $this->remapExportData($exportData);

        $duplicateIndices = [];
        $duplicateRows = [];

        foreach ($exportData as $index => $row) {
            if ($this->isExportDuplicate($row, $contractId)) {
                $duplicateIndices[] = $index;
                $duplicateRows[] = $row;
            }
        }

        // Store data in session for confirmation page
        Session::put('export_data', $exportData);
        Session::put('duplicate_indices', $duplicateIndices);
        Session::put('duplicate_rows', $duplicateRows);
        Session::put('contract_id', $contractId);

        // Redirect to confirmation page
        return redirect()->route('export.confirmation');
    }


    private function remapExportData($data)
    {
        $remapped = [];
        foreach ($data as $row) {
            $remapped[] = $this->normalizeRowKeys($row);
        }
        return $remapped;
    }

    private function normalizeRowKeys($row)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(preg_replace('/[^a-z0-9]/i', '_', $key));
            $normalizedKey = preg_replace('/_+/', '_', $normalizedKey);
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }

    private function parseDecimal($value)
    {
        if (is_numeric($value)) {
            return round(floatval($value), 2);
        }

        // Handle currency strings
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        if (is_numeric($cleaned)) {
            return round(floatval($cleaned), 2);
        }

        return 0.00;
    }

    public function confirmExport(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = Session::get('export_data');
            $duplicateIndices = Session::get('duplicate_indices', []);
            $contractId = Session::get('contract_id');
            $keepIds = $request->keep_ids ?? [];

            // Filter out duplicates that weren't selected
            $filtered = [];
            foreach ($data as $index => $row) {
                if (in_array($index, $duplicateIndices) && !in_array($index, $keepIds)) {
                    continue;
                }
                $filtered[] = $row;
            }

            // Import filtered data
            SalesExport::insert($this->mapExportData($filtered, $contractId));

            // Clear session
            Session::forget(['export_data', 'duplicate_indices', 'duplicate_rows', 'contract_id']);

            DB::commit();

            return redirect()->route('sales-contracts.show', $contractId)
                ->with('success', 'Export data imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function isExportDuplicate($row, $contractId)
    {
        return SalesExport::where([
            'shipment_date' => $this->parseExportDate($row['shipment_date'] ?? $row['shipment_date'] ?? null),
            'contract_id' => $contractId,
            'invoice_no' => $row['invoice_no'] ?? null,
            'export_bill_no' => $row['export_bill_no'] ?? null,
            'amount_usd' => $this->parseDecimal($row['amount'] ?? 0),
        ])->exists();
    }

    private function mapExportData($data, $contractId)
    {
        return collect($data)->map(function ($row) use ($contractId) {
            return [
                'shipment_date' => $this->parseExportDate($row['shipment_date'] ?? $row['shipment'] ?? null),
                'contract_id' => $contractId,
                'invoice_no' => $row['invoice_no'] ?? null,
                'export_bill_no' => $row['export_bill_no'] ?? null,
                'amount_usd' => $this->parseDecimal($row['amount'] ?? 0),
                'realized_value' => $this->parseDecimal($row['realized_value'] ?? 0),
                'g_qty_pcs' => intval($row['quantity'] ?? 0),
                'date_of_realized' => $this->parseExportDate($row['date_of_realised'] ?? $row['realized_date'] ?? null),
                'due_amount_usd' => $this->parseDecimal($row['due_amount'] ?? 0),
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();
    }

    private function parseExportDate($value)
    {
        if (empty($value)) return null;

        try {
            // Handle Excel numeric dates
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays(intval($value) - 2);
            }
            // Handle string formats
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
