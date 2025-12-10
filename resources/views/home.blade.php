<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HİPODROM CASUSU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#DABE71',
                        secondary: '#4C6E4E',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
<!-- Navigation -->
<nav class="bg-secondary text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center space-x-8">
                <a href="/">
                    <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Hipodrom Casusu" class="h-12">
                </a>
                <div class="hidden md:flex space-x-4">
                    <a href="/" class="hover:text-primary transition">Ana Sayfa</a>
                    <a href="/tahminler" class="hover:text-primary transition">Tahminler</a>
                    <a href="/abonelik" class="hover:text-primary transition">Abonelik</a>
                    <a href="/iletisim" class="hover:text-primary transition">İletişim</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                @guest
                    <a href="/login" class="hover:text-primary transition">Giriş Yap</a>
                    <a href="/register" class="bg-primary hover:bg-red-700 px-4 py-2 rounded-lg transition">Kayıt Ol</a>
                @else
                    <a href="/panel" class="hover:text-primary transition">Panelim</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-primary transition">Çıkış</button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-secondary via-gray-800 to-primary text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Profesyonel At Yarışı Tahminleri</h1>
        <p class="text-xl mb-8 text-gray-300">Uzman analizler ile kazancınızı artırın</p>
        <div class="flex justify-center gap-4">
            <a href="/abonelik"
               class="bg-white text-secondary hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition transform hover:scale-105">
                Paketleri İncele
            </a>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-white py-12 shadow-md">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-6">
                <div class="text-5xl font-bold text-primary mb-2">{{ $successRate }}%</div>
                <div class="text-gray-600 text-lg">Başarı Oranı</div>
            </div>
            <div class="p-6">
                <div class="text-5xl font-bold text-primary mb-2">{{ $totalPredictions }}+</div>
                <div class="text-gray-600 text-lg">Toplam Tahmin</div>
            </div>
            <div class="p-6">
                <div class="text-5xl font-bold text-primary mb-2">5K+</div>
                <div class="text-gray-600 text-lg">Aktif Üye</div>
            </div>
        </div>
    </div>
</div>


<!-- Subscription Plans Preview -->
<div class="bg-secondary text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Abonelik Paketleri</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <!-- Standart -->
            <div class="bg-gray-800 rounded-xl p-8 hover:transform hover:scale-105 transition">
                <h3 class="text-2xl font-bold mb-4">Standart Üyelik</h3>
                <div class="text-4xl font-bold text-primary mb-6">₺199<span class="text-lg text-gray-400">/ay</span>
                </div>
                <ul class="space-y-3 mb-8 text-gray-300">
                    <li class="flex items-center">✓ Seçili şehir tahminleri</li>
                    <li class="flex items-center">✓ Basit analiz</li>
                    <li class="flex items-center">✓ Günlük bildirimler</li>
                    <li class="flex items-center">✓ Geçmiş tahmin arşivi</li>
                </ul>
                <a href="/abonelik"
                   class="block w-full text-center bg-white text-secondary py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                    Başla
                </a>
            </div>

            <!-- Premium -->
            <div class="bg-primary rounded-xl p-8 hover:transform hover:scale-105 transition relative">
                <div
                    class="absolute top-0 right-0 bg-yellow-400 text-secondary px-4 py-1 rounded-bl-lg rounded-tr-xl font-bold text-sm">
                    ÖNERİLEN
                </div>
                <h3 class="text-2xl font-bold mb-4">Premium Üyelik</h3>
                <div class="text-4xl font-bold mb-6">₺399<span class="text-lg text-gray-200">/ay</span></div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">✓ Tüm şehir tahminleri</li>
                    <li class="flex items-center">✓ Detaylı analiz ve istatistik</li>
                    <li class="flex items-center">✓ Özel BANKO kuponlar</li>
                    <li class="flex items-center">✓ Canlı WhatsApp desteği</li>
                    <li class="flex items-center">✓ Yarış öncesi bilgilendirme</li>
                    <li class="flex items-center">✓ Uzman yorumları</li>
                </ul>
                <a href="/abonelik"
                   class="block w-full text-center bg-white text-primary py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                    Premium'a Geç
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-secondary text-white py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-gray-400">&copy; 2025 HİPODROM CASUSU. Tüm hakları saklıdır.</p>
        <div class="mt-4 space-x-4">
            <a href="#" class="hover:text-primary transition">Gizlilik Politikası</a>
            <a href="#" class="hover:text-primary transition">Kullanım Şartları</a>
            <a href="/iletisim" class="hover:text-primary transition">İletişim</a>
        </div>
    </div>
</footer>
</body>
</html>
