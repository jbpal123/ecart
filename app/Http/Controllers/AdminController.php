<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;

class AdminController extends Controller
{
    // app/Http/Controllers/AdminController.php
public function dashboard()
{
    $totalProducts = Product::count();
    $totalOrders = Order::count();
    $pendingOrders = Order::where('status', 'pending')->count();
    
    return view('admin.dashboard', compact('totalProducts', 'totalOrders', 'pendingOrders'));
}
}
