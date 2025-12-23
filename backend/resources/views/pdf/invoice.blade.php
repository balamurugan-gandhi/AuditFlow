<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .invoice-info {
            flex: 1;
            text-align: right;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .client-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .invoice-details {
            margin: 20px 0;
        }
        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .amount-table th, .amount-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .amount-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>{{ $company['company_name'] ?? 'Company Name' }}</h1>
            @if($company['company_address'] ?? null)
                <p>{{ $company['company_address'] }}</p>
            @endif
            @if($company['company_phone'] ?? null)
                <p>Phone: {{ $company['company_phone'] }}</p>
            @endif
            @if($company['company_email'] ?? null)
                <p>Email: {{ $company['company_email'] }}</p>
            @endif
        </div>
        <div class="invoice-info">
            <div class="invoice-title">INVOICE</div>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="client-info">
        <h3>Bill To:</h3>
        <p><strong>{{ $invoice->client->name }}</strong></p>
        @if($invoice->client->address)
            <p>{{ $invoice->client->address }}</p>
        @endif
        @if($invoice->client->phone)
            <p>Phone: {{ $invoice->client->phone }}</p>
        @endif
        @if($invoice->client->email)
            <p>Email: {{ $invoice->client->email }}</p>
        @endif
    </div>

    @if($invoice->file)
    <div class="invoice-details">
        <h3>File Information:</h3>
        <p><strong>File:</strong> {{ $invoice->file->file_number }} - {{ $invoice->file->title }}</p>
    </div>
    @endif

    <table class="amount-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Tax Amount</td>
                <td>${{ number_format($invoice->total_tax_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Auditor Fee</td>
                <td>${{ number_format($invoice->auditor_fee ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Amount Due</td>
                <td>${{ number_format($invoice->auditor_fee ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->notes)
    <div class="notes">
        <h3>Notes:</h3>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif
</body>
</html>
