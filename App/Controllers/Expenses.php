<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Expense;
use \App\Models\PaymentMethod;
use \App\Auth;

/**
 * Expense controller
 *
 * PHP version 7.4
 */
class Expenses extends Authenticated
{
	/**
	* Before filter - called before each action method
	*/
	protected function before()
	{
		parent::before();
		
		$this->user = Auth::getUser();
	}
	
    /**
     * Show the expense page
     *
     * @return void
     */
    public function newAction()
    {
        $expenseCategories = Expense::findExpensesByUserID();
		$paymentMethods = PaymentMethod::findPaymentMethodsByUserId();
		
		View::renderTemplate('Expenses/new.html', [
		'expenseCategories' => $expenseCategories,
		'paymentMethods' => $paymentMethods
		]);
    }

    /**
     * Add a new expense
     *
     * @return void
     */
    public function createAction()
    {
        $expense = new Expense($_POST);

        if ($expense->save($_POST['expenseCategories'], $_POST['paymentMethods'])) {

            $this->redirect('/expenses/success');

        } else {

            View::renderTemplate('Expenses/new.html', [
                'expense' => $expense
            ]);

        }
    }

    /**
     * Show the expense success page
     *
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Expenses/success.html');
    }
	
	 /**
     * Show Ajax Limit Info
     *
     * @return void
     */
    public function ajaxInfoAction()
    {
        $expenseCategory = $_POST['expenseCategory'];
		$expenseId = Expense::findExpenseID($expenseCategory);
		$getLimit = Expense::getLimit($expenseId);
		
		if($getLimit > 0) {
		
			$amount = $_POST['correctAmount'];
			$dateExpense = $_POST['dateExpense'];
			
			$startDate = date('Y-m-01', strtotime($dateExpense));
			$endDate = date('Y-m-t', strtotime($dateExpense));

			$sumOfExpense = Expense::sumOfExpenseCategoryAssignedToUser($expenseId, $startDate, $endDate);
			
			$sumOfExpenseAndAmount = $sumOfExpense + $amount;
			
			$result = $getLimit - $sumOfExpenseAndAmount;
			
			if($getLimit < $sumOfExpenseAndAmount) {
				
				echo '<p style="background: #fff3d4; color: #ff0d0d;">
				Dla danej kategorii określony jest miesięczny limit w wysokości: <b>'.number_format($getLimit, 2,'.',' ').'</b> PLN. <br>Dotychczas wydano: <b>'.number_format($sumOfExpense, 2,'.',' ').'</b> PLN. <br>Dotychczas wydano + wpisana kwota wynosi: <b>'.number_format($sumOfExpenseAndAmount, 2,'.',' ').'</b> PLN. <br>Limit przekroczony o: <b>'.number_format(($getLimit-$sumOfExpenseAndAmount), 2,'.',' ').' </b>PLN.
				</span>';
				
			} else {
				
				echo '<p style="background: #fff3d4;">
				Dla danej kategorii określony jest miesięczny limit w wysokości: <b>'.number_format($getLimit, 2,'.',' ').'</b> PLN. <br>Dotychczas wydano: <b>'.number_format($sumOfExpense, 2,'.',' ').'</b> PLN. <br>Dotychczas wydano + wpisana kwota wynosi: <b>'.number_format($sumOfExpenseAndAmount, 2,'.',' ').'</b> PLN. <br>Do wydania w danej kategorii zostało jeszcze: <b>'.number_format(($getLimit-$sumOfExpenseAndAmount), 2,'.',' ').'</b> PLN.
				</p>';
				
			}
		}
    }

}
