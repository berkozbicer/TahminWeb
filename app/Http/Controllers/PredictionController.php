<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Prediction\IndexPredictionRequest;
use App\Models\Prediction;
use App\Repositories\HippodromeRepository;
use App\Repositories\PredictionRepository;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PredictionController extends Controller
{
    public function __construct(
        protected HippodromeRepository $hippodromeRepo,
        protected PredictionRepository $predictionRepo,
        protected PredictionService    $predictionService
    )
    {
    }

    /**
     * /tahminler
     */
    public function index(IndexPredictionRequest $request): View
    {
        $date = $request->input('date', now()->toDateString());
        $hipInput = $request->input('hippodrome');

        $hippodromes = $this->hippodromeRepo->getActiveList();

        $selectedHippodrome = null;
        if ($hipInput) {
            $selectedHippodrome = $this->hippodromeRepo->findByIdOrSlug($hipInput);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $userLevel = $user?->getSubscriptionLevel();

        $predictions = $this->predictionRepo->getFilteredPredictions(
            $date,
            $selectedHippodrome?->id,
            $userLevel
        );

        return view('predictions.index', [
            'hippodromes' => $hippodromes,
            'hippodrome' => $selectedHippodrome,
            'predictions' => $predictions,
            'date' => $date
        ]);
    }

    /**
     * /tahminler/bugun
     */
    public function today(Request $request): View
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $userLevel = $user?->getSubscriptionLevel();

        $predictions = $this->predictionRepo->getTodayPredictions($userLevel);

        return view('predictions.today', [
            'predictions' => $predictions,
            'userLevel' => $userLevel,
        ]);
    }

    /**
     * /tahminler/{prediction}
     */
    public function show(Prediction $prediction): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $accessStatus = $this->predictionService->checkAccess($prediction, $user);

        $userLevel = $user?->getSubscriptionLevel();
        $relatedPredictions = $this->predictionRepo->getRelatedPredictions($prediction, $userLevel);

        return view('predictions.show', [
            'prediction' => $prediction,
            'canAccess' => $accessStatus['canAccess'],
            'needsUpgrade' => $accessStatus['needsUpgrade'],
            'relatedPredictions' => $relatedPredictions,
        ]);
    }
}
