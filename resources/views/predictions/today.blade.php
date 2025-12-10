@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-secondary mb-2">
                Bugünün Tahminleri
            </h1>
            <p class="text-gray-600">
                {{ now()->format('d.m.Y') }} tarihli, yayınlanmış tahminler.
            </p>
        </div>

        @if($predictions->isEmpty())
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-gray-600">
                    Bugün için henüz yayınlanmış tahmin bulunmuyor.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($predictions as $prediction)
                    <a href="{{ route('predictions.show', $prediction) }}"
                       class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-primary uppercase tracking-wide">
                                {{ $prediction->hippodrome->display_name ?? $prediction->hippodrome->name ?? 'Hipodrom' }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $prediction->access_level === 'premium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ $prediction->access_level === 'premium' ? 'Premium' : 'Standart' }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-900 mb-1">
                            {{ $prediction->race_title ?? ('Koşu #' . $prediction->race_number) }}
                        </h2>

                        <div class="flex items-center text-sm text-gray-500 mb-3 gap-3">
                            <span>{{ $prediction->race_time ? \Illuminate\Support\Carbon::parse($prediction->race_time)->format('H:i') : 'Saat Bilgisi Yok' }}</span>
                            <span>•</span>
                            <span>{{ $prediction->race_date?->format('d.m.Y') }}</span>
                        </div>

                        <p class="text-gray-700 line-clamp-3">
                            {{ $prediction->basic_prediction ?? 'Detaylar için tıklayın.' }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif

        @guest
            <div class="mt-10 bg-primary text-white rounded-2xl p-8 text-center shadow-lg">
                <h2 class="text-2xl font-bold mb-3">Tüm Tahminlere Erişmek İçin Giriş Yapın</h2>
                <p class="mb-6 text-white/80">
                    Daha detaylı analizler ve Premium tahminler için üyelik oluşturun.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-lg font-bold transition">
                        Hemen Kayıt Ol
                    </a>
                    <a href="{{ route('subscriptions.index') }}"
                       class="bg-transparent border-2 border-white hover:bg-white hover:text-primary px-6 py-3 rounded-lg font-bold transition">
                        Paketleri İncele
                    </a>
                </div>
            </div>
        @endguest
    </div>
@endsection
