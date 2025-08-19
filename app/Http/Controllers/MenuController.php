<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;
use App\Models\Order;
use App\Models\Order_item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    public function index()
    {
        // Fetch all items from the database with category relationship
        $items = item::with('category')->where('is_active', true)->get();
        
        // Pass the items to the menu view
        return view('customer.menu', ['items' => $items]);
    }

}