<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $reportType;
    protected $data;

    public function __construct($reportType, $data)
    {
        $this->reportType = $reportType;
        $this->data = $data;
    }

    public function array(): array
    {
        switch ($this->reportType) {
            case 'profit_loss':
                return $this->getProfitLossData();
            case 'balance_sheet':
                return $this->getBalanceSheetData();
            case 'trial_balance':
                return $this->getTrialBalanceData();
            case 'cash_flow':
                return $this->getCashFlowData();
            case 'loan_portfolio_aging':
                return $this->getLoanPortfolioAgingData();
            case 'provisioning_report':
                return $this->getProvisioningData();
            default:
                return [];
        }
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'profit_loss':
                return ['Account', 'Amount'];
            case 'balance_sheet':
                return ['Account', 'Balance'];
            case 'trial_balance':
                return ['Account Code', 'Account Name', 'Debit', 'Credit'];
            case 'cash_flow':
                return ['Activity', 'Cash In', 'Cash Out', 'Net Cash'];
            case 'loan_portfolio_aging':
                return ['Aging Bucket', 'Loan Count', 'Outstanding Balance', 'Percentage'];
            case 'provisioning_report':
                return ['Aging Bucket', 'Loan Count', 'Outstanding Balance', 'Provision Rate', 'Provision Amount'];
            default:
                return [];
        }
    }

    public function title(): string
    {
        return ucwords(str_replace('_', ' ', $this->reportType));
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getProfitLossData(): array
    {
        $data = [];
        
        // Revenue section
        $data[] = ['REVENUE', ''];
        foreach ($this->data['revenue'] as $revenue) {
            $data[] = [$revenue['account']->name, $revenue['formatted_amount']];
        }
        $data[] = ['Total Revenue', $this->data['formatted_totals']['total_revenue']];
        $data[] = ['', ''];
        
        // Expenses section
        $data[] = ['EXPENSES', ''];
        foreach ($this->data['expenses'] as $expense) {
            $data[] = [$expense['account']->name, $expense['formatted_amount']];
        }
        $data[] = ['Total Expenses', $this->data['formatted_totals']['total_expenses']];
        $data[] = ['', ''];
        
        // Net Income
        $data[] = ['Net Income (Loss)', $this->data['formatted_totals']['net_income']];
        
        return $data;
    }

    private function getBalanceSheetData(): array
    {
        $data = [];
        
        // Assets
        $data[] = ['ASSETS', ''];
        foreach ($this->data['assets'] as $category => $accounts) {
            if ($accounts->count() > 0) {
                $data[] = [ucwords(str_replace('_', ' ', $category)), ''];
                foreach ($accounts as $account) {
                    $data[] = [$account['account']->name, $account['formatted_balance']];
                }
            }
        }
        $data[] = ['Total Assets', $this->data['formatted_totals']['total_assets']];
        $data[] = ['', ''];
        
        // Liabilities
        $data[] = ['LIABILITIES', ''];
        foreach ($this->data['liabilities'] as $category => $accounts) {
            if ($accounts->count() > 0) {
                $data[] = [ucwords(str_replace('_', ' ', $category)), ''];
                foreach ($accounts as $account) {
                    $data[] = [$account['account']->name, $account['formatted_balance']];
                }
            }
        }
        $data[] = ['Total Liabilities', $this->data['formatted_totals']['total_liabilities']];
        $data[] = ['', ''];
        
        // Equity
        $data[] = ['EQUITY', ''];
        foreach ($this->data['equity'] as $category => $accounts) {
            if ($accounts->count() > 0) {
                $data[] = [ucwords(str_replace('_', ' ', $category)), ''];
                foreach ($accounts as $account) {
                    $data[] = [$account['account']->name, $account['formatted_balance']];
                }
            }
        }
        $data[] = ['Total Equity', $this->data['formatted_totals']['total_equity']];
        
        return $data;
    }

    private function getTrialBalanceData(): array
    {
        $data = [];
        
        foreach ($this->data['entries'] as $entry) {
            $data[] = [
                $entry['account']->code,
                $entry['account']->name,
                $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '',
                $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '',
            ];
        }
        
        return $data;
    }

    private function getCashFlowData(): array
    {
        $data = [];
        
        // Operating Activities
        $data[] = ['OPERATING ACTIVITIES', '', '', ''];
        foreach ($this->data['operating_activities'] as $activity) {
            $data[] = [
                $activity['description'],
                $activity['cash_in'] > 0 ? number_format($activity['cash_in'], 2) : '',
                $activity['cash_out'] > 0 ? number_format($activity['cash_out'], 2) : '',
                number_format($activity['net_cash'], 2),
            ];
        }
        $data[] = ['Net Operating Cash', '', '', $this->data['formatted_totals']['net_operating_cash']];
        $data[] = ['', '', '', ''];
        
        // Investing Activities
        $data[] = ['INVESTING ACTIVITIES', '', '', ''];
        foreach ($this->data['investing_activities'] as $activity) {
            $data[] = [
                $activity['description'],
                $activity['cash_in'] > 0 ? number_format($activity['cash_in'], 2) : '',
                $activity['cash_out'] > 0 ? number_format($activity['cash_out'], 2) : '',
                number_format($activity['net_cash'], 2),
            ];
        }
        $data[] = ['Net Investing Cash', '', '', $this->data['formatted_totals']['net_investing_cash']];
        $data[] = ['', '', '', ''];
        
        // Financing Activities
        $data[] = ['FINANCING ACTIVITIES', '', '', ''];
        foreach ($this->data['financing_activities'] as $activity) {
            $data[] = [
                $activity['description'],
                $activity['cash_in'] > 0 ? number_format($activity['cash_in'], 2) : '',
                $activity['cash_out'] > 0 ? number_format($activity['cash_out'], 2) : '',
                number_format($activity['net_cash'], 2),
            ];
        }
        $data[] = ['Net Financing Cash', '', '', $this->data['formatted_totals']['net_financing_cash']];
        $data[] = ['', '', '', ''];
        $data[] = ['Net Cash Flow', '', '', $this->data['formatted_totals']['net_cash_flow']];
        
        return $data;
    }

    private function getLoanPortfolioAgingData(): array
    {
        $data = [];
        
        $agingBuckets = [
            'current' => 'Current (0-30 days)',
            '30_days' => '31-60 days',
            '60_days' => '61-90 days',
            '90_days' => '91-120 days',
            'over_90_days' => 'Over 120 days',
        ];
        
        foreach ($agingBuckets as $bucket => $label) {
            if (isset($this->data[$bucket]) && count($this->data[$bucket]) > 0) {
                $totalOutstanding = collect($this->data[$bucket])->sum('outstanding_balance');
                $percentage = $totalOutstanding > 0 ? ($totalOutstanding / collect($this->data)->flatten()->sum('outstanding_balance')) * 100 : 0;
                
                $data[] = [
                    $label,
                    count($this->data[$bucket]),
                    number_format($totalOutstanding, 2),
                    number_format($percentage, 2) . '%',
                ];
            }
        }
        
        return $data;
    }

    private function getProvisioningData(): array
    {
        $data = [];
        
        foreach ($this->data['provisions'] as $bucket => $provision) {
            $data[] = [
                ucwords(str_replace('_', ' ', $bucket)),
                $provision['loan_count'],
                $provision['formatted_outstanding'],
                $provision['provision_rate'] . '%',
                $provision['formatted_provision'],
            ];
        }
        
        $data[] = ['', '', '', 'Total Provision', $this->data['formatted_total_provision']];
        
        return $data;
    }
}
