<?php

namespace App\Http\Controllers;

use App\Models\SalesContract;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'sales_contract_no' => 'required|unique:sales_contracts',
            'sales_contract_value' => 'required|numeric',
            'quantity_pcs' => 'required|integer',
            'first_shipment_date' => 'required|date',
            'last_shipment_date' => 'required|date|after:first_shipment_date'
        ]);

        SalesContract::create($validated);
        return redirect()->route('sales-contracts.index');
    }

    public function show(SalesContract $salesContract)
    {
        return view('sales-contracts.show', [
            'contract' => $salesContract->load(['imports', 'exports'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesContract  $salesContract
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesContract $salesContract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesContract  $salesContract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesContract $salesContract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesContract  $salesContract
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesContract $salesContract)
    {
        //
    }
}
