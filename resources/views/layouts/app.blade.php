<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BRAVE') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700,800,900" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        @php
            $cartCount = auth()->check()
                ? \App\Models\Cart::where('user_id', auth()->id())->count()
                : 0;
        @endphp

        @if(session('success'))
            <div class="alert alert-success fw-bold text-center mb-0 rounded-0">
                {{ session('success') }}
            </div>
        @endif

        <header class="brave-header">
            <div class="brave-header__main">
                <a class="brave-logo" href="{{ url('/') }}" aria-label="BRAVE homepage">BRAVE</a>

                <form class="brave-search" action="{{ url('/') }}" method="GET" role="search">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="search" name="search" placeholder="Tìm kiếm" value="{{ request('search') }}" aria-label="Tìm kiếm sản phẩm">
                    <button type="submit" aria-label="Tìm kiếm">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m21 21-4.7-4.7m2-5.3a7.3 7.3 0 1 1-14.6 0 7.3 7.3 0 0 1 14.6 0Z" />
                        </svg>
                    </button>
                </form>

                <div class="brave-actions" aria-label="Tài khoản và tiện ích">
                    @guest
                        <a class="brave-icon" href="{{ route('login') }}" aria-label="Đăng nhập">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20 21a8 8 0 0 0-16 0" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </a>
                    @else
                        <div class="dropdown">
                            <a class="brave-icon" href="#" id="braveAccountMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tài khoản">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 21a8 8 0 0 0-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="braveAccountMenu">
                                <h6 class="dropdown-header">{{ Auth::user()->name }}</h6>
                                <a class="dropdown-item" href="/profile">Thông tin của tôi</a>
                                @if(auth()->user()->role !== 'admin')
                                    <a class="dropdown-item" href="/profile/orders">Lịch sử đơn hàng</a>
                                @endif
                                @if(auth()->user()->role === 'admin')
                                    <a class="dropdown-item" href="/admin/dashboard">Bảng thống kê</a>
                                    <a class="dropdown-item" href="/admin/products">Quản lý kho</a>
                                    <a class="dropdown-item" href="/admin/orders">Quản lý đơn hàng</a>
                                    <a class="dropdown-item" href="/admin/categories">Danh mục</a>
                                    <a class="dropdown-item" href="/admin/users">Tài khoản</a>
                                    <a class="dropdown-item" href="/admin/banners">Quản lý banner</a>
                                    <a class="dropdown-item" href="/admin/vouchers">Voucher</a>
                                @endif
                                <hr class="dropdown-divider">
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Đăng xuất
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest

                    <a class="brave-icon brave-icon--badge" href="/cart" aria-label="Giỏ hàng" data-count="{{ $cartCount }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 6h15l-1.6 8.2a2 2 0 0 1-2 1.6H8.6a2 2 0 0 1-2-1.7L5 3H2" />
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="18" cy="21" r="1" />
                        </svg>
                    </a>

                    <button class="brave-icon" type="button" id="brave-chat-shortcut" aria-label="Hỗ trợ">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 14a8 8 0 0 1 16 0" />
                            <path d="M4 14v3a2 2 0 0 0 2 2h1v-7H6a2 2 0 0 0-2 2Zm16 0v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2Z" />
                            <path d="M9 19c1 1.3 5 1.3 6 0" />
                        </svg>
                    </button>

                </div>
            </div>

            <nav class="brave-nav" aria-label="Danh mục chính">
                <div class="brave-category">
                    <button class="brave-category__trigger" type="button" aria-expanded="false">
                        Thể loại
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    @isset($categories)
                        <div class="brave-mega" role="menu">
                            <aside class="brave-mega__list" aria-label="Danh sách thể loại">
                                <a class="brave-mega__item {{ !request('category') ? 'active' : '' }}" href="{{ url('/') }}" data-category-panel="all">Tất cả sản phẩm</a>
                                @foreach($categories as $cat)
                                    <a class="brave-mega__item {{ request('category') == $cat->id ? 'active' : '' }}" href="{{ url('/?category=' . $cat->id) }}" data-category-panel="cat-{{ $cat->id }}">{{ $cat->name }}</a>
                                @endforeach
                            </aside>

                            <section class="brave-mega__content">
                                <div>
                                    <h2>Lựa chọn cho bạn</h2>
                                    <div class="brave-mini-grid" id="brave-featured-menu">
                                        @foreach(($products ?? collect())->take(12) as $p)
                                            @php
                                                $menuImage = $p->image ?: optional($p->variants->first())->image;
                                            @endphp
                                            <a href="/product/{{ $p->id }}" class="brave-mini-product" data-product-category="cat-{{ $p->category_id }}">
                                                <img src="{{ $menuImage ?: 'https://dummyimage.com/160x160/f1f1f1/111111.png&text=BRAVE' }}" alt="{{ $p->name }}">
                                                <span>{{ $p->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <h2>Bạn có lẽ cũng thích</h2>
                                    <div class="brave-mini-grid brave-mini-grid--wide">
                                        @foreach(($products ?? collect())->skip(12)->take(21) as $p)
                                            @php
                                                $menuImage = $p->image ?: optional($p->variants->first())->image;
                                            @endphp
                                            <a href="/product/{{ $p->id }}" class="brave-mini-product" data-product-category="cat-{{ $p->category_id }}">
                                                <img src="{{ $menuImage ?: 'https://dummyimage.com/160x160/f1f1f1/111111.png&text=BRAVE' }}" alt="{{ $p->name }}">
                                                <span>{{ $p->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endisset
                </div>

                <a href="{{ url('/') }}">Chỉ dành cho bạn</a>
                <a href="{{ url('/?sort=new') }}">Hàng mới về</a>
                <a href="{{ url('/?sort=sales') }}">Doanh Thu</a>
                @isset($categories)
                    @foreach($categories->take(10) as $cat)
                        <a href="{{ url('/?category=' . $cat->id) }}">{{ $cat->name }}</a>
                    @endforeach
                @else
                    <a href="{{ url('/') }}">Quần áo nữ</a>
                    <a href="{{ url('/') }}">Đồ bơi</a>
                    <a href="{{ url('/') }}">Đôi giày</a>
                    <a href="{{ url('/') }}">Trẻ em</a>
                    <a href="{{ url('/') }}">Quần áo nam</a>
                @endisset
            </nav>
        </header>

        <main class="brave-main">
            @yield('content')
        </main>
    </div>

    @include('components.chat-widget')
</body>
</html>
