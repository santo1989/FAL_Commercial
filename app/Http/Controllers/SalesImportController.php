<?php

namespace App\Http\Controllers;

use App\Models\SalesImport;
use Illuminate\Http\Request;

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

    
}
