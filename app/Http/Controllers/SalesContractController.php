<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\SalesContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesContractsExport;
use Barryvdh\DomPDF\Facades\Pdf;

class SalesContractController extends Controller
{
    // public function index()
    // {
    //     return view('sales-contracts.index', [
    //         'contracts' => SalesContract::with(['imports', 'exports'])->paginate(10)
    //     ]);
    // }

    public function index(Request $request)
    {
        // Start a new query on the SalesContract model
        $query = SalesContract::query();

        // Eager‐load your relations
        $query->with(['imports', 'exports']);

        // Apply buyer filter
        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }

        // Apply contract number filter
        if ($request->filled('contract_no')) {
            $query->where('sales_contract_no', $request->contract_no);
        }

        // Apply exact contract date filter from start date to end date
        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('contract_date', [
                $request->contract_date_from,
                $request->contract_date_to
            ]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('contract_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('contract_date', '<=', $request->contract_date_to);
        }

        // Apply generic search across multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%")
                    // add more orWhere() clauses here for other searchable columns
                ;
            });
        }

        // Paginate & append all query parameters so links keep filters
        $contracts = $query
            ->orderBy('contract_date', 'desc')    // optional: default sort
            ->paginate(10)
            ->appends($request->all());

        // Pass filtered & paginated result to the view
        return view('sales-contracts.index', compact('contracts'));
    }

    /**
     * Export filtered contracts to Excel
     */
    public function export(Request $request)
    {
        // duplicate filters from index so export contains same data
        $query = SalesContract::query();
        $query->with(['imports', 'exports']);

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }

        if ($request->filled('contract_no')) {
            $query->where('sales_contract_no', $request->contract_no);
        }

        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('contract_date', [
                $request->contract_date_from,
                $request->contract_date_to
            ]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('contract_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('contract_date', '<=', $request->contract_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $contracts = $query->orderBy('contract_date', 'desc')->get();

        return Excel::download(new SalesContractsExport($contracts), 'sales_contracts_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Export filtered contracts to PDF
     */
    public function exportPdf(Request $request)
    {
        if (!class_exists(Pdf::class)) {
            abort(500, 'PDF generation library not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        // reuse the same filtering logic as export()
        $query = SalesContract::query();
        $query->with(['imports', 'exports']);

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if ($request->filled('contract_no')) {
            $query->where('sales_contract_no', $request->contract_no);
        }
        if ($request->filled('contract_date_from') && $request->filled('contract_date_to')) {
            $query->whereBetween('contract_date', [$request->contract_date_from, $request->contract_date_to]);
        } elseif ($request->filled('contract_date_from')) {
            $query->whereDate('contract_date', '>=', $request->contract_date_from);
        } elseif ($request->filled('contract_date_to')) {
            $query->whereDate('contract_date', '<=', $request->contract_date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sales_contract_no', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%");
            });
        }

        $contracts = $query->orderBy('contract_date', 'desc')->get();

        $pdf = Pdf::loadView('sales-contracts.pdf', compact('contracts'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('sales_contracts_' . now()->format('Ymd_His') . '.pdf');
    }

    public function create()
    {
        return view('sales-contracts.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'buyer_id' => 'required',
                'sales_contract_no' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $isRevision = $request->input('revised_contract_details') === 'yes';

                        if ($isRevision && !SalesContract::where('sales_contract_no', $value)->exists()) {
                            $fail('Contract number does not exist for revision.');
                        }

                        if (!$isRevision && SalesContract::where('sales_contract_no', $value)->exists()) {
                            $fail('Contract number already exists.');
                        }
                    },
                ],
                'contract_date' => 'required|date',
                'sales_contract_value' => 'required',
                'quantity_pcs' => 'required|integer',
                'revised_contract_details' => 'required|in:yes,no',
            ]);

            // dd($validatedData, $request->all());

            $buyerName = Buyer::where('id', $validatedData['buyer_id'])
                ->value('name');

            if ($validatedData['revised_contract_details'] === 'yes') {
                $contract = SalesContract::where('sales_contract_no', $validatedData['sales_contract_no'])
                    ->firstOrFail();

                // Backup existing data
                $contract->update([
                    'Revised_Contract_details' => [
                        'buyer_id' => $contract->buyer_id,
                        'sales_contract_value' => $contract->sales_contract_value,
                        'quantity_pcs' => $contract->quantity_pcs,
                        'contract_date' => $contract->contract_date,

                    ]
                ]);

                // Update with new data
                $contract->update([
                    'buyer_id' => $validatedData['buyer_id'],
                    'buyer_name' => $buyerName,
                    'contract_date' => $validatedData['contract_date'],
                    'sales_contract_value' => $validatedData['sales_contract_value'],
                    'quantity_pcs' => $validatedData['quantity_pcs'],

                ]);
            } else {
                $sales_data = SalesContract::create([
                    'buyer_id' => $validatedData['buyer_id'],
                    'buyer_name' => $buyerName,
                    'sales_contract_no' => $validatedData['sales_contract_no'],
                    'contract_date' => $validatedData['contract_date'],
                    'sales_contract_value' => $validatedData['sales_contract_value'],
                    'quantity_pcs' => $validatedData['quantity_pcs'],

                ]);
            }

            // dd($sales_data, $validatedData, $request->all());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Operation failed: ' . $e->getMessage()]);
        }

        return redirect()->route('sales-contracts.index');
    }


    public function show(SalesContract $SalesContract)
    {
        return view('sales-contracts.show', [
            'contract' => $SalesContract->load(['imports', 'exports'])
        ]);
    }


    public function edit(SalesContract $SalesContract)
    {
        return view('sales-contracts.edit', [
            'contract' => $SalesContract,
            'buyers' => Buyer::all()
        ]);
    }


    public function update(Request $request, SalesContract $SalesContract)
    {
        $validatedData = $request->validate([
            'buyer_id' => 'required',
            'sales_contract_no' => 'required|unique:sales_contracts,sales_contract_no,' . $SalesContract->id,
            'contract_date' => 'required|date',
            'sales_contract_value' => 'required|numeric',
            'quantity_pcs' => 'required|integer',
        ]);

        $buyerName = Buyer::where('id', $validatedData['buyer_id'])
            ->value('name');

        $SalesContract->update([
            'buyer_id' => $validatedData['buyer_id'],
            'buyer_name' => $buyerName,
            'sales_contract_no' => $validatedData['sales_contract_no'],
            'contract_date' => $validatedData['contract_date'],
            'sales_contract_value' => $validatedData['sales_contract_value'],
            'quantity_pcs' => $validatedData['quantity_pcs'],
        ]);

        return redirect()->route('sales-contracts.index')->withMessage('Contract updated successfully.');
    }


    public function destroy(SalesContract $SalesContract)
    {
        //check if the contract has any related imports or exports
        if ($SalesContract->imports()->exists() || $SalesContract->exports()->exists()) {
            return redirect()->back()->withErrors('Cannot delete contract with related imports or exports.');
        }
        $SalesContract->delete();
        return redirect()->route('sales-contracts.index')->withMessage('Contract deleted successfully.');
    }

    public function storeUD(Request $request, SalesContract $contract)
    {
        $validated = $request->validate([
            'ud_no'         => 'required|string',
            'ud_date'       => 'required|date',
            'ud_value'      => 'required|numeric',
            'ud_value_pcs'  => 'required|integer',
            'used_value'    => 'nullable|numeric',
            'bank_name'     => 'nullable|string',
        ]);

        // 1) Build up the history array (existing + snapshot if any)
        $history = $contract->ud_history ?? [];

        // Only snapshot if there was a previous UD
        if ($contract->ud_no !== null) {
            $history[] = [
                'ud_no'       => $contract->ud_no,
                'ud_date'     => $contract->ud_date->toDateString(),
                'ud_value'    => $contract->ud_value,
                'ud_qty_pcs'  => $contract->ud_qty_pcs,
                'used_value'  => $contract->data_1,
                'bank_name'   => $contract->bank_name,
                'changed_at'  => now()->toDateTimeString(),
                'changed_by'  => auth()->id(),
            ];
        }

        // 2) Overwrite the “live” UD fields + history
        $contract->ud_history    = $history;
        $contract->ud_no         = $validated['ud_no'];
        $contract->ud_date       = $validated['ud_date'];
        $contract->ud_value      = $validated['ud_value'];
        $contract->ud_qty_pcs    = $validated['ud_value_pcs'];
        $contract->used_value        = $validated['used_value'] ?? 0;
        $contract->bank_name     = $validated['bank_name'] ?? null;

        // 3) Save everything in one go
        $contract->save();

        return back()->withMessage('UD details updated!');
    }



    public function storeRevised(Request $request, SalesContract $contract)
    {
        $validated = $request->validate([
            'Revised_no' => 'required|string',
            'Revised_date' => 'required|date',
            'Revised_value' => 'required|numeric',
            'Revised_qty_pcs' => 'required|integer',
        ]);

        $history = $contract->revised_history ?? [];

        if ($contract->Revised_no !== null) {
            $history[] = [
                'Revised_no'       => $contract->Revised_no,
                'Revised_date'     => $contract->Revised_date, // Fixed: use string directly
                'Revised_value'    => $contract->Revised_value,
                'Revised_qty_pcs'  => $contract->Revised_qty_pcs,
            ];
        }

        // Assign to the correct attribute
        $contract->revised_history = $history; // Corrected attribute name
        $contract->Revised_no      = $validated['Revised_no'];
        $contract->Revised_date    = $validated['Revised_date'];
        $contract->Revised_value   = $validated['Revised_value'];
        $contract->Revised_qty_pcs = $validated['Revised_qty_pcs'];

        $contract->save();

        return redirect()->back()->withMessage('Revised details updated!');
    }

    public function closed(Request $request, SalesContract $contract)
    {
        $request->validate([
            'status' => 'required|in:live,closed'
        ]);

        $contract->update([
            'data_4' => $request->status
        ]);

        return back()->withMessage('Status updated!');
    }
}
