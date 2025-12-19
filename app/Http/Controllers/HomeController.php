<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        // Bugünün yayınlanmış tahminleri
        $todayPredictions = Prediction::with('hippodrome')
            ->published()
            ->today()
            ->orderBy('race_time')
            ->get();

        // Basit istatistikler (cache ile optimize edildi)
        $totalPredictions = cache()->remember('stats.total_predictions', 3600, function () {
            return Prediction::published()->count();
        });
        
        $wonCount = cache()->remember('stats.won_predictions', 3600, function () {
            return Prediction::published()->where('prediction_result', Prediction::RESULT_WON)->count();
        });
        
        $successRate = $totalPredictions > 0
            ? round(($wonCount / $totalPredictions) * 100, 1)
            : 0;

        // Planlar (cache ile optimize edildi)
        $plans = cache()->remember('subscription_plans.active', 3600, function () {
            return SubscriptionPlan::active()
                ->orderBy('price')
                ->get();
        });

        $user = Auth::user();
        $activeSubscription = $user?->activeSubscription;

        return view('home', compact(
            'todayPredictions',
            'successRate',
            'totalPredictions',
            'plans',
            'activeSubscription'
        ));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Save to DB first so message isn't lost if mail fails
            $contact = \App\Models\ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'message' => $validated['message'],
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            // Dispatch email sending to queue
            \App\Jobs\SendContactMessageEmail::dispatch($contact);

            return back()->with('success', 'Mesajınız kaydedildi. E-posta arka planda gönderilecektir.');
        } catch (\Throwable $e) {
            Log::error('Contact form send failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Mesaj işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        }
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }
}
