@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8">
        <nav class="flex mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="hover:text-primary transition">Ana Sayfa</a>
                </li>
                <li><i class="fas fa-chevron-right text-xs mx-2"></i></li>
                <li>
                    <a href="{{ route('predictions.index') }}" class="hover:text-primary transition">Tahminler</a>
                </li>
                <li><i class="fas fa-chevron-right text-xs mx-2"></i></li>
                <li aria-current="page"
                    class="text-gray-900 font-semibold truncate max-w-xs">{{ $prediction->hippodrome->name }}</li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-secondary text-white p-6 md:p-8 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold">{{ $prediction->race_title ?? $prediction->race_number . '. Koşu' }}</h1>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $prediction->access_level === 'premium' ? 'bg-primary text-secondary' : 'bg-gray-700 text-gray-200' }}">
                                    {{ $prediction->access_level === 'premium' ? 'PREMIUM' : 'STANDART' }}
                                </span>
                            </div>
                            <p class="text-gray-300 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i> {{ $prediction->hippodrome->name }}
                                - {{ $prediction->hippodrome->city }}
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 text-center min-w-[100px]">
                            <div
                                class="text-2xl font-bold">{{ $prediction->race_time ? \Illuminate\Support\Carbon::parse($prediction->race_time)->format('H:i') : '--:--' }}</div>
                            <div
                                class="text-xs text-gray-300 uppercase">{{ $prediction->race_date ? \Illuminate\Support\Carbon::parse($prediction->race_date)->format('d.m.Y') : '-' }}</div>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full transform translate-x-1/2 -translate-y-1/2"></div>
            </div>

            <div class="p-6 md:p-8">
                @if($canAccess)
                    @if($prediction->basic_prediction)
                        <div class="mb-10">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                <span
                                    class="bg-secondary text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                                Genel Değerlendirme
                            </h2>
                            <div
                                class="bg-gray-50 rounded-xl p-6 border border-gray-100 text-gray-700 leading-relaxed whitespace-pre-line shadow-sm">
                                {{ $prediction->basic_prediction }}
                            </div>
                        </div>
                    @endif

                    @if($prediction->detailed_analysis && $prediction->isPremiumOnly())
                        <div class="mb-10">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                <span
                                    class="bg-primary text-secondary w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm"><i
                                        class="fas fa-star"></i></span>
                                Detaylı Analiz
                                <span
                                    class="ml-3 px-2 py-0.5 bg-primary/20 text-primary text-[10px] uppercase font-bold rounded">Premium</span>
                            </h2>
                            <div
                                class="bg-gradient-to-br from-yellow-50 to-white rounded-xl p-6 border border-yellow-100 text-gray-800 leading-relaxed whitespace-pre-line shadow-sm">
                                {{ $prediction->detailed_analysis }}
                            </div>
                        </div>
                    @endif

                    @if($prediction->banker_tips && $prediction->isPremiumOnly())
                        <div class="mb-10">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                <span
                                    class="bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm"><i
                                        class="fas fa-fire"></i></span>
                                Banko & Sürprizler
                            </h2>
                            <div
                                class="bg-red-50 rounded-xl p-6 border-l-4 border-red-500 text-gray-800 leading-relaxed whitespace-pre-line font-medium">
                                {{ $prediction->banker_tips }}
                            </div>
                        </div>
                    @endif

                    @if($prediction->statistics && count($prediction->statistics) > 0)
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-chart-pie mr-3 text-gray-400"></i> İstatistikler
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                @foreach($prediction->statistics as $key => $value)
                                    <div
                                        class="bg-gray-50 rounded-lg p-4 border border-gray-100 text-center hover:shadow-md transition">
                                        <div
                                            class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                        <div class="text-lg font-bold text-secondary">{{ $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($prediction->winning_horse)
                        <div
                            class="mt-8 bg-green-50 border border-green-200 rounded-xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-green-800 mb-1"><i
                                        class="fas fa-flag-checkered mr-2"></i>Yarış Sonucu</h3>
                                <p class="text-green-700">
                                    Kazanan: <strong>{{ $prediction->winning_horse }}</strong>
                                    @if($prediction->winning_odds)
                                        <span class="opacity-75 mx-2">|</span> Oran:
                                        <strong>{{ $prediction->winning_odds }}</strong>
                                    @endif
                                </p>
                            </div>
                            @if($prediction->prediction_result === 'won')
                                <div
                                    class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold shadow-sm animate-pulse">
                                    ✓ TAHMİN TUTTU
                                </div>
                            @elseif($prediction->prediction_result === 'lost')
                                <div class="bg-red-500 text-white px-6 py-2 rounded-lg font-bold shadow-sm">
                                    ✗ TUTMADI
                                </div>
                            @endif
                        </div>
                    @endif

                @else
                    <div class="py-12 text-center">
                        <div class="inline-block p-4 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-lock text-4xl text-gray-400"></i>
                        </div>

                        @if($needsUpgrade)
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Bu İçerik Premium Üyelere Özeldir</h2>
                            <p class="text-gray-500 max-w-lg mx-auto mb-8">
                                Bu koşuya ait detaylı analizleri, banko tahminleri ve özel istatistikleri görüntülemek
                                için hesabınızı yükseltin.
                            </p>
                            <a href="{{ route('subscriptions.upgrade') }}"
                               class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-xl text-secondary bg-primary hover:bg-yellow-400 shadow-lg transition transform hover:scale-105">
                                <i class="fas fa-crown mr-2"></i> Premium'a Geç
                            </a>
                        @else
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Abonelik Gerekli</h2>
                            <p class="text-gray-500 max-w-lg mx-auto mb-8">
                                Profesyonel tahminleri görüntülemek için aktif bir aboneliğiniz olmalıdır. Hemen
                                paketlerimizi inceleyin.
                            </p>
                            <a href="{{ route('subscriptions.index') }}"
                               class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-secondary hover:bg-green-800 shadow-lg transition transform hover:scale-105">
                                Paketleri İncele
                            </a>
                        @endif
                    </div>
                    <div class="mt-8 space-y-4 opacity-30 select-none filter blur-sm pointer-events-none"
                         aria-hidden="true">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        <div class="h-32 bg-gray-100 rounded-xl mt-4"></div>
                    </div>
                @endif
            </div>
        </div>

        @if(count($relatedPredictions) > 0)
            <div class="border-t border-gray-200 pt-8 mt-12">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Bu Günün Diğer Yarışları</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($relatedPredictions as $related)
                        <a href="{{ route('predictions.show', $related) }}"
                           class="group block bg-white rounded-lg p-4 border border-gray-100 hover:border-primary hover:shadow-md transition">
                            <div class="flex justify-between items-center mb-2">
                                <span
                                    class="font-bold text-secondary group-hover:text-primary transition">{{ $related->hippodrome->name }}</span>
                                <span
                                    class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($related->race_time)->format('H:i') }}</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $related->race_number }}. Koşu
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
