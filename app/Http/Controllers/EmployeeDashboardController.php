<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeeDashboardController extends Controller
{
    /**
     * لوحة تحكم الموظف الرئيسية
     */
    public function index()
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            abort(403, 'لم يتم ربط حسابك بموظف');
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // إحصائيات الموظف
        $stats = [
            'salary' => $employee->salary ?? 0,
            'years_of_service' => $employee->years_of_service,
            'total_salaries_paid' => $employee->total_salaries_paid,
            'total_advances' => $employee->total_advances,
            'balance' => $employee->balance,
            'monthly_salary' => $employee->transactions()
                ->where('type', EmployeeTransaction::TYPE_SALARY)
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'yearly_salary' => $employee->transactions()
                ->where('type', EmployeeTransaction::TYPE_SALARY)
                ->whereBetween('transaction_date', [$startOfYear, Carbon::now()])
                ->sum('amount'),
            'pending_advances' => $employee->transactions()
                ->where('type', EmployeeTransaction::TYPE_ADVANCE)
                ->sum('amount'),
        ];

        // آخر المعاملات
        $recentTransactions = $employee->transactions()
            ->with('creator')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        // إحصائيات المعاملات حسب النوع
        $transactionsByType = [
            'salaries' => $employee->transactions()->salaries()->count(),
            'advances' => $employee->transactions()->advances()->count(),
            'bonuses' => $employee->transactions()->where('type', 'bonus')->count(),
            'deductions' => $employee->transactions()->where('type', 'deduction')->count(),
        ];

        return view('employee-dashboard.index', compact(
            'employee',
            'stats',
            'recentTransactions',
            'transactionsByType'
        ));
    }

    /**
     * عرض كشف حساب الموظف
     */
    public function statement(Request $request)
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            abort(403, 'لم يتم ربط حسابك بموظف');
        }

        $query = $employee->transactions()->with('creator');

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // فلترة حسب الشهر
        if ($request->filled('month_year')) {
            $query->where('month_year', $request->month_year);
        }

        $transactions = $query->latest('transaction_date')->paginate(20)->withQueryString();

        // إجماليات
        $totals = [
            'salaries' => (clone $query)->where('type', 'salary')->sum('amount'),
            'advances' => (clone $query)->where('type', 'advance')->sum('amount'),
            'bonuses' => (clone $query)->where('type', 'bonus')->sum('amount'),
            'deductions' => (clone $query)->where('type', 'deduction')->sum('amount'),
        ];

        return view('employee-dashboard.statement', compact('employee', 'transactions', 'totals'));
    }

    /**
     * عرض تقارير الموظف
     */
    public function reports(Request $request)
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            abort(403, 'لم يتم ربط حسابك بموظف');
        }

        $year = $request->get('year', Carbon::now()->year);

        // تقرير شهري
        $monthlyData = collect();
        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($year, $i, 1)->startOfMonth();
            $end = Carbon::create($year, $i, 1)->endOfMonth();

            $monthlyData->push([
                'month' => $i,
                'month_name' => $start->translatedFormat('F'),
                'salary' => $employee->transactions()
                    ->where('type', 'salary')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
                'advances' => $employee->transactions()
                    ->where('type', 'advance')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
                'bonuses' => $employee->transactions()
                    ->where('type', 'bonus')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
                'deductions' => $employee->transactions()
                    ->where('type', 'deduction')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
            ]);
        }

        // إجماليات السنة
        $yearlyTotals = [
            'salary' => $monthlyData->sum('salary'),
            'advances' => $monthlyData->sum('advances'),
            'bonuses' => $monthlyData->sum('bonuses'),
            'deductions' => $monthlyData->sum('deductions'),
        ];

        return view('employee-dashboard.reports', compact('employee', 'year', 'monthlyData', 'yearlyTotals'));
    }

    /**
     * عرض الملف الشخصي
     */
    public function profile()
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            abort(403, 'لم يتم ربط حسابك بموظف');
        }

        $employee->load('branch');

        return view('employee-dashboard.profile', compact('employee'));
    }

    /**
     * الحصول على الموظف الحالي
     */
    protected function getEmployee(): ?Employee
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return $user->employee;
    }
}
