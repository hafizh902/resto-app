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
                                        {{ $item->category->cat_name ?? 'Tidak diketahui' }}s
                                    </div>
                                    <div class="p-4 border border-secondary border-top-0 rounded-bottom">
                                        <h4>{{ $item->name }}</h4>
                                        <p class="text-limited">{{ $item->description }}</p>
                                        <div class="d-flex justify-content-between flex-lg-wrap">
                                            <p class="text-dark fs-5 fw-bold mb-0">Rp{{ number_format($item->price, 0, ',', '.') }}</p>
                                            <a href="#"
                                                class="btn border border-secondary rounded-pill px-3 text-primary"><i
                                                    class="fa fa-shopping-bag me-2 text-primary"></i> Tambah
                                                Keranjang</a>
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
        fetch('{{ route('cart.add', ['id' => '']) }}/' + menuId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
            body: JSON.stringify({
                id: menuId,
            })
        })
        .then(response => response.json())      
        .then(data => {
            if (data.success) {
                alert('Item added to cart successfully!');
            } else {
                alert('Failed to add item to cart.');
            }
        })          
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the item to the cart.');
        }); 
    }
</script>
@endsection