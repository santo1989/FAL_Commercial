<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Division;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        $companies = Company::all();
        $divisions = Division::all();
        return view('backend.library.suppliers.index', compact('suppliers', 'companies', 'divisions'));
    }


    public function create()
    {
        $suppliers = Supplier::all();
        $companies = Company::all();
        $divisions = Division::all();
        return view('backend.library.suppliers.create', compact('suppliers', 'companies', 'divisions'));
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|array|min:1|max:191',
            'company_id' => 'required',
            'division_id' => 'required'
        ]);

        $suppliers = Supplier::all();
        $companies = Company::all();
        $divisions = Division::all(); 

        // Loop through the 'name' array and save each supplier name
        foreach ($request->name as $name) {
            foreach ($suppliers as $supplier) {
                if (
                    $supplier->name == strtoupper($name) &&
                    $supplier->company_id == $request->company_id &&
                    $supplier->division_id == $request->division_id
                ) {
                    return redirect()->route('suppliers.index')->withErrors('Supplier already exists!');
                }
            }
            // dd($request->all());
            // Data insert
            $supplier = new Supplier; 
            $supplier->name = strtoupper($name);
            $supplier->division_id = $request->division_id;
            $supplier->company_id = $request->company_id; 
            $supplier->company_name = Company::findorFail($request->company_id)->name;  
            $supplier->save();
        } 

        // Redirect
        return redirect()->route('suppliers.index')->with('message', 'Supplier created successfully!');
    }


    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        $companies = Company::all();
        $divisions = Division::all();
        return view('backend.library.suppliers.show', compact('supplier', 'companies', 'divisions'));
    }


    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $companies = Company::all();
        $divisions = Division::all();
        return view('backend.library.suppliers.edit', compact('supplier', 'companies', 'divisions'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:1|max:191',
        ]);

        // Data update
        $supplier = Supplier::findOrFail($id);
        $suppliers = Supplier::all();
        $companies = Company::all();
        $divisions = Division::all();

        if ($supplier->name == strtoupper($request->name) && $supplier->company_id == $request->company_id && $supplier->division_id == $request->division_id) {
            return redirect()->route('suppliers.index')->withErrors('No changes made!');
        } else {
            foreach ($suppliers as $supplier) {
                if ($supplier->name == strtoupper($request->name) && $supplier->company_id == $request->company_id && $supplier->division_id == $request->division_id) {
                    return redirect()->route('suppliers.index')->withErrors('Supplier already exists!');
                }
            }
        }
        $supplier->name = strtoupper($request->name);
        $supplier->company_id = $request->company_id;
        $supplier->company_name = Company::findOrFail($request->company_id)->name;
        $supplier->division_id = $request->division_id;
        $supplier->division_name = Division::findOrFail($request->division_id)->name;
        $supplier->save();

        // Redirect
        return redirect()->route('suppliers.index')->with('message', 'Supplier updated successfully!');
    }


    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->route('suppliers.index')->with('message', 'Supplier deleted successfully!');
    }

    public function supplier_active($id)
    {
        $supplier = Supplier::findOrFail($id);
        if ($supplier->is_active == 0) {
            $supplier->is_active = 1;
            $supplier->save();
            return redirect()->route('suppliers.index')->with('message', 'Supplier deactivated successfully!');
        } else {
            $supplier->is_active = 0;
            $supplier->save();
            return redirect()->route('suppliers.index')->with('message', 'Supplier activated successfully!');
        }
    }

    public function get_supplier(Request $request)
    {
        $supplier = Supplier::where('company_id', $request->company_id)->get();
        return response()->json($supplier);
    } 
     
}
