<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ödeme Başlatılıyor | Hipodrom Casusu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .loader {
            border-top-color: #DABE71;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }

        @keyframes spinner {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full text-center">
    <div class="mb-6 flex justify-center">
        <h1 class="text-2xl font-bold text-[#4C6E4E]">HİPODROM CASUSU</h1>
    </div>

    <div class="flex justify-center mb-4">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
    </div>

    <h2 class="text-xl font-bold text-gray-800 mb-2">Ödeme Sayfasına Yönlendiriliyorsunuz</h2>
    <p class="text-gray-500 text-sm mb-4">Lütfen bekleyiniz, güvenli bağlantı kuruluyor...</p>

    <form id="paytr_form" method="post" action="https://www.paytr.com/odeme/guvenli">
        <input type="hidden" name="merchant_id" value="{{ $merchant_id }}">
        <input type="hidden" name="token" value="{{ $token }}">
    </form>
</div>

<script>
    // Formu otomatik gönder
    window.onload = function () {
        setTimeout(function () {
            document.getElementById('paytr_form').submit();
        }, 500);
    };
</script>
</body>
</html>
