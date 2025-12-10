<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HİPODROM CASUSU') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#DABE71',
                        secondary: '#4C6E4E',
                    }
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<nav class="bg-secondary text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center space-x-8">
                <a href="/">
                    <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Hipodrom Casusu" class="h-12">
                </a>

                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('home') }}"
                       class="hover:text-primary transition {{ request()->routeIs('home') ? 'text-primary font-semibold' : '' }}">
                        Ana Sayfa
                    </a>
                    <a href="{{ route('predictions.index') }}"
                       class="hover:text-primary transition {{ request()->routeIs('predictions.*') ? 'text-primary font-semibold' : '' }}">
                        Tahminler
                    </a>
                    @auth
                        <a href="{{ route('predictions.today') }}" class="hover:text-primary transition">
                            Bugünün Tahminleri
                        </a>
                        <a href="{{ route('subscriptions.index') }}"
                           class="hover:text-primary transition {{ request()->routeIs('subscriptions.*') ? 'text-primary font-semibold' : '' }}">
                            Abonelik
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:text-primary transition hidden sm:block">
                        Giriş Yap
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-primary hover:bg-red-700 px-4 py-2 rounded-lg transition font-semibold">
                        Kayıt Ol
                    </a>
                @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center space-x-2 hover:text-primary transition focus:outline-none">
                            <span class="hidden sm:block font-semibold">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 text-gray-800"
                             style="display: none;">

                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Giriş Yapıldı</p>
                                <p class="text-sm font-bold truncate text-primary">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('dashboard') }}"
                               class="block px-4 py-2 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-tachometer-alt mr-2 w-5 text-center"></i>Panelim
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-user mr-2 w-5 text-center"></i>Profilim
                            </a>

                            @if(auth()->user()->isAdmin())
                                <a href="/admin"
                                   class="block px-4 py-2 text-red-600 hover:bg-red-50 font-semibold transition">
                                    <i class="fas fa-cog mr-2 w-5 text-center"></i>Yönetim Paneli
                                </a>
                            @endif

                            <hr class="my-1 border-gray-100">

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-gray-600 hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i>Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest

                <button id="mobile-menu-btn" class="md:hidden text-white hover:text-primary focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden bg-gray-800 pb-4 border-t border-gray-700">
        <div class="px-4 space-y-2 pt-2">
            <a href="{{ route('home') }}" class="block py-2 hover:text-primary transition">Ana Sayfa</a>
            <a href="{{ route('predictions.index') }}" class="block py-2 hover:text-primary transition">Tahminler</a>
            <a href="{{ route('subscriptions.index') }}" class="block py-2 hover:text-primary transition">Abonelik</a>
            @guest
                <hr class="border-gray-600 my-2">
                <a href="{{ route('login') }}" class="block py-2 hover:text-primary transition">Giriş Yap</a>
                <a href="{{ route('register') }}" class="block py-2 text-primary font-bold transition">Kayıt Ol</a>
            @endguest
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span>{{ session('info') }}</span>
            </div>
        </div>
    </div>
@endif

<main class="flex-grow">
    {{ $slot ?? '' }}
    @yield('content')
</main>

<footer class="bg-secondary text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="text-2xl font-bold text-primary mb-4 flex items-center gap-4">
                    <a href="/">
                        <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Hipodrom Casusu" class="h-12">
                    </a>
                       HİPODROM CASUSU
                </div>
                <p class="text-gray-400 mb-4">
                    Türkiye'nin en güvenilir at yarışları tahmin platformu.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4">Hızlı Linkler</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-primary">Ana Sayfa</a></li>
                    <li><a href="{{ route('predictions.index') }}" class="hover:text-primary">Tahminler</a></li>
                    <li><a href="{{ route('subscriptions.index') }}" class="hover:text-primary">Abonelik</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4">İletişim</h3>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li><i class="fas fa-envelope mr-2"></i> destek@hipodromcasusu.com</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; {{ date('Y') }} HİPODROM CASUSU</p>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (btn && menu) {
            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
