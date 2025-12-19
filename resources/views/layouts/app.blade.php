<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
                        primary: '#DABE71', // Altın Sarısı
                        secondary: '#4C6E4E', // Yarış Yeşili
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans antialiased">

<nav class="bg-secondary text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center space-x-8">
                <a href="/">
                    <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Hipodrom Casusu" class="h-12 w-auto">
                </a>

                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('home') }}"
                       class="hover:text-primary transition duration-150 {{ request()->routeIs('home') ? 'text-primary font-bold' : '' }}">
                        Ana Sayfa
                    </a>
                    <a href="{{ route('predictions.index') }}"
                       class="hover:text-primary transition duration-150 {{ request()->routeIs('predictions.*') ? 'text-primary font-bold' : '' }}">
                        Tahminler
                    </a>

                    @auth
                        <a href="{{ route('predictions.today') }}"
                           class="hover:text-primary transition duration-150 {{ request()->routeIs('predictions.today') ? 'text-primary font-bold' : '' }}">
                            Bugünün Bülteni
                        </a>
                        <a href="{{ route('subscriptions.index') }}"
                           class="hover:text-primary transition duration-150 {{ request()->routeIs('subscriptions.*') ? 'text-primary font-bold' : '' }}">
                            Abonelik Paketleri
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:text-primary transition hidden sm:block font-medium">
                        Giriş Yap
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-primary text-secondary hover:bg-yellow-500 hover:text-white px-4 py-2 rounded-lg transition font-bold shadow-sm">
                        Kayıt Ol
                    </a>
                @else
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 hover:text-primary transition focus:outline-none">
                            <span class="hidden sm:block font-semibold">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                               :class="{'rotate-180': open}"></i>
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

                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                <p class="text-xs text-gray-500 uppercase">Hesap</p>
                                <p class="text-sm font-bold truncate text-primary">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('dashboard') }}"
                               class="block px-4 py-2 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-tachometer-alt mr-2 w-5 text-center text-gray-400"></i> Panelim
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-user mr-2 w-5 text-center text-gray-400"></i> Profil Ayarları
                            </a>

                            @if(auth()->user()->isAdmin())
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="/admin"
                                   class="block px-4 py-2 text-red-600 hover:bg-red-50 font-semibold transition">
                                    <i class="fas fa-shield-alt mr-2 w-5 text-center"></i> Yönetici Paneli
                                </a>
                            @endif

                            <div class="border-t border-gray-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-gray-600 hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i> Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest

                <button @click="document.getElementById('mobile-menu').classList.toggle('hidden')"
                        class="md:hidden text-white hover:text-primary focus:outline-none ml-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden bg-secondary border-t border-green-800">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="{{ route('home') }}"
               class="block py-2 text-white hover:bg-green-700 px-3 rounded {{ request()->routeIs('home') ? 'bg-green-800' : '' }}">Ana
                Sayfa</a>
            <a href="{{ route('predictions.index') }}"
               class="block py-2 text-white hover:bg-green-700 px-3 rounded {{ request()->routeIs('predictions.*') ? 'bg-green-800' : '' }}">Tahminler</a>
            @auth
                <a href="{{ route('predictions.today') }}"
                   class="block py-2 text-white hover:bg-green-700 px-3 rounded">Bugünün Bülteni</a>
                <a href="{{ route('subscriptions.index') }}"
                   class="block py-2 text-white hover:bg-green-700 px-3 rounded {{ request()->routeIs('subscriptions.*') ? 'bg-green-800' : '' }}">Abonelik</a>
            @endauth

            @guest
                <div class="border-t border-green-700 my-2 pt-2">
                    <a href="{{ route('login') }}" class="block py-2 text-gray-200 hover:text-white">Giriş Yap</a>
                    <a href="{{ route('register') }}" class="block py-2 text-primary font-bold">Kayıt Ol</a>
                </div>
            @endguest
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4">
    @if(session('success'))
        <div
            class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex items-center animate-fade-in-down"
            role="alert">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div
            class="mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex items-center animate-fade-in-down"
            role="alert">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if(session('info'))
        <div
            class="mt-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-r shadow-sm flex items-center animate-fade-in-down"
            role="alert">
            <i class="fas fa-info-circle mr-3 text-xl"></i>
            <div>{{ session('info') }}</div>
        </div>
    @endif
</div>

<main class="flex-grow">
    {{ $slot ?? '' }}
    @yield('content')
</main>

<footer class="bg-secondary text-white mt-16 border-t-4 border-primary">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            <div class="col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Logo" class="h-10">
                    <span class="text-xl font-bold text-primary tracking-wider">HİPODROM</span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed">
                     Uzman analizler ve yorumları ile kazanmaya bir adım daha yakın olun.
                </p>
                <div class="mt-4 flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4 text-primary">Hızlı Erişim</h3>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li>
                        <a href="{{ route('home') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Ana Sayfa</a>
                    </li>
                    <li>
                        <a href="{{ route('predictions.index') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Tahminler</a>
                    </li>
                    <li>
                        <a href="{{ route('subscriptions.index') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Premium
                            Üyelik</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4 text-primary">Kurumsal</h3>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li>
                        <a href="{{ route('about') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Hakkımızda</a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Gizlilik
                            Politikası</a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">Kullanım
                            Şartları</a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}"
                           class="hover:text-white hover:translate-x-1 transition-transform inline-block">İletişim
                            Formu</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4 text-primary">Bize Ulaşın</h3>
                <ul class="space-y-3 text-gray-300 text-sm">
                    <li class="flex items-center">
                        <i class="fas fa-envelope mr-3 w-4 text-primary"></i>
                        destek@hipodromcasusu.com
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-3 w-4 text-primary"></i>
                        İstanbul, Türkiye
                    </li>
                    <li class="flex items-center mt-4">
                        <i class="fas fa-shield-alt mr-3 w-4 text-green-400"></i>
                        <span class="text-green-100">PayTR ile Güvenli Ödeme</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-green-800 mt-10 pt-6 text-center text-gray-400 text-xs">
            <p>&copy; {{ date('Y') }} HİPODROM CASUSU. Tüm hakları saklıdır.</p>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
