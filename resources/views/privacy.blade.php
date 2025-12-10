@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-4">Gizlilik Politikası</h1>

    <p class="text-gray-700 mb-4">Bu gizlilik politikası, Hipodrom Casusu olarak kullanıcı verilerinin nasıl toplandığını, kullanıldığını ve korunduğunu açıklar.</p>

    <h2 class="text-xl font-semibold mt-6 mb-2">Toplanan Bilgiler</h2>
    <p class="text-gray-700 mb-4">Kayıt olduğunuzda isim, e-posta gibi temel bilgileri toplarız. Ödeme işlemleri sırasında ödeme sağlayıcılarının sağladığı işlem kimlikleri saklanır; kart bilgileri saklanmaz.</p>

    <h2 class="text-xl font-semibold mt-6 mb-2">Kullanım Amaçları</h2>
    <p class="text-gray-700 mb-4">Veriler hizmet sunumu, faturalama, kullanıcı desteği ve yasal yükümlülükler için kullanılır.</p>

    <h2 class="text-xl font-semibold mt-6 mb-2">İletişim</h2>
    <p class="text-gray-700">Gizlilik ile ilgili sorularınız için <a href="{{ route('contact') }}" class="text-primary">iletişim</a> sayfasından bize ulaşabilirsiniz.</p>
</div>
@endsection
