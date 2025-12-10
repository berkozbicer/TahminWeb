@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-secondary mb-2">
                Aboneliğini Yükselt
            </h1>
            <p class="text-gray-600">
                Daha fazla hipodrom, detaylı analiz ve Premium banko kuponlar için paketini büyüt.
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

        @if(isset($activeSubscription) && $activeSubscription)
            <div class="mb-6 bg-white rounded-xl shadow p-5 border border-gray-100">
                <h2 class="font-semibold text-lg text-secondary mb-1">
                    Mevcut Paketiniz
                </h2>
                <p class="text-gray-700">
                    Plan: <strong>{{ $activeSubscription->plan->name ?? 'Bilinmiyor' }}</strong><br>
                    Bitiş Tarihi:
                    @if($activeSubscription->expires_at)
                        <strong>{{ $activeSubscription->expires_at->format('d.m.Y') }}</strong>
                    @else
                        <strong>Süresiz / Tanımsız</strong>
                    @endif
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($plans as $plan)
                @php
                    $isCurrent = isset($activeSubscription)
                        && $activeSubscription
                        && $activeSubscription->subscription_plan_id === $plan->id
                        && $activeSubscription->isActive();
                @endphp

                <div class="bg-white rounded-2xl shadow-lg p-8 border
                    {{ $plan->slug === 'premium' ? 'border-primary' : 'border-gray-100' }}">
                    <h2 class="text-2xl font-bold text-secondary mb-2">
                        {{ $plan->name }}
                    </h2>
                    <p class="text-gray-600 mb-4">
                        {{ $plan->description }}
                    </p>

                    <div class="mb-6">
                        <span class="text-3xl font-extrabold text-secondary">
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

                    <div>
                        @if($isCurrent)
                            <button class="w-full bg-gray-100 text-gray-500 cursor-default px-4 py-2 rounded-lg">
                                Şu Anki Paketiniz
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
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('subscriptions.index') }}" class="text-primary font-semibold hover:underline">
                Tüm Paketleri Gör
            </a>
        </div>
    </div>
@endsection
