<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Models\PaymentMethod;

/**
 * Profile controller
 *
 * PHP version 7.4
 */
class Profile extends Authenticated
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
     * Show the profile
     *
     * @return void
     */
    public function showAction()
    {
        View::renderTemplate('Profile/show.html', [
            'user' => $this->user
        ]);
    }

    /**
     * Show the form for editing the profile
     *
     * @return void
     */
    public function editAction()
    {
        View::renderTemplate('Profile/edit.html', [
            'user' => $this->user
        ]);
    }

    /**
     * Update the profile
     *
     * @return void
     */
    public function updateAction()
    {
        if ($this->user->updateProfile($_POST)) {

            Flash::addMessage('Zmiany zostały zapisane');

            $this->redirect('/profile/show');

        } else {

            View::renderTemplate('Profile/edit.html', [
                'user' => $this->user
            ]);

        }
    }
	
	/**
     * Show categories of incomes
     *
     * @return void
     */
    public function categoriesOfIncomesAction()
    {
		static::findIncomes();
		
		View::renderTemplate('Profile/categories-of-incomes.html', [
			'income' => $this->income
        ]);
    }
	
	/**
     * Find categories of incomes by user
     *
     * @return void
     */
	 public function findIncomes()
    {
			return $this->income = Income::findIncomesByUserID();
	}
	
	/**
     * Add a new category of incomes
     *
     * @return void
     */
	 public function createIncomeAction()
    {
		$income = new Income($_POST);
		
		$name = ucfirst(strtolower($_POST['name']));
		
		if (Income::isIncomeExist($name) == FALSE) {
		
			if ($income->saveNewIncome($name)) {

				Flash::addMessage('Zamiany zostały zapisane');
				
				$this->redirect('/profile/categories-of-incomes');

			} else {

				Flash::addMessage('Nie udało się zapisać zmian, spróbuj jeszcze raz.', Flash::WARNING);
				
				$this->redirect('/profile/categories-of-incomes');

			}
		} else {
			
			Flash::addMessage('Kategoria o podanej nazwie już istnieje.', Flash::WARNING);
				
			$this->redirect('/profile/categories-of-incomes');

		}
	}	
	
	/**
     * Show categories of expenses
     *
     * @return void
     */
    public function categoriesOfExpensesAction()
    {
		static::findExpenses();
		
		View::renderTemplate('Profile/categories-of-expenses.html', [
			'expense' => $this->expense
        ]);
    }
	
	/**
     * Find categories of expenses by user
     *
     * @return void
     */
	 public function findExpenses()
    {
			return $this->expense = Expense::findExpensesByUserID();
	}
	
	/**
     * Add a new category of expenses
     *
     * @return void
     */
	 public function createExpenseAction()
    {
		$expense = new Expense($_POST);
		
		$name = ucfirst(strtolower($_POST['name']));
		
		if (Expense::isExpenseExist($name) == FALSE) {

			if ($expense->saveNewExpense($name)) {
				
				$expenseID = Expense::findExpenseID($_POST['name']);
				
				if(!empty($_POST['limit'])) $expense->updateLimit($expenseID, $_POST['limit']);

			   Flash::addMessage('Kategoria wydatków została dodana');
			   
			   $this->redirect('/profile/categories-of-expenses');

			} else {

				Flash::addMessage('Nie udało się zapisać zmian, spróbuj jeszcze raz.', Flash::WARNING);
				
				$this->redirect('/profile/categories-of-expenses');

			}
			
		} else {
			
			Flash::addMessage('Kategoria o podanej nazwie już istnieje.', Flash::WARNING);
				
			$this->redirect('/profile/categories-of-expenses');
			
		}
	}
	
	/**
     * Show payment methods
     *
     * @return void
     */
    public function paymentMethodsAction()
    {
		static::findPaymentMethods();
		
		View::renderTemplate('Profile/payment-methods.html', [
			'paymentMethod' => $this->paymentMethod
        ]);
    }
	
	/**
     * Find payment methods by user
     *
     * @return void
     */
	 public function findPaymentMethods()
    {
			return $this->paymentMethod = PaymentMethod::findPaymentMethodsByUserID();
	}
	
	/**
     * Add a new payment method
     *
     * @return void
     */
	 public function createPaymentMethodAction()
    {
		$paymentMethod = new PaymentMethod($_POST);
		
		$name = ucfirst(strtolower($_POST['name']));

        if (PaymentMethod::isMethodExist($name) == TRUE) {
			
			$id = PaymentMethod::getPaymentMethodID($name);
			
			PaymentMethod::activeMethod($id);

        } else {

            $paymentMethod->saveNewPaymentMethod($name);

        }
		
		Flash::addMessage('Metoda płatności została dodana');
			
		$this->redirect('/profile/payment-methods');
	}
	
	/**
     * Update the category of income
     *
     * @return void
     */
    public function updateIncomeAction()
    {
		$income = new Income($_POST);
		
		$incomeID = Income::findIncomeID($_POST['oldName']);
		
		$oldName = ucfirst(strtolower($_POST['oldName']));
		
		$newName = ucfirst(strtolower($_POST['name']));
		
		if (Income::isIncomeExist($newName) == FALSE) {
			
			if ($oldName != $newName) {
					
				$income->updateIncome($incomeID, $newName);
					
				Flash::addMessage('Zamiany zostały zapisane.');
				
				$this->redirect('/profile/categories-of-incomes');

			} else {

				Flash::addMessage('Nie udało się zapisać zmian, spróbuj jeszcze raz.', Flash::WARNING);
				
				$this->redirect('/profile/categories-of-incomes');

			}
			
		} else {
				
			Flash::addMessage('Kategoria o podanej nazwie już istnieje.', Flash::WARNING);
					
			$this->redirect('/profile/categories-of-incomes');
			
		}
    }
	
	/**
     * Update the category of expense
     *
     * @return void
     */
    public function updateExpenseAction()
    {
		$expense = new Expense($_POST);
		
		$expenseID = Expense::findExpenseID($_POST['oldName']);
		
		$oldName = ucfirst(strtolower($_POST['oldName']));
		
		$newName = ucfirst(strtolower($_POST['name']));
		
		if (Expense::isExpenseExist($newName) == FALSE) {
			
			if ($oldName == $newName) {
				
				if (!empty($_POST['limit'])) $expense->updateLimit($expenseID, $_POST['limit']);
				
				Flash::addMessage('Zamiany zostały zapisane.');
				
				$this->redirect('/profile/categories-of-expenses');
					
			} elseif ($oldName != $newName) {
					
				$expense->updateExpense($expenseID, $newName);
				
				if(!empty($_POST['limit'])) $expense->updateLimit($expenseID, $_POST['limit']);
					
				Flash::addMessage('Zamiany zostały zapisane.');
				
				$this->redirect('/profile/categories-of-expenses');

			} else {

				Flash::addMessage('Nie udało się zapisać zmian, spróbuj jeszcze raz.', Flash::WARNING);
				
				$this->redirect('/profile/categories-of-expenses');

			}
			
		} else {
			
			if (!empty($_POST['limit']) OR ($_POST['limit'] == 0)) {
				
				$expense->updateLimit($expenseID, $_POST['limit']);
				
				Flash::addMessage('Zamiany zostały zapisane.');
				
				$this->redirect('/profile/categories-of-expenses');
				
			} else {
			
				Flash::addMessage('Kategoria o podanej nazwie już istnieje.', Flash::WARNING);
					
				$this->redirect('/profile/categories-of-expenses');
			
			}
		}
    }	
	
	/**
     * Update the payment method
     *
     * @return void
     */
    public function updatePaymentMethodAction()
    {
		$paymentMethod = new PaymentMethod($_POST);
		
		$paymentMethodID = PaymentMethod::getPaymentMethodID($_POST['oldName']);
		
		$newName = ucfirst(strtolower($_POST['name']));
		
		if ($paymentMethod->updatePaymentMethod($paymentMethodID, $newName)) {

            Flash::addMessage('Zamiany zostały zapisane.');
			
			$this->redirect('/profile/payment-methods');

        } else {

            Flash::addMessage('Nie udało się zapisać zmian, spróbuj jeszcze raz.', Flash::WARNING);
			
			View::renderTemplate('Profile/payment-methods.html');

        }
    }
	
	/**
     * Delete the category of income
     *
     * @return void
     */
    public function deleteIncomeAction()
    {
		$oldID = Income::findIncomeID($_POST['oldName']);
		
		if (Income::isRecordExist($oldID) == FALSE) {
			
			Income::deleteIncome($oldID);
			
			Flash::addMessage('Kategoria przychodu została usunięta.');
			
			$this->redirect('/profile/categories-of-incomes'); 
			
		} else {
			
			Flash::addMessage('Nie można usunąć kategorii przychodu, ponieważ istnieją pozycje przypisane do tej kategorii.', Flash::WARNING);
			
			$this->redirect('/profile/categories-of-incomes'); 
			
		}		
    }

	/**
     * Delete the category of expense
     *
     * @return void
     */
    public function deleteExpenseAction()
    {
		$oldID = Expense::findExpenseID($_POST['oldName']);
		
		if (Expense::isRecordExist($oldID) == FALSE) {
			
			Expense::deleteExpense($oldID);
			
			Flash::addMessage('Kategoria wydatku została usunięta.');
			
			$this->redirect('/profile/categories-of-expenses'); 
			
		} else {
			
			Flash::addMessage('Nie można usunąć kategorii wydatku, ponieważ istnieją pozycje przypisane do tej kategorii.', Flash::WARNING);
			
			$this->redirect('/profile/categories-of-expenses'); 
			
		}		
    }

	
	/**
     * Delete the payment method
     *
     * @return void
     */
    public function deletePaymentMethodAction()
    {
		$oldID = PaymentMethod::getPaymentMethodID($_POST['oldName']);
		
		PaymentMethod::inactiveMethod($oldID);
		
		Flash::addMessage('Metoda płatności została usunięta.');
		
		$this->redirect('/profile/payment-methods'); 
			
    }

	
}
