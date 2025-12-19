<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Repositories\PredictionRepository;
use App\Repositories\SubscriptionPlanRepository;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(
        protected PredictionRepository       $predictionRepo,
        protected SubscriptionPlanRepository $planRepo,
        protected ContactService             $contactService
    )
    {
    }

    public function index(): View
    {
        $stats = $this->predictionRepo->getStats();

        $todayPredictions = $this->predictionRepo->getTodayPredictions(null); // Misafir olduğu için userLevel null olabilir

        $plans = $this->planRepo->getActivePlans();

        $user = Auth::user();
        $activeSubscription = $user?->activeSubscription;

        return view('home', [
            'todayPredictions' => $todayPredictions,
            'successRate' => $stats['rate'],
            'totalPredictions' => $stats['total'],
            'plans' => $plans,
            'activeSubscription' => $activeSubscription
        ]);
    }

    public function contactSubmit(StoreContactRequest $request): RedirectResponse
    {
        try {
            $this->contactService->storeMessage(
                $request->validated(),
                $request->ip(),
                $request->header('User-Agent')
            );

            return back()->with('success', 'Mesajınız kaydedildi. En kısa sürede döneceğiz.');

        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Mesaj gönderilirken bir hata oluştu.');
        }
    }


    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function privacy(): View
    {
        return view('privacy');
    }

    public function terms(): View
    {
        return view('terms');
    }
}
