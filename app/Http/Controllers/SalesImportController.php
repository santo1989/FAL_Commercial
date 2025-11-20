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
use App\Exports\SalesImportsExport;
use App\Exports\SalesImportReportExport;
use App\Models\BtbLc;
use Barryvdh\DomPDF\Facades\Pdf;
use Illuminate\Support\Facades\Log;


class SalesImportController extends Controller
{

    public function index(Request $request)
    {
        $query = SalesImport::with('salesContract');

        // Filter by buyer via related sales contract
        if ($request->filled('buyer_id')) {
            $query->whereHas('salesContract', function ($q) use ($request) {
                $q->where('buyer_id', $request->buyer_id);
            });
        }

        // Filter by contract number
        if ($request->filled('contract_no')) {
            $query->whereHas('salesContract', function ($q) use ($request) {
                $q->where('sales_contract_no', $request->contract_no);
            });
        }

        // Filter by import date range
        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('date', '<=', $request->contract_date_to);
        }

        // Generic search across contract no and buyer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesContract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $imports = $query->orderBy('date', 'desc')->paginate(10)->appends($request->all());

        return view('sales-imports.index', compact('imports'));
    }

    public function create()
    {
        $contracts = \App\Models\SalesContract::all();
        return view('sales-imports.create', compact('contracts'));
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

        return redirect()->route('sales-imports.index')->withMessage('Sales Import created successfully.');
    }


    public function show(SalesImport $salesImport)
    {
        return view('sales-imports.show', compact('salesImport'));
    }


    public function edit(SalesImport $salesImport)
    {
        // Check if the sales import exists
        $import = SalesImport::find($salesImport->id);
        $contracts = \App\Models\SalesContract::all();
        return view('sales-imports.edit', compact('import', 'contracts'));
    }


    public function update(Request $request, SalesImport $salesImport)
    {
        // dd($salesImport);
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

        return redirect()->route('sales-imports.index')->withMessage('Sales Import updated successfully.');
    }


    public function destroy(SalesImport $salesImport)
    {
        // Check if the sales import exists
        $salesImport = SalesImport::find($salesImport->id);
        if (!$salesImport) {
            return redirect()->back()->withErrors('Sales Import not found.');
        }

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

    /**
     * Export filtered imports to Excel
     */
    public function export(Request $request)
    {
        $query = SalesImport::with('salesContract');

        if ($request->filled('buyer_id')) {
            $query->whereHas('salesContract', function ($q) use ($request) {
                $q->where('buyer_id', $request->buyer_id);
            });
        }

        if ($request->filled('contract_no')) {
            $query->whereHas('salesContract', function ($q) use ($request) {
                $q->where('sales_contract_no', $request->contract_no);
            });
        }

        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('date', '<=', $request->contract_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesContract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $imports = $query->orderBy('date', 'desc')->get();

        return Excel::download(new SalesImportsExport($imports), 'sales_imports_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Export filtered imports to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            if (!class_exists(Pdf::class)) {
                abort(500, 'PDF generation library not installed. Run: composer require barryvdh/laravel-dompdf');
            }

            $query = SalesImport::with('salesContract');

            if ($request->filled('buyer_id')) {
                $query->whereHas('salesContract', function ($q) use ($request) {
                    $q->where('buyer_id', $request->buyer_id);
                });
            }

            if ($request->filled('contract_no')) {
                $query->whereHas('salesContract', function ($q) use ($request) {
                    $q->where('sales_contract_no', $request->contract_no);
                });
            }

            if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
                $query->whereBetween('date', [$request->contract_date_from, $request->contract_date_to]);
            } elseif ($request->filled('contract_date_from')) {
                $query->whereDate('date', '>=', $request->contract_date_from);
            } elseif ($request->filled('contract_date_to')) {
                $query->whereDate('date', '<=', $request->contract_date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('salesContract', function ($q) use ($search) {
                    $q->where('sales_contract_no', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%");
                });
            }

            $imports = $query->orderBy('date', 'desc')->get();

            $pdf = Pdf::loadView('sales-imports.pdf', compact('imports'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('sales_imports_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF export (sales imports) failed: ' . $e->getMessage(), ['exception' => $e]);
            abort(500, 'PDF generation failed. Check application logs for details.');
        }
    }

    /**
     * Export detailed Sales Import Report (Excel)
     */
    public function exportReport(Request $request)
    {
        $query = BtbLc::with(['import', 'contract']);

        if ($request->filled('buyer_id')) {
            $query->whereHas('contract', function ($q) use ($request) {
                $q->where('buyer_id', $request->buyer_id);
            });
        }

        if ($request->filled('contract_no')) {
            $query->whereHas('contract', function ($q) use ($request) {
                $q->where('sales_contract_no', $request->contract_no);
            });
        }

        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('date', '<=', $request->contract_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('date', 'desc')->get();

        return Excel::download(new SalesImportReportExport($rows), 'sales_import_report_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Export detailed Sales Import Report to PDF
     */
    public function exportReportPdf(Request $request)
    {
        try {
            if (!class_exists(Pdf::class)) {
                abort(500, 'PDF generation library not installed. Run: composer require barryvdh/laravel-dompdf');
            }

            $query = BtbLc::with(['import', 'contract']);

            if ($request->filled('buyer_id')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('buyer_id', $request->buyer_id);
                });
            }

            if ($request->filled('contract_no')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('sales_contract_no', $request->contract_no);
                });
            }

            if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
                $query->whereBetween('date', [$request->contract_date_from, $request->contract_date_to]);
            } elseif ($request->filled('contract_date_from')) {
                $query->whereDate('date', '>=', $request->contract_date_from);
            } elseif ($request->filled('contract_date_to')) {
                $query->whereDate('date', '<=', $request->contract_date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('contract', function ($q) use ($search) {
                    $q->where('sales_contract_no', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%");
                });
            }

            $rows = $query->orderBy('date', 'desc')->get();

            $pdf = Pdf::loadView('reports.sales_import_report_pdf', compact('rows'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('sales_import_report_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF export (sales import report) failed: ' . $e->getMessage(), ['exception' => $e]);
            abort(500, 'PDF generation failed. Check application logs for details.');
        }
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
                ->withMessage('Import data processed successfully.');
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
