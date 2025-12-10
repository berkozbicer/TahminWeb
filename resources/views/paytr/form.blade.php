<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ödeme Yönlendiriliyor</title>
</head>
<body>
    <p>Ödemeniz güvenli ödeme sayfasına yönlendiriliyor. Eğer otomatik olarak yönlendirilmezseniz, "Ödemeye Devam Et" butonuna tıklayın.</p>

    <form id="paytr_form" method="post" action="https://www.paytr.com/odeme/guvenli">
        <input type="hidden" name="merchant_id" value="{{ $merchant_id }}">
        <input type="hidden" name="token" value="{{ $token }}">
        <noscript>
            <button type="submit">Ödemeye Devam Et</button>
        </noscript>
    </form>

    <script>
        try {
            document.getElementById('paytr_form').submit();
        } catch (e) {
            console.error(e);
        }
    </script>
</body>
</html>
