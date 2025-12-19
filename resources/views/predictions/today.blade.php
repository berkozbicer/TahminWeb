@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-2">
                    Bugünün Bülteni
                </h1>
                <p class="text-gray-600">
                    <i class="far fa-calendar-check mr-2"></i> {{ now()->format('d.m.Y') }} tarihli tüm yarış
                    tahminleri.
                </p>
            </div>

            @auth
                @if(!auth()->user()->hasActiveSubscription())
                    <a href="{{ route('subscriptions.index') }}"
                       class="bg-primary text-secondary px-4 py-2 rounded-lg font-bold text-sm shadow hover:bg-yellow-400 transition">
                        <i class="fas fa-crown mr-1"></i> Tümünü Görmek İçin Abone Ol
                    </a>
                @endif
            @endauth
        </div>

        @if($predictions->isEmpty())
            <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-100">
                <div class="text-gray-300 text-6xl mb-4"><i class="far fa-folder-open"></i></div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Bülten Henüz Hazır Değil</h3>
                <p class="text-gray-500">
                    Editörlerimiz bugünkü yarışlar için analizlerini hazırlıyor. Lütfen daha sonra tekrar kontrol edin.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($predictions as $prediction)
                    <a href="{{ route('predictions.show', $prediction) }}"
                       class="group block bg-white rounded-xl shadow-sm hover:shadow-xl transition p-6 border border-gray-100 hover:border-primary relative overflow-hidden">

                        <div
                            class="absolute left-0 top-0 bottom-0 w-1 bg-primary transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom duration-300"></div>

                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-sm font-bold text-secondary uppercase tracking-wide flex items-center gap-2">
                                <i class="fas fa-horse-head"></i>
                                {{ $prediction->hippodrome->name }}
                            </span>
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold
                                {{ $prediction->access_level === 'premium' ? 'bg-primary/20 text-yellow-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $prediction->access_level === 'premium' ? 'PREMIUM' : 'STANDART' }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition">
                            {{ $prediction->race_title ?? ($prediction->race_number . '. Koşu') }}
                        </h2>

                        <div class="flex items-center text-sm text-gray-500 mb-4 gap-4">
                            <span class="flex items-center"><i class="far fa-clock mr-1.5"></i> {{ $prediction->race_time ? \Carbon\Carbon::parse($prediction->race_time)->format('H:i') : '--:--' }}</span>
                            <span class="text-gray-300">|</span>
                            <span class="flex items-center"><i class="far fa-calendar mr-1.5"></i> {{ $prediction->race_date ? \Carbon\Carbon::parse($prediction->race_date)->format('d.m.Y') : '' }}</span>
                        </div>

                        <div
                            class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600 line-clamp-2 mb-2 group-hover:bg-yellow-50/50 transition">
                            {{ $prediction->basic_prediction ?? 'Detaylı analiz için tıklayın.' }}
                        </div>

                        <div class="text-primary text-sm font-semibold flex items-center justify-end">
                            İncele <i
                                class="fas fa-chevron-right ml-1 text-xs transform group-hover:translate-x-1 transition"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @guest
            <div class="mt-12 bg-gray-900 rounded-2xl p-8 md:p-12 text-center text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold mb-4">Analizleri Kaçırmayın!</h2>
                    <p class="text-gray-400 mb-8 max-w-xl mx-auto">
                        Profesyonel ekibimizin hazırladığı günlük analizlere ve banko kuponlara erişmek için hemen
                        ücretsiz üyelik oluşturun.
                    </p>
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('register') }}"
                           class="bg-primary text-secondary hover:bg-white px-8 py-3 rounded-lg font-bold transition">
                            Hemen Kayıt Ol
                        </a>
                        <a href="{{ route('subscriptions.index') }}"
                           class="bg-transparent border border-gray-600 hover:border-white hover:text-white text-gray-300 px-8 py-3 rounded-lg font-bold transition">
                            Paketleri İncele
                        </a>
                    </div>
                </div>
                <div
                    class="absolute top-0 right-0 w-96 h-96 bg-primary opacity-5 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
            </div>
        @endguest
    </div>
@endsection
