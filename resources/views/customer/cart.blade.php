@extends('customer.layouts.master')

@section('content')
<!-- Cart Page Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <h1 class="mb-4">Keranjang Belanja</h1>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Total</th>
                                <th scope="col">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cartItems as $item)
                                <tr id="cart-item-{{ $item->id }}">
                                    <th scope="row">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $item->item->img ?? 'https://via.placeholder.com/80' }}"
                                                class="img-fluid me-5 rounded-circle" style="width: 80px; height: 80px;"
                                                alt="{{ $item->item->name }}">
                                        </div>
                                    </th>
                                    <td><p class="mb-0 mt-4">{{ $item->item->name }}</p></td>
                                    <td><p class="mb-0 mt-4">Rp{{ number_format($item->price, 0, ',', '.') }}</p></td>
                                    <td>
                                        <div class="input-group quantity mt-4" style="width: 100px;">
                                            <div class="input-group-btn">
                                                <button class="btn btn-sm btn-minus rounded-circle bg-light border"
                                                    onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </div>
                                            <input type="text" class="form-control form-control-sm text-center border-0 item-quantity"
                                                value="{{ $item->quantity }}" readonly>
                                            <div class="input-group-btn">
                                                <button class="btn btn-sm btn-plus rounded-circle bg-light border"
                                                    onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0 mt-4 item-total">
                                            Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                                        </p>
                                    </td>
                                    <td>
                                        <button class="btn btn-md rounded-circle bg-light border mt-4"
                                            onclick="removeFromCart({{ $item->id }})">
                                            <i class="fa fa-times text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <h4>Keranjang Anda Kosong</h4>
                                        <a href="{{ route('menu') }}" class="btn btn-primary">Lihat Menu</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Cart Summary -->
                @if($cartItems->count() > 0)
                <div class="row g-4 justify-content-end">
                    <div class="col-8"></div>
                    <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                        <div class="bg-light rounded">
                            <div class="p-4">
                                <h1 class="display-6 mb-4">Ringkasan <span class="fw-normal">Keranjang</span></h1>
                                <div class="d-flex justify-content-between mb-4">
                                    <h5 class="mb-0 me-4">Subtotal:</h5>
                                    <p class="mb-0">Rp{{ number_format($cartItems->sum(fn($item) => $item->price * $item->quantity), 0, ',', '.') }}</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <h5 class="mb-0 me-4">Pajak (10%):</h5>
                                    <p class="mb-0">Rp{{ number_format($cartItems->sum(fn($item) => $item->price * $item->quantity * 0.1), 0, ',', '.') }}</p>
                                </div>
                                <p class="mb-0 text-end">Termasuk pajak</p>
                            </div>
                            <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                                <h5 class="mb-0 ps-4 me-4">Total</h5>
                                <p class="mb-0 pe-4">Rp{{ number_format($cartItems->sum(fn($item) => $item->price * $item->quantity * 1.1), 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('checkout') }}" class="btn border-secondary rounded-pill px-4 py-3 text-primary text-uppercase mb-4 ms-4" type="button">Proses Checkout</a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- Cart Page End -->

<!-- JavaScript for Cart Functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // CSRF Token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Update quantity via AJAX
    function updateQuantity(itemId, newQuantity) {
        // Validasi jumlah minimal
        if (newQuantity < 1) {
            if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
                removeFromCart(itemId);
            }
            return;
        }

        // Validasi jumlah maksimal
        if (newQuantity > 1000) {
            alert('Jumlah tidak boleh lebih dari 1000');
            return;
        }

        // Tampilkan loading state
        const quantityInput = $(`#cart-item-${itemId} .item-quantity`);
        const totalElement = $(`#cart-item-${itemId} .item-total`);
        const originalValue = quantityInput.val();
        
        quantityInput.val('...');
        
        $.ajax({
            url: `/cart/update/${itemId}`,
            method: 'POST',
            data: {
                quantity: newQuantity,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update tampilan quantity
                    quantityInput.val(newQuantity);
                    
                    // Update total item
                    const newTotal = response.item_total;
                    totalElement.text(`Rp${newTotal.toLocaleString('id-ID')}`);
                    
                    // Update ringkasan keranjang
                    updateCartSummary(response);
                } else {
                    // Tampilkan pesan error dari server
                    alert(response.message || 'Terjadi kesalahan saat memperbarui jumlah.');
                    quantityInput.val(originalValue);
                }
            },
            error: function(xhr) {
                // Kembalikan nilai semula jika error
                quantityInput.val(originalValue);
                
                // Tampilkan pesan error yang lebih detail
                let errorMessage = 'Terjadi kesalahan saat memperbarui jumlah. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    }

    // Remove item from cart
    function removeFromCart(itemId) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            $.ajax({
                url: `/cart/remove/${itemId}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Remove item row
                        $(`#cart-item-${itemId}`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if ($('tbody tr').length === 0) {
                                location.reload();
                            } else {
                                updateCartSummary(response);
                            }
                        });
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus item. Silakan coba lagi.');
                }
            });
        }
    }

    // Update cart summary
    function updateCartSummary(response) {
        // This function would update the cart summary section
        // Implementation depends on your specific HTML structure
        console.log('Cart summary updated:', response);
    }
</script>
@endsection
