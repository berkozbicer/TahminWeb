@extends('layouts.app')

@section('content')
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-secondary sm:text-4xl">
                    Kazanmaya Başlamak İçin <span class="text-primary">Profesyonellere Katılın</span>
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto">
                    Sıradan oyuncular şansa, <span class="font-bold text-gray-800">kazananlar veriye güvenir.</span>
                    Yapay zeka destekli analizlerle kuponlarınızı şansa bırakmayın.
                </p>
            </div>

            @if(session('error'))
                <div class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <p class="font-bold">Hata</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">

                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 opacity-75 hover:opacity-100 transition duration-300">
                    <h3 class="text-xl font-semibold text-gray-500">Misafir Üye</h3>
                    <p class="mt-4 text-gray-400 text-sm">Sadece siteyi gezinenler için.</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold text-gray-800">0 ₺</span>
                    </div>

                    <ul class="mt-6 space-y-4 text-sm text-gray-500">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 w-5 text-center"></i>
                            Geçmiş Sonuçlar
                        </li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 w-5 text-center"></i>
                            Temel Haberler
                        </li>
                        <li class="flex items-center"><i class="fas fa-times text-red-400 mr-2 w-5 text-center"></i>
                            <strong>Banko Tahminler</strong></li>
                        <li class="flex items-center"><i class="fas fa-times text-red-400 mr-2 w-5 text-center"></i>
                            <strong>Yapay Zeka Analizi</strong></li>
                        <li class="flex items-center"><i class="fas fa-times text-red-400 mr-2 w-5 text-center"></i>
                            <strong>Altılı Ganyan Şablonları</strong></li>
                    </ul>
                </div>

                @foreach($plans as $plan)
                    @php
                        $isActive = isset($activeSubscription)
                            && $activeSubscription
                            && $activeSubscription->subscription_plan_id === $plan->id
                            && $activeSubscription->isActive();

                        $isPremium = $plan->slug === 'premium' || $loop->last;
                    @endphp

                    <div
                        class="relative bg-white rounded-2xl shadow-xl border-2 {{ $isPremium ? 'border-primary transform scale-105 z-10' : 'border-gray-200' }} p-8 flex flex-col h-full">

                        @if($isPremium)
                            <div
                                class="absolute top-0 right-0 -mt-4 mr-4 bg-primary text-secondary text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide shadow-sm">
                                EN ÇOK KAZANDIRAN
                            </div>
                        @endif

                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 min-h-[40px]">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline">
                            <span
                                class="text-5xl font-extrabold text-gray-900">₺{{ number_format($plan->price, 0) }}</span>
                            <span class="ml-2 text-gray-500">/ {{ $plan->duration_days }} Gün</span>
                        </div>

                        <ul class="mt-6 space-y-4 mb-8 flex-grow">
                            @if(is_array($plan->features))
                                @foreach($plan->features as $feature)
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle text-primary text-lg"></i>
                                        </div>
                                        <p class="ml-3 text-base text-gray-700">{{ $feature }}</p>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400 italic">Özellik listesi yüklenemedi.</li>
                            @endif
                        </ul>

                        <div class="mt-auto">
                            @auth
                                @if($isActive)
                                    <button
                                        class="w-full bg-green-100 text-green-700 font-bold py-4 px-4 rounded-xl border border-green-200 cursor-default flex justify-center items-center gap-2"
                                        disabled>
                                        <i class="fas fa-check-circle"></i> <span>MEVCUT PAKETİNİZ</span>
                                    </button>
                                @else
                                    {{-- FORM ACTION GÜNCELLENDİ: ARTIK DOĞRU ROTAYA GİDİYOR --}}
                                    <form action="{{ route('subscriptions.payment.init', $plan) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="w-full bg-secondary hover:bg-green-800 text-white font-bold py-4 px-4 rounded-xl shadow-lg transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                                            <span>HEMEN BAŞLA</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                   class="block w-full bg-secondary hover:bg-green-800 text-white font-bold py-4 px-4 rounded-xl shadow-lg text-center transition transform hover:-translate-y-1">
                                    GİRİŞ YAP VE BAŞLA
                                </a>
                            @endauth

                            <p class="mt-3 text-xs text-center text-gray-400 flex justify-center items-center gap-1">
                                <i class="fas fa-lock"></i> PayTR ile 256-bit SSL Güvenli Ödeme
                            </p>
                        </div>
                    </div>
                @endforeach

            </div>

            <div class="mt-16 border-t border-gray-200 pt-10 text-center">
                <h4 class="text-lg font-bold text-gray-800 mb-6">Neden Hipodrom Casusu?</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <div class="text-primary text-4xl mb-3"><i class="fas fa-robot"></i></div>
                        <h5 class="font-bold text-gray-900">Yapay Zeka Analizi</h5>
                        <p class="text-sm text-gray-500 mt-2">Binlerce yarışı saniyeler içinde analiz eder, insan
                            gözünün kaçırdığı detayları yakalar.</p>
                    </div>
                    <div>
                        <div class="text-primary text-4xl mb-3"><i class="fas fa-chart-line"></i></div>
                        <h5 class="font-bold text-gray-900">Yüksek Başarı Oranı</h5>
                        <p class="text-sm text-gray-500 mt-2">İstatistiklerimiz şeffaftır. Geçmiş tahminlerimizi
                            inceleyerek başarımızı kendiniz görün.</p>
                    </div>
                    <div>
                        <div class="text-primary text-4xl mb-3"><i class="fas fa-mobile-alt"></i></div>
                        <h5 class="font-bold text-gray-900">Her An Yanınızda</h5>
                        <p class="text-sm text-gray-500 mt-2">Mobil uyumlu tasarımı ile hipodromda, evde veya yolda;
                            tüyolar her zaman cebinizde.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
