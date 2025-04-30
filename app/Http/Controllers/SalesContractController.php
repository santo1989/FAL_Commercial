<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\SalesContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesContractController extends Controller
{
    public function index()
    {
        return view('sales-contracts.index', [
            'contracts' => SalesContract::with(['imports', 'exports'])->paginate(10)
        ]);
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

        return redirect()->route('sales-contracts.index')->with('success', 'Contract updated successfully.');
    }

  
    public function destroy(SalesContract $SalesContract)
    {
        //check if the contract has any related imports or exports
        if ($SalesContract->imports()->exists() || $SalesContract->exports()->exists()) {
            return redirect()->back()->withErrors('Cannot delete contract with related imports or exports.');
        }
        $SalesContract->delete();
        return redirect()->route('sales-contracts.index')->with('success', 'Contract deleted successfully.');
    }

    public function storeUD(Request $request, SalesContract $contract)
    {
        $validated = $request->validate([
            'ud_no' => 'required|string',
            'ud_date' => 'required|date',
            'ud_value' => 'required|numeric',
            'ud_value_pcs' => 'required|integer',
            'used_value' => 'nullable|numeric'
        ]);

        $updateData = [
            'ud_no' => $validated['ud_no'],
            'ud_date' => $validated['ud_date'],
            'ud_value' => $validated['ud_value'],
            'ud_qty_pcs' => $validated['ud_value_pcs'],
            'used_value' => $validated['used_value'],
        ];

        // Check for related data and if UD is actually changing
        if (($contract->imports()->exists() || $contract->exports()->exists()) && $contract->isDirty($updateData)) {
            $currentUD = [
                'ud_no' => $contract->ud_no,
                'ud_date' => optional($contract->ud_date)->toDateString(),
                'ud_value' => $contract->ud_value,
                'ud_qty_pcs' => $contract->ud_qty_pcs,
                'used_value' => $contract->used_value,
                'changed_at' => now()->toDateTimeString(),
                'changed_by' => auth()->id(), // If using authentication
            ];

            $udHistory = $contract->ud_history ?? [];
            array_push($udHistory, $currentUD);
            $updateData['ud_history'] = $udHistory;
        }

        $contract->update($updateData);

        return redirect()->back()->with('success', 'UD details updated!');
    }
   

    public function storeRevised(Request $request, SalesContract $contract)
    {
        $validated = $request->validate([
            'ud_no' => 'required|string',
            'ud_date' => 'required|date',
            'ud_value' => 'required|numeric',
            'ud_qty_pcs' => 'required|integer',
        ]);

        $contract->update([
            'Revised_Contract_details' => [
                'ud_no' => $validated['ud_no'],
                'ud_date' => $validated['ud_date'],
                'ud_value' => $validated['ud_value'],
                'ud_qty_pcs' => $validated['ud_qty_pcs'],
            ]
        ]);

        return redirect()->back()->with('success', 'Revised details updated!');
    }
}
