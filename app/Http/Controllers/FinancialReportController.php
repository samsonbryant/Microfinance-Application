<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportsExport;

class FinancialReportController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view_financial_reports');
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        return view('accounting.reports.index');
    }

    public function profitAndLoss(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $data = $this->accountingService->getProfitAndLoss($fromDate, $toDate);
        $trends = $this->accountingService->getMonthlyTrends(12);
        $revenueBreakdown = $this->accountingService->getRevenueBreakdown($fromDate, $toDate);

        if ($request->ajax()) {
            return response()->json([
                'data' => $data,
                'trends' => $trends,
                'revenue_breakdown' => $revenueBreakdown,
            ]);
        }

        return view('accounting.reports.profit-loss', compact('data', 'trends', 'revenueBreakdown'));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        $data = $this->accountingService->getBalanceSheet($asOfDate);
        $cashPosition = $this->accountingService->getCashPosition($asOfDate);

        if ($request->ajax()) {
            return response()->json([
                'data' => $data,
                'cash_position' => $cashPosition,
            ]);
        }

        return view('accounting.reports.balance-sheet', compact('data', 'cashPosition'));
    }

    public function cashFlow(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $data = $this->accountingService->getCashFlowStatement($fromDate, $toDate);
        $trends = $this->accountingService->getMonthlyTrends(12);

        if ($request->ajax()) {
            return response()->json([
                'data' => $data,
                'trends' => $trends,
            ]);
        }

        return view('accounting.reports.cash-flow', compact('data', 'trends'));
    }

    public function revenueBoard(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $profitLoss = $this->accountingService->getProfitAndLoss($fromDate, $toDate);
        $revenueBreakdown = $this->accountingService->getRevenueBreakdown($fromDate, $toDate);
        $trends = $this->accountingService->getMonthlyTrends(12);

        if ($request->ajax()) {
            return response()->json([
                'profit_loss' => $profitLoss,
                'revenue_breakdown' => $revenueBreakdown,
                'trends' => $trends,
            ]);
        }

        return view('accounting.reports.revenue-board', compact('profitLoss', 'revenueBreakdown', 'trends'));
    }

    public function exportProfitAndLoss(Request $request, $format = 'pdf')
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $data = $this->accountingService->getProfitAndLoss($fromDate, $toDate);

        if ($format === 'pdf') {
            $pdf = PDF::loadView('accounting.reports.exports.profit-loss-pdf', compact('data'));
            return $pdf->download('profit-loss-' . now()->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new FinancialReportsExport('profit_loss', $data),
                'profit-loss-' . now()->format('Y-m-d') . '.xlsx'
            );
        } elseif ($format === 'csv') {
            return Excel::download(
                new FinancialReportsExport('profit_loss', $data),
                'profit-loss-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }
    }

    public function exportBalanceSheet(Request $request, $format = 'pdf')
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        $data = $this->accountingService->getBalanceSheet($asOfDate);

        if ($format === 'pdf') {
            $pdf = PDF::loadView('accounting.reports.exports.balance-sheet-pdf', compact('data'));
            return $pdf->download('balance-sheet-' . now()->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new FinancialReportsExport('balance_sheet', $data),
                'balance-sheet-' . now()->format('Y-m-d') . '.xlsx'
            );
        } elseif ($format === 'csv') {
            return Excel::download(
                new FinancialReportsExport('balance_sheet', $data),
                'balance-sheet-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }
    }

    public function exportCashFlow(Request $request, $format = 'pdf')
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $data = $this->accountingService->getCashFlowStatement($fromDate, $toDate);

        if ($format === 'pdf') {
            $pdf = PDF::loadView('accounting.reports.exports.cash-flow-pdf', compact('data'));
            return $pdf->download('cash-flow-' . now()->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new FinancialReportsExport('cash_flow', $data),
                'cash-flow-' . now()->format('Y-m-d') . '.xlsx'
            );
        } elseif ($format === 'csv') {
            return Excel::download(
                new FinancialReportsExport('cash_flow', $data),
                'cash-flow-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }
    }
}

