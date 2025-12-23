<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->has('client_id')) {
            $invoices = $this->billingService->getInvoicesByClient($request->client_id);
            return response()->json($invoices);
        }

        $invoices = $this->billingService->getInvoicesForUser(auth()->user());
        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'file_id' => 'nullable|exists:files,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'total_tax_amount' => 'nullable|numeric|min:0',
            'auditor_fee' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->billingService->createInvoice($validated);
        return response()->json($invoice, 201);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->billingService->getInvoice($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        return response()->json($invoice);
    }

    public function downloadPdf(int $id)
    {
        $invoice = $this->billingService->getInvoice($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Get company settings
        $companySettings = Setting::whereIn('key', ['company_name', 'company_address', 'company_phone', 'company_email'])
            ->pluck('value', 'key');

        $data = [
            'invoice' => $invoice,
            'company' => $companySettings,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
