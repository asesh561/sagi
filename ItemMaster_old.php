<?php

namespace App\Http\Controllers\masters;

use Illuminate\Support\Facades\DB;  // Ensure this is included
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ims_itemcodes;
use App\Models\ims_itemtypes;
use App\Models\ims_itemunits;
use App\Models\ims_taxcodes;
use App\Models\onboard_connections;


class ItemMaster extends Controller
{
  public function index()
  {
    $ims_itemcodes = ims_itemcodes::all();

    return view('content.masters.ItemMaster', compact('ims_itemcodes'));
  }

  public function add()
  {
    $categoryGroups = ims_itemtypes::select('catgroup', 'type')
    ->orderBy('catgroup', 'asc')->get();
    $sunits = ims_itemunits::select('sunits')
    ->distinct()->orderBy('sunits', 'asc')->get();
    $description = ims_taxcodes::select('description')
    ->distinct()->orderBy('description', 'asc')->get();
    $sunits1 = ims_itemunits::where('sunits', 'bags')
      ->select('sunits')
      ->distinct()->orderBy('sunits', 'asc')->get();
    return view('content.masters.ItemMaster-add-edit', compact('categoryGroups', 'sunits', 'sunits1', 'description'));
  }

  public function edit($id)
  {
    $categoryGroups = ims_itemtypes::select('catgroup', 'type')
      ->get();

    $types = DB::table('ims_itemtypes as a')
      ->join('ims_itemcodes as b', 'a.catgroup', '=', 'b.catgroup')
      ->where('b.id', '=', $id)
      ->select('a.type')
      ->get();


    $sunits = ims_itemunits::select('sunits')
      ->get()
      ->unique('sunits');

    $description = ims_taxcodes::select('description')
      ->get()
      ->unique('description');


    $taxApplicable = ims_itemcodes::where('id', $id)
      ->select('tax_applicable')
      ->get();


    $sunits1 = ims_itemunits::where('sunits', 'bags')
      ->select('sunits')
      ->get()
      ->unique('sunits');

    $ims_itemcodes = ims_itemcodes::findOrFail($id);
    return view(
      'content.masters.ItemMaster-add-edit',
      compact('ims_itemcodes', 'categoryGroups', 'sunits', 'sunits1', 'description', 'types', 'taxApplicable')
    );
  }

  public function store(Request $request)
  {
    // Define validation rules
    $validatedData = $request->validate([
      'code' => 'required|string|max:255',
      'description' => 'required|string|max:255',
      'cat' => 'required|string|max:255',
      'catgroup' => 'required|string|max:255',
      'type' => 'required|string|max:255',
      'cm' => 'required|string|max:255',
      'sunits' => 'required|string|max:255',
      'cunits' => 'required|string|max:255',
      'saunits' => 'required|string|max:255',
      'source' => 'required|string|max:255',
      'iusage' => 'required|string|max:255',
      'tax_applicable' => 'required|array',
    ]);

    // Create new item instance
    $nn = new ims_itemcodes();
    $nn->code = $validatedData['code'];
    $nn->description = $validatedData['description'];
    $nn->cat = $validatedData['cat'];
    $nn->catgroup = $validatedData['catgroup'];
    $nn->type = $validatedData['type'];
    $nn->cm = $validatedData['cm'];
    $nn->sunits = $validatedData['sunits'];
    $nn->cunits = $validatedData['cunits'];
    $nn->sales_units = $validatedData['saunits'];
    $nn->source = $validatedData['source'];
    $nn->iusage = $validatedData['iusage'];
    $nn->tax_applicable = implode(',', $validatedData['tax_applicable']);

    // Save the item
    $nn->save();
    onboard_connections::query()->update(['item_flag' => 0]);
    // Return response
    return redirect()
      ->route('masters-ItemMaster')
      ->with('success', 'Item saved successfully!');
  }

  public function update(Request $request, $id)
  {
    // Define validation rules
    $validatedData = $request->validate([
      'code' => 'required|string|max:255',
      'description' => 'required|string|max:255',
      'cat' => 'required|string|max:255',
      'catgroup' => 'required|string|max:255',
      'type' => 'required|string|max:255',
      'cm' => 'required|string|max:255',
      'sunits' => 'required|string|max:255',
      'cunits' => 'required|string|max:255',
      'saunits' => 'required|string|max:255',
      'source' => 'required|string|max:255',
      'iusage' => 'required|string|max:255',
      'tax_applicable' => 'required|array',
    ]);

    // Find the existing item
    $ims_itemcodes = ims_itemcodes::findOrFail($id);

    // Update the item's properties
    $ims_itemcodes->code = $validatedData['code'];
    $ims_itemcodes->description = $validatedData['description'];
    $ims_itemcodes->cat = $validatedData['cat'];
    $ims_itemcodes->catgroup = $validatedData['catgroup'];
    $ims_itemcodes->type = $validatedData['type'];
    $ims_itemcodes->cm = $validatedData['cm'];
    $ims_itemcodes->sunits = $validatedData['sunits'];
    $ims_itemcodes->cunits = $validatedData['cunits'];
    $ims_itemcodes->source = $validatedData['source'];
    $ims_itemcodes->sales_units = $validatedData['saunits'];
    $ims_itemcodes->iusage = $validatedData['iusage'];
    $ims_itemcodes->tax_applicable = implode(',', $validatedData['tax_applicable']);

    // Save the updated item
    $ims_itemcodes->save();
    onboard_connections::query()->update(['item_flag' => 0]);   
     // Return response
    return redirect()
      ->route('masters-ItemMaster')
      ->with('success', 'Item updated successfully!');
  }

  public function destroy($id)
  {
    $ims_itemcodes = ims_itemcodes::findOrFail($id);
    $ims_itemcodes->delete();

    return redirect()
      ->route('masters-ItemMaster')
      ->with('success', 'Item deleted successfully!');
  }
}
