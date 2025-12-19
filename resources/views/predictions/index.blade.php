@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Tüm Tahminler</h1>

            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-primary">
                <form method="GET" action="{{ route('predictions.index') }}"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hipodrom</label>
                        <select name="hippodrome"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Tüm Hipodromlar</option>
                            @foreach($hippodromes as $hippodrome)
                                <option
                                    value="{{ $hippodrome->id }}" {{ request('hippodrome') == $hippodrome->id ? 'selected' : '' }}>
                                    {{ $hippodrome->name }} ({{ $hippodrome->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tarih</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full bg-secondary hover:bg-green-800 text-white py-2 px-6 rounded-lg font-semibold transition shadow-md">
                            <i class="fas fa-filter mr-2"></i> Filtrele
                        </button>
                    </div>
                </form>

                @if(request()->has('hippodrome') || request()->has('date'))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('predictions.index') }}"
                           class="text-red-500 hover:text-red-700 font-semibold text-sm flex items-center">
                            <i class="fas fa-times-circle mr-1"></i> Filtreleri Temizle
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if($predictions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($predictions as $prediction)
                    <div
                        class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 border-t-4 {{ $prediction->access_level === 'premium' ? 'border-primary' : 'border-gray-400' }} flex flex-col h-full">
                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-secondary">{{ $prediction->hippodrome->name }}</h3>
                                    <p class="text-sm text-gray-500"><i
                                            class="fas fa-map-marker-alt mr-1"></i> {{ $prediction->hippodrome->city }}
                                    </p>
                                </div>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold {{ $prediction->access_level === 'premium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $prediction->access_level === 'premium' ? 'PREMIUM' : 'STANDART' }}
                                </span>
                            </div>

                            <div class="mb-4 pb-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-2xl font-bold text-gray-800">{{ $prediction->race_number }}.
                                            Koşu
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <i class="far fa-calendar-alt mr-1"></i> {{ $prediction->race_date ? \Carbon\Carbon::parse($prediction->race_date)->format('d.m.Y') : '-' }}
                                            <span class="mx-2">|</span>
                                            <i class="far fa-clock mr-1"></i> {{ $prediction->race_time ? \Carbon\Carbon::parse($prediction->race_time)->format('H:i') : '-' }}
                                        </div>
                                    </div>

                                    @if($prediction->prediction_result !== 'pending')
                                        <div class="text-right">
                                            <span
                                                class="px-3 py-1 rounded-full text-xs font-bold text-white {{ $prediction->prediction_result === 'won' ? 'bg-green-600' : 'bg-red-500' }}">
                                                {{ $prediction->prediction_result === 'won' ? 'KAZANDI' : 'KAYBETTİ' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                {{ Str::limit($prediction->basic_prediction, 120) }}
                            </p>
                        </div>

                        <div class="p-6 pt-0 mt-auto">
                            <a href="{{ route('predictions.show', $prediction) }}"
                               class="block w-full text-center bg-gray-50 hover:bg-secondary hover:text-white text-gray-700 py-3 rounded-lg font-semibold transition border border-gray-200">
                                Detayları Gör <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $predictions->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-100">
                <div class="text-6xl mb-4 text-gray-300"><i class="fas fa-search"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Tahmin Bulunamadı</h3>
                <p class="text-gray-500 mb-6">
                    @if(request()->has('hippodrome') || request()->has('date'))
                        Seçtiğiniz kriterlere uygun sonuç yok.
                    @else
                        Henüz yayınlanmış bir tahmin bulunmuyor.
                    @endif
                </p>
                @if(request()->has('hippodrome') || request()->has('date'))
                    <a href="{{ route('predictions.index') }}"
                       class="inline-block bg-primary hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                        Tümünü Göster
                    </a>
                @endif
            </div>
        @endif

        @guest
            <div
                class="mt-12 bg-gradient-to-r from-secondary to-gray-800 rounded-2xl shadow-xl p-8 md:p-12 text-white text-center relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-3xl font-bold mb-4">Kazananlar Kulübüne Katılın!</h3>
                    <p class="mb-8 text-lg text-gray-300 max-w-2xl mx-auto">Üye olarak yapay zeka destekli detaylı
                        analizlere, banko kuponlara ve özel tüyolara anında erişebilirsiniz.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}"
                           class="bg-primary text-secondary hover:bg-white px-8 py-3 rounded-xl font-bold transition transform hover:scale-105 shadow-lg">
                            Hemen Üye Ol
                        </a>
                        <a href="{{ route('subscriptions.index') }}"
                           class="bg-transparent border-2 border-white hover:bg-white hover:text-secondary px-8 py-3 rounded-xl font-bold transition">
                            Paketleri İncele
                        </a>
                    </div>
                </div>
                <div
                    class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
                <div
                    class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-primary opacity-10 rounded-full blur-3xl"></div>
            </div>
        @endguest
    </div>
@endsection
