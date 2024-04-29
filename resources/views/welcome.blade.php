<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite('resources/css/app.css')
    </head>
    <body class="font-sans antialiased dark:bg-slate-950 dark:text-white/50">
        <div class="invoice">
            <div class="invoice-header">
                <h1>Supermarket Invoice</h1>
                <div class="invoice-buttons">
                    <button class="invoice-button">Return to Seller</button>
                    <button class="invoice-button" onclick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg></button>
                </div>
            </div>
            <div class="company-info">
                <div class="company-logo">
                    <img src="company_logo.png" alt="Company Logo">
                </div>
                <p><strong>Company Name:</strong> XYZ Supermarket</p>
                <p><strong>Address:</strong> 123 Main Street, City, Country</p>
                <p><strong>Tax ID:</strong> TAX123456</p>
                <p><strong>Phone:</strong> +1 (123) 456-7890</p>
            </div>
            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> INV123456</p>
                <p><strong>Date:</strong> April 29, 2024</p>
                <p><strong>Customer:</strong> John Doe</p>
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
                    <tr>
                        <td>Bread</td>
                        <td>2</td>
                        <td>$2.50</td>
                        <td>$5.00</td>
                    </tr>
                    <tr>
                        <td>Milk</td>
                        <td>1</td>
                        <td>$1.50</td>
                        <td>$1.50</td>
                    </tr>
                    <tr>
                        <td>Cheese</td>
                        <td>1</td>
                        <td>$3.00</td>
                        <td>$3.00</td>
                    </tr>
                </tbody>
            </table>
            <div class="invoice-total">
                <p><strong>Total:</strong> $9.50</p>
            </div>
            <div class="invoice-footer">
                <p>Thank you for shopping with us!</p>
                <p>Printed by: John Doe</p>
            </div>
        </div>
    </body>
</html>
