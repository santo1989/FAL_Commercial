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
use App\Exports\SalesExportsExport;
use App\Exports\SalesExportReportExport;
use Barryvdh\DomPDF\Facades\Pdf;
use App\Models\BtbLc;
use Illuminate\Support\Facades\Log;

class SalesExportController extends Controller
{

    public function index(Request $request)
    {
        $query = SalesExport::with('salesContract');

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

        // Filter by shipment date range
        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('shipment_date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('shipment_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('shipment_date', '<=', $request->contract_date_to);
        }

        // Generic search across contract no and buyer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesContract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $exports = $query->orderBy('shipment_date', 'desc')->paginate(10)->appends($request->all());

        return view('sales-exports.index', compact('exports'));
    }


    public function create()
    {
        $contracts = \App\Models\SalesContract::all();
        return view('sales-exports.create', compact('contracts'));
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

        return redirect()->route('sales-exports.index')->withMessage('Sales Export created successfully.');
    }


    public function show(SalesExport $salesExport)
    {
        // Ensure the export exists
        $export = SalesExport::findOrFail($salesExport->id);
        $contracts = \App\Models\SalesContract::all();
        return view('sales-exports.show', compact('export', 'contracts'));
    }


    public function edit(SalesExport $salesExport)
    {
        // Ensure the export exists
        $export = SalesExport::findOrFail($salesExport->id);
        $contracts = \App\Models\SalesContract::all();
        return view('sales-exports.edit', compact('export', 'contracts'));
    }


    public function update(Request $request, SalesExport $salesExport)
    {
        // 
        $validatedData = $request->validate([
            'contract_id' => 'required|exists:sales_contracts,id',
            'invoice_no' => 'nullable|string|max:255',
            'export_bill_no' => 'nullable|string|max:255',
            'amount_usd' => 'required|numeric',
            'realized_value' => 'required|numeric',
            'g_qty_pcs' => 'required|integer',
            'date_of_realized' => 'nullable|date',
            'due_amount_usd' => 'nullable|numeric',
            'shipment_date' => 'nullable|date'

        ]);
        // Ensure the export exists
        $salesExport = SalesExport::findOrFail($salesExport->id);
        // Update the export with validated data
        $validatedData['shipment_date'] = $validatedData['shipment_date'] ?? null;
        $validatedData['due_amount_usd'] = $validatedData['due_amount_usd'] ?? 0;


        $salesExport->update($validatedData);

        return redirect()->route('sales-exports.index')->withMessage('Sales Export updated successfully.');
    }


    public function destroy(SalesExport $salesExport)
    {
        //    dd($salesExport);
        // Ensure the export exists
        $salesExport = SalesExport::findOrFail($salesExport->id);
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

    /**
     * Export filtered exports to Excel
     */
    public function export(Request $request)
    {
        $query = SalesExport::with('salesContract');

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
            $query->whereBetween('shipment_date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('shipment_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('shipment_date', '<=', $request->contract_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesContract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $exports = $query->orderBy('shipment_date', 'desc')->get();

        return Excel::download(new SalesExportsExport($exports), 'sales_exports_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Export filtered exports to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            if (!class_exists(Pdf::class)) {
                abort(500, 'PDF generation library not installed. Run: composer require barryvdh/laravel-dompdf');
            }

            $query = SalesExport::with('salesContract');

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
                $query->whereBetween('shipment_date', [$request->contract_date_from, $request->contract_date_to]);
            } elseif ($request->filled('contract_date_from')) {
                $query->whereDate('shipment_date', '>=', $request->contract_date_from);
            } elseif ($request->filled('contract_date_to')) {
                $query->whereDate('shipment_date', '<=', $request->contract_date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('salesContract', function ($q) use ($search) {
                    $q->where('sales_contract_no', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%");
                });
            }

            $exports = $query->orderBy('shipment_date', 'desc')->get();

            $pdf = Pdf::loadView('sales-exports.pdf', compact('exports'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('sales_exports_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF export (sales exports) failed: ' . $e->getMessage(), ['exception' => $e]);
            abort(500, 'PDF generation failed. Check application logs for details.');
        }
    }

    /**
     * Export detailed Sales Export Report (Excel)
     */
    public function exportReport(Request $request)
    {
        $query = SalesExport::with('salesContract');

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
            $query->whereBetween('shipment_date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('shipment_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('shipment_date', '<=', $request->contract_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('salesContract', function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('shipment_date', 'desc')->get();

        return Excel::download(new SalesExportReportExport($rows), 'sales_export_report_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportReportPdf(Request $request)
    {
        try {
            if (!class_exists(Pdf::class)) {
                abort(500, 'PDF generation library not installed. Run: composer require barryvdh/laravel-dompdf');
            }

            $query = SalesExport::with('salesContract');

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
                $query->whereBetween('shipment_date', [$request->contract_date_from, $request->contract_date_to]);
            } elseif ($request->filled('contract_date_from')) {
                $query->whereDate('shipment_date', '>=', $request->contract_date_from);
            } elseif ($request->filled('contract_date_to')) {
                $query->whereDate('shipment_date', '<=', $request->contract_date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('salesContract', function ($q) use ($search) {
                    $q->where('sales_contract_no', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%");
                });
            }

            $rows = $query->orderBy('shipment_date', 'desc')->get();

            $pdf = Pdf::loadView('reports.sales_export_report_pdf', compact('rows'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('sales_export_report_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF export (sales export report) failed: ' . $e->getMessage(), ['exception' => $e]);
            abort(500, 'PDF generation failed. Check application logs for details.');
        }
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
                ->withMessage('Export data imported successfully.');
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
