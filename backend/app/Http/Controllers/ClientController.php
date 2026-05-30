<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = $request->user()->clients()->latest()->get();
        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:50',
            'pic_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('client-logos', 'public');
            $validated['logo'] = $path;
        }

        $client = $request->user()->clients()->create($validated);

        return response()->json([
            'message' => 'Klien berhasil ditambahkan',
            'client' => $client
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $client = $request->user()->clients()->findOrFail($id);
        $client->delete();

        return response()->json(['message' => 'Klien berhasil dihapus']);
    }

    public function show(Request $request, $id)
    {
        $client = $request->user()->clients()->findOrFail($id);
        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = $request->user()->clients()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:50',
            'pic_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('client-logos', 'public');
            $validated['logo'] = $path;
        }

        $client->update($validated);

        return response()->json([
            'message' => 'Klien berhasil diperbarui',
            'client' => $client
        ]);
    }
}
