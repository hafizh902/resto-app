<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;

class AdminMenuController extends Controller
{
    public function index()
    {
        $items = item::all();
        return view('admin.menu', compact('items'));
    }

    // Additional methods for create, edit, delete can be added here
}
