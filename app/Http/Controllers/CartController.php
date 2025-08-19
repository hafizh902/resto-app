<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja
     * Fungsi ini mengambil semua item yang ada di keranjang user yang sedang login
     * dan menampilkannya dalam view cart
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cari order dengan status 'pending' untuk user yang sedang login
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        // Siapkan collection kosong untuk item keranjang
        $cartItems = collect();
        
        // Jika order ditemukan, ambil semua item yang ada di order tersebut
        if ($order) {
            $cartItems = Order_item::where('order_id', $order->id)
                ->with('item')
                ->get();
        }

        // Kirim data ke view
        return view('customer.cart', compact('cartItems'));
    }

    /**
     * Menambahkan item ke keranjang belanja
     * Fungsi ini menambahkan item baru atau menambah jumlah item yang sudah ada di keranjang
     * 
     * @param int $itemId ID dari item yang akan ditambahkan
     * @param Request $request Request object berisi quantity
     * @return \Illuminate\Http\JsonResponse
     */
    public function add($itemId, Request $request)
    {
        // Validasi quantity yang dikirim
        $quantity = max(1, (int)$request->input('quantity', 1));

        // Pastikan item yang dimaksud ada di database
        $item = Item::findOrFail($itemId);

        // Validasi stok barang
        if ($item->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok barang tidak mencukupi. Stok tersedia: ' . $item->stock
            ], 400);
        }

        // Cari atau buat order dengan status 'pending' untuk user ini
        $order = Order::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'status'  => 'pending'
            ],
            [
                'order_code' => 'ORD-' . time() . '-' . Auth::id(),
                'subtotal' => 0,
                'tax' => 0,
                'grand_total' => 0,
                'table_number' => 1,
                'payment_method' => 'tunai',
            ]
        );

        // Cari apakah item sudah ada di keranjang
        $orderItem = Order_item::where('order_id', $order->id)
            ->where('item_id', $itemId)
            ->first();

        if ($orderItem) {
            // Jika sudah ada, tambahkan quantity
            $newQuantity = $orderItem->quantity + $quantity;
            
            // Validasi stok untuk quantity baru
            if ($item->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, melebihi stok yang tersedia. Stok tersedia: ' . $item->stock
                ], 400);
            }
            
            $orderItem->quantity = $newQuantity;
        } else {
            // Jika belum ada, buat item baru
            $orderItem = new Order_item();
            $orderItem->order_id = $order->id;
            $orderItem->item_id  = $itemId;
            $orderItem->quantity = $quantity;
            $orderItem->price    = $item->price;
            $orderItem->img      = $item->img;
        }

        // Hitung subtotal untuk item ini
        $subtotalItem = $orderItem->price * $orderItem->quantity;
        $orderItem->tax         = (int)($subtotalItem * 0.1); // Pajak 10%
        $orderItem->total_price = $subtotalItem + $orderItem->tax;

        // Simpan perubahan
        $orderItem->save();

        // Update total keseluruhan order
        $this->updateOrderTotals($order);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan ke keranjang!'
        ]);
    }

    /**
     * Memperbarui jumlah item di keranjang
     * Fungsi ini digunakan untuk mengubah jumlah item yang sudah ada di keranjang
     * 
     * @param int $id ID dari order_item yang akan diupdate
     * @param Request $request Request object berisi quantity baru
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        // Validasi input quantity
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000'
        ]);

        // Cari item di keranjang milik user yang sedang login
        $orderItem = Order_item::where('id', $id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                  ->where('status', 'pending');
            })
            ->firstOrFail();

        // Validasi stok barang
        $item = $orderItem->item;
        if ($item->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok tidak mencukupi. Stok tersedia: ' . $item->stock
            ], 400);
        }

        // Update quantity
        $orderItem->quantity = $request->quantity;
        
        // Hitung ulang harga
        $subtotalItem = $orderItem->price * $orderItem->quantity;
        $orderItem->tax = (int)($subtotalItem * 0.1);
        $orderItem->total_price = $subtotalItem + $orderItem->tax;
        $orderItem->save();

        // Update total order
        $order = $orderItem->order;
        $this->updateOrderTotals($order);

        return response()->json([
            'success'    => true,
            'item_total' => $subtotalItem,
            'subtotal'   => $order->subtotal,
            'tax'        => $order->tax,
            'total'      => $order->grand_total
        ]);
    }

    /**
     * Menghapus item dari keranjang
     * Fungsi ini menghapus item tertentu dari keranjang belanja
     * 
     * @param int $id ID dari order_item yang akan dihapus
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove($id)
    {
        // Cari item yang akan dihapus
        $orderItem = Order_item::where('id', $id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                  ->where('status', 'pending'); // Perbaikan: menggunakan 'pending' agar konsisten
            })
            ->firstOrFail();

        // Simpan referensi ke order
        $order = $orderItem->order;
        
        // Hapus item
        $orderItem->delete();

        // Update total order
        $this->updateOrderTotals($order);

        return response()->json([
            'success'  => true,
            'subtotal' => $order->subtotal,
            'tax'      => $order->tax,
            'total'    => $order->grand_total
        ]);
    }

    /**
     * Helper function untuk update total order
     * Fungsi ini menghitung ulang subtotal, pajak, dan total keseluruhan untuk order
     * 
     * @param Order $order Order yang akan diupdate totalnya
     * @return void
     */
    private function updateOrderTotals($order)
    {
        // Hitung subtotal dari semua item
        $subtotal = $order->orderItems->sum(function($item) {
            return $item->price * $item->quantity;
        });
        
        // Hitung pajak 10%
        $tax = (int)($subtotal * 0.1);
        
        // Hitung total keseluruhan
        $grandTotal = $subtotal + $tax;

        // Update order dengan nilai baru
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $grandTotal
        ]);
    }
}
