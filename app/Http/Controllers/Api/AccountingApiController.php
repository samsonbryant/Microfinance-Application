<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class AccountingApiController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->middleware('auth:web');
        $this->accountingService = $accountingService;
    }

    public function getMetrics(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $profitLoss = $this->accountingService->getProfitAndLoss($fromDate, $toDate);
        $cashPosition = $this->accountingService->getCashPosition($toDate);

        return response()->json([
            'profit_loss' => $profitLoss,
            'cash_position' => $cashPosition,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function getRevenueBreakdown(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $breakdown = $this->accountingService->getRevenueBreakdown($fromDate, $toDate);

        return response()->json([
            'breakdown' => $breakdown,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    public function getCashPosition(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        $cashPosition = $this->accountingService->getCashPosition($asOfDate);

        return response()->json([
            'cash_position' => $cashPosition,
            'as_of_date' => $asOfDate,
        ]);
    }
}

