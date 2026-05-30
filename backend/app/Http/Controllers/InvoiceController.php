<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = $request->user()->invoices()->with('client:id,name')->latest()->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,unpaid,paid,overdue',
            'notes' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $subtotal += ($item['quantity'] * $item['unit_price']);
                }

                $tax = $validated['tax'] ?? 0;
                $total = $subtotal + $tax;

                $invoice = $request->user()->invoices()->create([
                    'client_id' => $validated['client_id'],
                    'invoice_number' => $validated['invoice_number'],
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);

                $invoiceItems = [];
                foreach ($validated['items'] as $item) {
                    $invoiceItems[] = [
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                    ];
                }

                $invoice->items()->createMany($invoiceItems);

                return $invoice->load('items', 'client');
            });

            return response()->json([
                'message' => 'Invoice berhasil dibuat',
                'invoice' => $invoice
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $invoice = $request->user()->invoices()->with(['client', 'items'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = $request->user()->invoices()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,unpaid,paid,overdue',
        ]);

        $invoice->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Status invoice berhasil diperbarui',
            'invoice' => $invoice
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $invoice = $request->user()->invoices()->findOrFail($id);

        $invoice->delete();

        return response()->json(['message' => 'Invoice berhasil dihapus']);
    }

    public function statistics(Request $request)
    {
        $invoices = $request->user()->invoices();

        $totalInvoices = (clone $invoices)->count();

        $totalPaid = (clone $invoices)->where('status', 'paid')->sum('total');

        $totalUnpaid = (clone $invoices)->whereIn('status', ['draft', 'unpaid', 'overdue'])->sum('total');

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
        ]);
    }
}
