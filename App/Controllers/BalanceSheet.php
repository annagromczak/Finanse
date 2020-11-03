<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\Income;
use \App\Models\Expense;

/**
 * Balance sheet controller
 *
 * PHP version 7.4
 */
class BalanceSheet extends Authenticated
{
	/**
     * Before filter - called before each action method
     *
     * @return void
     */
    protected function before()
    {
        parent::before();

        $this->user = Auth::getUser();
    }
	
	 /**
     * The balance sheet page of the current month
     *
     * @return void
     */
    public function showCurrentMonthAction()
    {
		$startDate = date('Y-m-01');
		$endDate = date('Y-m-d');
		
		$period = ' - bieżący miesiąc';
		
		static::show($startDate, $endDate, $period);
    }
	
	/**
     * The balance sheet page of the previous month
     *
     * @return void
     */
    public function showPreviousMonthAction()
    {
		$startDate = date('Y-m-d', strtotime("first day of last month"));
		$endDate = date('Y-m-d', strtotime("last day of last month"));
		
		$period = ' - poprzedni miesiąc';
		
		static::show($startDate, $endDate, $period);
    }
	
	/**
     * The balance sheet page of the current year
     *
     * @return void
     */
    public function showCurrentYearAction()
    {
		$startDate = date('Y-01-01');
		$endDate = date('Y-m-d');
		
		$period = ' - bieżący rok';
		
		static::show($startDate, $endDate, $period);
    }
	
	/**
     * The balance sheet page of selected period
     *
     * @return void
     */
    public function showNonStandardAction()
    {
		$startDate = $_POST['startDate'];
		$endDate = $_POST['endDate'];
		
		$period = 'od '.$startDate.' do '.$endDate.'';
		
		static::show($startDate, $endDate, $period);
    }
	
	/**
	* Show the balance sheet page
     *
     * @return void
	*/
	 public function show($startDate, $endDate, $period)
    {
		static::showIncomes($startDate, $endDate);
		static::showExpenses($startDate, $endDate);
		
		$this->temporaryInocmesSum = 0;
		$this->temporaryInocmesSum = Income::temporaryIncomesSum();
		$this->temporaryExpensesSum = 0;
		$this->temporaryExpensesSum = Expense::temporaryExpensesSum();
		
		$summary = static::showBalance($this->temporaryInocmesSum, $this->temporaryExpensesSum);
		
		View::renderTemplate('BalanceSheet/show.html', [
			'expense' => $this->expense,
			'income' => $this->income,
			'temporaryIncomesSum' => $this->temporaryInocmesSum,
			'temporaryExpensesSum' => $this->temporaryExpensesSum,
			'period' => $period, 
			'summary' => $summary
			]);
    }
	
	/**
	* Show temporary sum of incomes assigned to user
	*
	* @return the Income object
	*/
	public function showIncomes($startDate, $endDate)
	{
		$incomesId = [];
		$incomesId = Income::findIncomesIDAssignedToUser();
		
		foreach ($incomesId as $incomeId) 
		{
			$temporarySum = 0;
			
			$temporarySum=Income::sumOfIncomeCategoryAssignedToUser($incomeId, $startDate, $endDate);
		
			Income::setTemporarySum($incomeId, $temporarySum);
			$this->income = Income::findIncomeIfSumGreaterThanZero();
		}
		return $this->income;
	}
	
	/**
	* Show temporary sum of expenses assigned to user
	*
	* @return the Expense object
	*/
	public function showExpenses($startDate, $endDate)
	{
		$expensesId = [];
		$expensesId = Expense::findExpensesIDAssignedToUser();
		
		foreach ($expensesId as $expenseId) 
		{
			$temporarySum = 0;
			
			$temporarySum=Expense::sumOfExpenseCategoryAssignedToUser($expenseId, $startDate, $endDate);
		
			Expense::setTemporarySum($expenseId, $temporarySum);
			$this->expense = Expense::findExpenseIfSumGreaterThanZero();
		}
		return $this->expense;
	}
	
	/**
	* Show the summary of balance sheet
	*
	* @return string
	*/
	public function showBalance($temporaryInocmesSum, $temporaryExpensesSum)
	{
		$balance = $this->temporaryInocmesSum - $this->temporaryExpensesSum;
		
		$summary = '';
		
		if ($balance > 0) {
			$summary = 'Saldo wynosi: '.number_format($balance, 2,'.',' ').' PLN. Gratulacje! Świetnie zarządzasz finansami.';
		} else if ($balance === 0) {
			$summary = 'Saldo wynosi: '.number_format($balance, 2,'.',' ').' PLN.';
		} else if ($balance < 0) {
			$summary = 'Saldo wynosi: '.number_format($balance, 2,'.',' ').' PLN. Uważaj, wpadasz w długi!';
		}
		return $summary;
	}
}
