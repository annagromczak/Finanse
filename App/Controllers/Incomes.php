<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Income;
use \App\Auth;

/**
 * Income controller
 *
 * PHP version 7.4
 */
class Incomes extends Authenticated
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
     * Show the income page
     *
     * @return void
     */
    public function newAction()
    {
        $incomeCategories = Income::findIncomesByUserID();
		
		View::renderTemplate('Incomes/new.html', [
		'incomeCategories' => $incomeCategories
		]);
    }

    /**
     * Add a new income
     *
     * @return void
     */
    public function createAction()
    {
        $income = new Income($_POST);

        if ($income->save($_POST['incomeCategories'])) {

            $this->redirect('/incomes/success');

        } else {

            View::renderTemplate('Incomes/new.html', [
                'income' => $income
            ]);

        }
    }

    /**
     * Show the income success page
     *
     * @return void
     */
    public function successAction()
    {
		View::renderTemplate('Incomes/success.html');
    }

}
