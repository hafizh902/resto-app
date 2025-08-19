@extends('customer.layouts.master')

@section('content')
<div class="container-fluid fruite py-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="row g-3">
                    <div class="col-lg">
                        <div class="row g-4 justify-content-center">
                            @foreach ($items as $item)
                            <div class="col-md-6 col-lg-6 col-xl-4">
                                <div class="rounded position-relative fruite-item">
                                    <div class="fruite-img">
                                        <img src="{{ $item->img }}"
                                            class="img-fluid w-100 rounded-top" alt="{{ $item->name }}">
                                    </div>
                                    <div class="text-white {{ ($item->category->cat_name ?? 'Makanan') == 'Minuman' ? 'bg-info' : 'bg-secondary' }} px-3 py-1 rounded position-absolute"
                                        style="top: 10px; left: 10px;">
                                        {{ $item->category->cat_name ?? 'Tidak diketahui' }}
                                    </div>
                                    <div class="p-4 border border-secondary border-top-0 rounded-bottom">
                                        <h4>{{ $item->name }}</h4>
                                        <p class="text-limited">{{ $item->description }}</p>
                                        <div class="d-flex justify-content-between flex-lg-wrap">
                                            <p class="text-dark fs-5 fw-bold mb-0">
                                                Rp{{ number_format($item->price, 0, ',', '.') }}
                                            </p>
                                            <button 
                                                class="btn border border-secondary rounded-pill px-3 text-primary"
                                                onclick="addToCart({{ $item->id }})">
                                                <i class="fa fa-shopping-bag me-2 text-primary"></i> Tambah Keranjang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function addToCart(menuId) {
    fetch(`/cart/add/${menuId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(res => {
        // Check if response is ok
        if (!res.ok) {
            if (res.status === 401) {
                // User not authenticated
                if (confirm('Silakan login terlebih dahulu untuk menambahkan item ke keranjang. Login sekarang?')) {
                    window.location.href = '{{ route("login") }}';
                }
                return;
            }
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            alert('Menu berhasil ditambahkan ke keranjang!');
            window.location.href = '{{ route("cart") }}';
        } else {
            alert(data.message || 'Gagal menambahkan menu ke keranjang.');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Terjadi kesalahan saat menambahkan ke keranjang. Silakan coba lagi.');
    });
}
</script>
@endsection
