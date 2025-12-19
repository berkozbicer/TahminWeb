@extends('layouts.app')

@section('title', 'Erişim Engellendi')

@section('content')
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 items-center">
        <div class="text-center">
            {{-- İkon / Görsel --}}
            <div class="mb-6 flex justify-center">
                <div class="h-24 w-24 bg-red-100 rounded-full flex items-center justify-center animate-bounce">
                    <i class="fas fa-lock text-4xl text-red-600"></i>
                </div>
            </div>

            {{-- Hata Kodu --}}
            <p class="text-base font-semibold text-red-600 tracking-wide uppercase">403 HATA</p>

            {{-- Başlık --}}
            <h1 class="mt-2 text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
                Erişim İzniniz Yok
            </h1>

            {{-- Açıklama --}}
            <p class="mt-4 text-lg text-gray-500 max-w-lg mx-auto">
                Üzgünüz, bu sayfayı görüntülemek için yetkiniz bulunmuyor.
                Eğer yöneticiyseniz lütfen doğru hesapla giriş yaptığınızdan emin olun.
            </p>

            {{-- Butonlar --}}
            <div class="mt-8 flex justify-center gap-4">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-green-800 transition shadow-lg">
                    <i class="fas fa-home mr-2"></i> Anasayfaya Dön
                </a>

                {{-- Eğer çıkış yapıp tekrar girmek isterse diye --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
