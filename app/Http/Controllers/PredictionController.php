<?php

namespace App\Http\Controllers;

use App\Models\Hippodrome;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    /**
     * /tahminler  -> tüm tahminler (filtreli)
     */
    public function index(Request $request)
    {
        // Hipodromlar cache ile optimize edildi
        $hippodromes = cache()->remember('hippodromes.active', 86400, function () {
            return Hippodrome::active()
                ->orderBy('name')
                ->get();
        });

        // Tarih filtresi (varsayılan: bugün)
        $date = $request->input('date');
        if (!$date) {
            $date = now()->toDateString();
        }

        // Hipodrom filtresi (id veya slug gelebilir)
        $hipInput = $request->input('hippodrome');

        $selectedHippodrome = null;

        if ($hipInput) {
            if (is_numeric($hipInput)) {
                $selectedHippodrome = Hippodrome::active()->find($hipInput);
            } else {
                $selectedHippodrome = Hippodrome::active()
                    ->where('slug', $hipInput)
                    ->first();
            }
        }

        $query = Prediction::with(['hippodrome', 'creator'])
            ->published()
            ->forDate($date);

        if ($selectedHippodrome) {
            $query->forHippodrome($selectedHippodrome->id);
        }

        // Kullanıcının erişim seviyesine göre filtre
        $user = Auth::user();
        $userLevel = $user?->getSubscriptionLevel();
        $query->accessibleForLevel($userLevel);

        // 🔥 BURAYI DEĞİŞTİRDİK: get() → paginate(12)->withQueryString()
        $predictions = $query
            ->orderBy('race_time')
            ->orderBy('race_number')
            ->paginate(12)
            ->withQueryString();

        // View'de kullanılan isimlerle gönderelim
        $hippodrome = $selectedHippodrome;

        return view('predictions.index', compact(
            'hippodromes',
            'hippodrome',
            'predictions',
            'date'
        ));
    }

    /**
     * /tahminler/bugun -> sadece bugünün tahminleri (özel sayfa)
     */
    public function today(Request $request)
    {
        $user = Auth::user();
        $userLevel = $user?->getSubscriptionLevel();

        $predictions = Prediction::with(['hippodrome', 'creator'])
            ->published()
            ->today()
            ->accessibleForLevel($userLevel)
            ->orderBy('race_time')
            ->orderBy('race_number')
            ->get();

        return view('predictions.today', [
            'predictions' => $predictions,
            'userLevel' => $userLevel,
        ]);
    }

    /**
     * /tahminler/{prediction} -> detay sayfası
     */
    public function show(Prediction $prediction)
    {
        $user = Auth::user();

        $canAccess = false;
        $needsUpgrade = false;

        if ($user && $user->hasActiveSubscription()) {
            // User modelindeki helper'ı kullan
            $canAccess = $user->canAccessPrediction($prediction->access_level);

            $userLevel = $user->getSubscriptionLevel();
            if (
                !$canAccess
                && $prediction->access_level === Prediction::ACCESS_PREMIUM
                && $userLevel !== 'premium'
            ) {
                $needsUpgrade = true;
            }
        }

        // İlgili diğer tahminler (aynı gün + aynı hipodrom)
        $userLevel = $user?->getSubscriptionLevel();
        $relatedPredictions = Prediction::with('hippodrome')
            ->published()
            ->forDate($prediction->race_date)
            ->forHippodrome($prediction->hippodrome_id)
            ->where('id', '<>', $prediction->id)
            ->accessibleForLevel($userLevel)
            ->orderBy('race_time')
            ->limit(4)
            ->get();

        return view('predictions.show', [
            'prediction' => $prediction,
            'canAccess' => $canAccess,
            'needsUpgrade' => $needsUpgrade,
            'relatedPredictions' => $relatedPredictions,
        ]);
    }
}
