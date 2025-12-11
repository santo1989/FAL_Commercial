<?php

namespace App\Http\Controllers;

use App\Models\BtbLc;
use App\Models\SalesContract;
use App\Models\SalesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BtbLcsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class BtbLcController extends Controller
{
    public function index(Request $request)
    {
        $query = BtbLc::with(['contract', 'import']);

        // Load contracts for filter dropdown
        $contracts = SalesContract::all();

        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        if ($request->filled('btb_lc_no')) {
            $query->where('btb_lc_no', 'like', "%{$request->btb_lc_no}%");
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->bank_name);
        }

        if ($request->filled('import_type')) {
            $query->where('import_type', $request->import_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $btb = $query->orderBy('date', 'desc')->paginate(15)->appends($request->all());

        return view('btb-lcs.index', compact('btb', 'contracts'));
    }

    public function create()
    {
        $contracts = SalesContract::all();
        $imports = SalesImport::all();
        return view('btb-lcs.create', compact('contracts', 'imports'));
    }

    /**
     * Return imports (that have btb_lc_no) for a given contract id as JSON.
     * Used by the create/edit form to populate BTB/LC numbers and auto-fill dates.
     */
    public function importsByContract($contractId)
    {
        $imports = SalesImport::where('contract_id', $contractId)
            ->whereNotNull('btb_lc_no')
            ->orderBy('date', 'desc')
            ->get(['id', 'btb_lc_no', 'date']);

        // Return unique btb_lc_no entries keeping the first import id/date for each
        $unique = [];
        foreach ($imports as $imp) {
            if (!isset($unique[$imp->btb_lc_no])) {
                $d = null;
                try{
                    $d = $imp->date ? Carbon::parse($imp->date)->format('Y-m-d') : null;
                }catch(\Exception $e){
                    $d = null;
                }
                $unique[$imp->btb_lc_no] = [
                    'import_id' => $imp->id,
                    'btb_lc_no' => $imp->btb_lc_no,
                    'date' => $d,
                ];
            }
        }

        return response()->json(array_values($unique));
    }

    /**
     * Download filtered BTB LC list as Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['contract_id', 'btb_lc_no', 'bank_name', 'import_type', 'date_from', 'date_to']);
        $fileName = 'btb-lcs-' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new BtbLcsExport($filters), $fileName);
    }

    /**
     * Download filtered BTB LC list as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = BtbLc::with(['contract', 'import']);

        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }
        if ($request->filled('btb_lc_no')) {
            $query->where('btb_lc_no', 'like', "%{$request->btb_lc_no}%");
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->bank_name);
        }

        if ($request->filled('import_type')) {
            $query->where('import_type', $request->import_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $rows = $query->orderBy('date', 'desc')->get();

        $pdf = Pdf::loadView('btb-lcs.pdf', ['rows' => $rows]);
        $fileName = 'btb-lcs-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id' => 'nullable|exists:sales_contracts,id',
            'import_id' => 'nullable|exists:sales_imports,id',
            'btb_lc_no' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:255',
            'aceptence_date' => 'nullable|date',
            'aceptence_value' => 'nullable|numeric',
            'aceptence_type' => 'nullable|string',
            'tenor_days' => 'nullable|integer',
            'tenor_date_of' => 'nullable|integer',
            'mature_date' => 'nullable|date',
            'date_of_payment_to_supplier_by_bank' => 'nullable|date',
            'repayment_date' => 'nullable|date',
            'repayment_value' => 'nullable|numeric',
            'closing_balance' => 'nullable|numeric',
            'proclument_type' => 'nullable|string',
            'import_type' => 'nullable|string',
        ]);

        // compute mature_date if not given
        if (empty($data['mature_date']) && !empty($data['aceptence_date']) && !empty($data['tenor_days'])) {
            $data['mature_date'] = Carbon::parse($data['aceptence_date'])->addDays(intval($data['tenor_days']));
        }

        // If import_id was not provided but we have contract_id + btb_lc_no,
        // try to auto-find the related SalesImport and attach it.
        if (empty($data['import_id']) && !empty($data['contract_id']) && !empty($data['btb_lc_no'])) {
            $imp = SalesImport::where('contract_id', $data['contract_id'])
                ->where('btb_lc_no', $data['btb_lc_no'])
                ->first();
            if ($imp) {
                $data['import_id'] = $imp->id;
            }
        }

        $btb = BtbLc::create($data);

        return Redirect::route('btb-lcs.index')->with('message', 'BTB LC record created');
    }

    public function show(BtbLc $btbLc)
    {
        return view('btb-lcs.show', ['item' => $btbLc->load(['contract', 'import'])]);
    }

    public function edit(BtbLc $btbLc)
    {
        $contracts = SalesContract::all();
        $imports = SalesImport::all();
        return view('btb-lcs.edit', compact('btbLc', 'contracts', 'imports'));
    }

    public function update(Request $request, BtbLc $btbLc)
    {
        $data = $request->validate([
            'contract_id' => 'nullable|exists:sales_contracts,id',
            'import_id' => 'nullable|exists:sales_imports,id',
            'btb_lc_no' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:255',
            'aceptence_date' => 'nullable|date',
            'aceptence_value' => 'nullable|numeric',
            'aceptence_type' => 'nullable|string',
            'tenor_days' => 'nullable|integer',
            'tenor_date_of' => 'nullable|integer',
            'mature_date' => 'nullable|date',
            'date_of_payment_to_supplier_by_bank' => 'nullable|date',
            'repayment_date' => 'nullable|date',
            'repayment_value' => 'nullable|numeric',
            'closing_balance' => 'nullable|numeric',
            'proclument_type' => 'nullable|string',
            'import_type' => 'nullable|string',
        ]);

        if (empty($data['mature_date']) && !empty($data['aceptence_date']) && !empty($data['tenor_days'])) {
            $data['mature_date'] = Carbon::parse($data['aceptence_date'])->addDays(intval($data['tenor_days']));
        }

        // If the update didn't include import_id but we have contract_id + btb_lc_no,
        // try to auto-find and attach the related SalesImport.
        if (empty($data['import_id']) && !empty($data['contract_id']) && !empty($data['btb_lc_no'])) {
            $imp = SalesImport::where('contract_id', $data['contract_id'])
                ->where('btb_lc_no', $data['btb_lc_no'])
                ->first();
            if ($imp) {
                $data['import_id'] = $imp->id;
            }
        }

        $btbLc->update($data);

        return Redirect::route('btb-lcs.index')->with('message', 'BTB LC record updated');
    }

    public function destroy(BtbLc $btbLc)
    {
        $btbLc->delete();
        return Redirect::route('btb-lcs.index')->with('message', 'BTB LC record deleted');
    }

    /**
     * Display BTB/LC Value Report
     */
    public function report(Request $request)
    {
        // Get unique banks for filter
        $banks = BtbLc::distinct()->pluck('bank_name')->filter()->sort()->values();

        // Set default date range (last 12 months)
        $dateFrom = $request->get('date_from', now()->subYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Build query
        $query = BtbLc::with(['contract', 'import'])
            ->whereNotNull('aceptence_value')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo);

        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->bank_name);
        }

        $records = $query->get();

        // Generate months between date range
        $months = $this->generateMonthRange($dateFrom, $dateTo);

        // Organize data by bank and category
        $reportData = $this->organizeReportData($records, $months);

        return view('btb-lcs.report', compact('reportData', 'months', 'banks'));
    }

    /**
     * Export BTB/LC Value Report to Excel
     */
    public function reportExcel(Request $request)
    {
        // Get unique banks for filter
        $banks = BtbLc::distinct()->pluck('bank_name')->filter()->sort()->values();

        // Set default date range
        $dateFrom = $request->get('date_from', now()->subYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Build query
        $query = BtbLc::with(['contract', 'import'])
            ->whereNotNull('aceptence_value')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo);

        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->bank_name);
        }

        $records = $query->get();

        // Generate months
        $months = $this->generateMonthRange($dateFrom, $dateTo);

        // Organize data
        $reportData = $this->organizeReportData($records, $months);

        return Excel::download(
            new \App\Exports\BtbLcReportExport($reportData, $months),
            'btblc_value_report_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Generate month range (e.g., Jul'25, Aug'25, ...)
     */
    private function generateMonthRange($dateFrom, $dateTo)
    {
        $months = [];
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        while ($start <= $end) {
            $months[] = $start->format("M'y"); // Jul'25
            $start->addMonth();
        }

        return $months;
    }

    /**
     * Organize data by bank group and category
     */
    private function organizeReportData($records, $months)
    {
        $reportData = [];

        // Define bank groups and their categories
        $bankGroups = [
            'BRAC Bank' => [
                'EDF-BRAC Bank',
                'ABP-BRAC',
                'UPAS-BRAC',
                'DPLC-BRAC Bank-Non-Accepted',
                'Sight LC-BRAC Bank-Non-Accepted',
                'Other-BRAC'
            ],
            'Prime Bank' => [
                'EDF-Prime Bank',
                'ABP-Prime',
                'UPAS-Prime',
                'DPLC-Prime Bank-Non-Accepted',
                'Sight LC-Prime Bank-Non-Accepted',
                'Other-Prime'
            ]
        ];

        foreach ($bankGroups as $bankGroup => $categories) {
            $reportData[$bankGroup] = [];

            foreach ($categories as $category) {
                $monthlyData = array_fill_keys($months, 0);

                foreach ($records as $record) {
                    // Match records to categories based on bank_name and aceptence_type
                    if ($this->matchesCategory($record, $category, $bankGroup)) {
                        $monthKey = Carbon::parse($record->date)->format("M'y");
                        if (isset($monthlyData[$monthKey])) {
                            $monthlyData[$monthKey] += floatval($record->aceptence_value ?? 0);
                        }
                    }
                }

                $reportData[$bankGroup][$category] = $monthlyData;
            }
        }

        return $reportData;
    }

    /**
     * Match record to category based on bank and type
     */
    private function matchesCategory($record, $category, $bankGroup)
    {
        $bankName = strtolower($record->bank_name ?? '');
        $aceptenceType = strtolower($record->aceptence_type ?? '');

        // Extract bank identifier
        $bankIdentifier = strtolower($bankGroup);

        if (!str_contains($bankName, $bankIdentifier)) {
            return false;
        }

        // Match category patterns
        if (str_contains($category, 'EDF')) {
            return str_contains($aceptenceType, 'edf');
        } elseif (str_contains($category, 'ABP')) {
            return str_contains($aceptenceType, 'abp');
        } elseif (str_contains($category, 'UPAS')) {
            return str_contains($aceptenceType, 'upas');
        } elseif (str_contains($category, 'DPLC')) {
            return str_contains($aceptenceType, 'dplc') || str_contains($aceptenceType, 'da');
        } elseif (str_contains($category, 'Sight LC')) {
            return str_contains($aceptenceType, 'sight') || str_contains($aceptenceType, 'lc');
        } elseif (str_contains($category, 'Other')) {
            // Other catches anything not matched above
            return !str_contains($aceptenceType, 'edf') &&
                   !str_contains($aceptenceType, 'abp') &&
                   !str_contains($aceptenceType, 'upas') &&
                   !str_contains($aceptenceType, 'dplc') &&
                   !str_contains($aceptenceType, 'da') &&
                   !str_contains($aceptenceType, 'sight') &&
                   !str_contains($aceptenceType, 'lc');
        }

        return false;
    }
}
