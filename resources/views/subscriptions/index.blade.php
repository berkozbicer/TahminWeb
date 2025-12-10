@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-secondary mb-3">
                Abonelik Paketleri
            </h1>
            <p class="text-gray-600">
                Standart ve Premium paketlerimizle günlük at yarışı tahminlerine erişin.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                @php
                    $isActive = isset($activeSubscription)
                        && $activeSubscription
                        && $activeSubscription->subscription_plan_id === $plan->id
                        && $activeSubscription->isActive();
                @endphp

                <div class="relative bg-white rounded-2xl shadow-lg p-8 border
                    {{ $plan->slug === 'premium' ? 'border-primary' : 'border-gray-100' }}">
                    @if($plan->slug === 'premium')
                        <span
                            class="absolute -top-3 right-6 inline-block bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">
                            En Çok Tercih Edilen
                        </span>
                    @endif

                    <h2 class="text-2xl font-bold text-secondary mb-2">
                        {{ $plan->name }}
                    </h2>

                    <p class="text-gray-600 mb-4">
                        {{ $plan->description }}
                    </p>

                    <div class="mb-6">
                        <span class="text-4xl font-extrabold text-secondary">
                            ₺{{ number_format($plan->price, 2, ',', '.') }}
                        </span>
                        <span class="text-gray-500">/ {{ $plan->duration_days }} gün</span>
                    </div>

                    @if($plan->features && is_array($plan->features))
                        <ul class="mb-6 space-y-2 text-sm text-gray-700">
                            @foreach($plan->features as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-2 h-2 rounded-full bg-primary"></span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-6">
                        @auth
                            @if($isActive)
                                <div class="text-green-600 font-semibold mb-2">
                                    Aktif Aboneliğiniz
                                </div>
                                <button class="w-full bg-gray-100 text-gray-500 cursor-default px-4 py-2 rounded-lg">
                                    Kullanımda
                                </button>
                            @else
                                <form method="POST" action="{{ route('subscriptions.subscribe', $plan) }}">
                                    @csrf
                                    <button
                                        class="w-full bg-primary text-white font-semibold px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                                        Bu Pakete Geç
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full text-center bg-primary text-white font-semibold px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                                Giriş Yap ve Satın Al
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        @guest
            <div class="mt-12 text-center text-gray-600">
                Henüz üye değil misiniz?
                <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">
                    Hemen kayıt olun
                </a>
            </div>
        @endguest
    </div>
@endsection
