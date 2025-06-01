<?php

namespace App\Http\Controllers;

use App\Models\SalesImport;
use Carbon\Carbon;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\SalesImport as ImportSales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class SalesImportController extends Controller
{
    
    public function index()
    {
        $salesImports = SalesImport::with('contract')->paginate(10);
        return view('sales-imports.index', compact('salesImports'));
    }

    public function create()
    {
        return view('sales-imports.create');
        
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contract_id' => 'required|exists:sales_contracts,id',
            'btb_lc_no' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'description' => 'nullable|string|max:255',
            'fabric_value' => 'required|numeric',
            'accessories_value' => 'required|numeric',
            'fabric_qty_kg' => 'required|numeric',
            'accessories_qty' => 'required|numeric',
            'print_emb_qty' => 'nullable|string|max:255',
            'print_emb_value' => 'required|numeric',
        ]);

        SalesImport::create($validatedData);

        return redirect()->route('sales-imports.index')->with('success', 'Sales Import created successfully.');
    }

   
    public function show(SalesImport $salesImport)
    {
        return view('sales-imports.show', compact('salesImport'));
    }

 
    public function edit(SalesImport $salesImport)
    {
        return view('sales-imports.edit', compact('salesImport'));
    }

  
    public function update(Request $request, SalesImport $salesImport)
    {
        $validatedData = $request->validate([
            'contract_id' => 'required|exists:sales_contracts,id',
            'btb_lc_no' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'description' => 'nullable|string|max:255',
            'fabric_value' => 'required|numeric',
            'accessories_value' => 'required|numeric',
            'fabric_qty_kg' => 'required|numeric',
            'accessories_qty' => 'required|numeric',
            'print_emb_qty' => 'nullable|string|max:255',
            'print_emb_value' => 'required|numeric',
        ]);

        $salesImport->update($validatedData);

        return redirect()->route('sales-imports.index')->with('success', 'Sales Import updated successfully.');
    }


    public function destroy(SalesImport $salesImport)
    {

        $salesImport->delete();
        //return back with success message from where it came from
        return redirect()->back()->withMessage('Sales Import deleted successfully.');
    }

    // Download Templates
    public function downloadImportTemplate()
    {

        //find the file prom public\templates\import_template.xlsx and download it
        return response()->download(public_path('templates/import_template.xlsx'));
    }



    // Handle Excel File Uploads
    public function processImportUpload(Request $request, $contractId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $importData = Excel::toArray(new ImportSales, $request->file('file'))[0];

        // Normalize keys
        $importData = array_map([$this, 'normalizeRowKeys'], $importData);

        $duplicates = [];
        foreach ($importData as $index => $row) {
            if ($this->isImportDuplicate($row, $contractId)) {
                $duplicates[$index] = $row;
            }
        }

        Session::put('import_data', $importData);
        Session::put('contract_id', $contractId);

        return view('imports.import-confirm-duplicates', [
            'duplicates' => $duplicates,
            'type' => 'import'
        ]);
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

    private function isImportDuplicate($row, $contractId)
    {
        return SalesImport::where([
            'contract_id' => $contractId,
            'btb_lc_no' => $row['btb_lc_no'] ?? null,
            'date' => isset($row['date']) ? Carbon::parse($row['date']) : null,
            'fabric_value' => $row['fabric_value'] ?? 0,
        ])->exists();
    }

    public function confirmImport(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = Session::get('import_data');
            $contractId = Session::get('contract_id');
            $keepIds = $request->keep_ids ?? [];

            // Filter out duplicates that weren't selected
            $filtered = [];
            foreach ($data as $index => $row) {
                if (array_key_exists($index, $keepIds)) {
                    continue; // Skip selected duplicates
                }
                $filtered[] = $row;
            }

            // Import filtered data
            SalesImport::insert($this->mapImportData($filtered, $contractId));

            // Clear session
            Session::forget(['import_data', 'contract_id']);

            DB::commit();

            return redirect()->route('sales-contracts.show', $contractId)
                ->with('success', 'Import data processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function mapImportData($data, $contractId)
    {
        return array_map(function ($row) use ($contractId) {
            return [
                'contract_id' => $contractId,
                'btb_lc_no' => $row['btb_lc_no'] ?? null,
                'date' => $this->parseImportDate($row['date'] ?? null),
                'description' => $row['description'] ?? 'No description',
                'fabric_value' => $this->parseDecimal($row['fabric_value'] ?? 0),
                'accessories_value' => $this->parseDecimal($row['accessories_value'] ?? 0),
                'fabric_qty_kg' => $this->parseDecimal($row['fabric_qty_in_kgs'] ?? 0),
                'accessories_qty' => $this->parseDecimal($row['accessories_qty'] ?? 0),
                'print_emb_qty' => $this->parseDecimal($row['printing_embroidery_qty'] ?? 0),
                'print_emb_value' => $this->parseDecimal($row['printing_embroidery_value'] ?? 0),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $data);
    }

    private function parseDecimal($value)
    {
        if (is_numeric($value)) return floatval($value);
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? floatval($cleaned) : 0.0;
    }

    private function parseImportDate($value)
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
                return Carbon::createFromFormat('Y-m-d H:i:s', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
