<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Invoice-{{ $invoice->invoice_number }}_{{ date('dhm')}}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite('resources/css/app.css')
    </head>
    <body class="font-sans antialiased dark:bg-slate-950 dark:text-white/50">
        <div class="invoice">
            <div class="invoice-header">
                <h1>Customer Invoice</h1>
                <div class="invoice-buttons">
                    <a href="{{ session()->all()['_previous']['url']}}" class="invoice-button">Return back</a>
                    <button class="invoice-button" onclick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg></button>
                </div>
            </div>
            <div class="company-info">
                <div class="company-logo">
                    <img src="/storage/{{ $invoice->team->logo_url }}" alt="Company Logo" >
                </div>
                <p><strong>Company Name:</strong> {{ $invoice->team->name}}</p>
                <p><strong>Address:</strong> {{ $invoice->team->address }}</p>
                <p><strong>Phone:</strong> {{ $invoice->team->phone}}</p>
            </div>
            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> INV-{{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ date('d/m/Y', strtotime($invoice->created_at)) }}</p>
                <p><strong>Customer:</strong> {{ $invoice->customer->name }}</p>
            </div>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->product->title }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ number_format($item->price) }}</td>
                        <td>{{ number_format($item->price * $item->quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="invoice-total">
                <p><strong>Total:</strong> {{ number_format($items->reduce(fn ($total, $item) => $total + $item->price * $item->quantity,0 ))}}</p>
            </div>
            <div class="invoice-footer">
                <p>Thank you for shopping with us!</p>
                <p>Issued by: {{ $invoice->user->name }}</p>
            </div>
        </div>
    </body>
</html>
