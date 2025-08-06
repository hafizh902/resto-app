<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;
use Illuminate\Support\Facades\Session;

class MenuController extends Controller
{
    public function index()
    {
        // Fetch all items from the database with category relationship
        $items = item::with('category')->where('is_active', true)->get();
        
        // Pass the items to the menu view
        return view('customer.menu', ['items' => $items]);
    }
    public function cart()
    {
        $cart = Session::get('cart', []);
        return view('customer.cart',compact('cart'));
}
   public function addToCart(Request $request)
   {
         $menuId = $request->input('mid');
         $menu = item::find($menuId);

         if (!$menu) {
             return response()->json(['succes' => false, 'message' => 'Menu not found'], 404);
   }
   $cart = Session::get('cart', []);

   if (isset($cart[$menuId])) {
       $cart[$menuId]['quantity']++;
   } else {
       $cart[$menuId] = [
           'id' => $menu->id,
           'name' => $menu->name,
           'price' => $menu->price,
              'quantity' => 1,
       ];  }
    Session::put('cart', $cart);
    return response()->json(['success' => true, 'message' => 'Menu added to cart']);
}
}