<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plugin;

class PluginController extends Controller
{
    public function index()
    {
        return Plugin::all();
    }

    public function update(Request $request, $id)
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->enabled = $request->input('enabled');
        $plugin->save();
        return response()->json($plugin);
    }
}
