<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Order $order): View
    {
        $invoice = $order->with(['customer', 'team', 'user'])->first();
        $items = $order->orderItems()->with('product')->get();
        return view('invoices.show', ['invoice' => $invoice, 'items' => $items]);
    }
}
