<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Models\PaymentMethod;
use \App\Auth;

/**
 * Signup controller
 *
 * PHP version 7.4
 */
class Signup extends \Core\Controller
{

    /**
     * Show the signup page
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }

    /**
     * Sign up a new user
     *
     * @return void
     */
    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
			
			session_regenerate_id(true);

			$_SESSION['user_id'] = User::getIdSavedUser($_POST['email']);
			
			Income::saveDefaultIncomes($_SESSION['user_id']);
			Expense::saveDefaultExpenses($_SESSION['user_id']);
			PaymentMethod::saveDefaultPaymentMethods($_SESSION['user_id']);
			
			$user->sendActivationEmail();

            $this->redirect('/signup/success');

        } else {

            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);

        }
    }

    /**
     * Show the signup success page
     *
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Signup/success.html');
    }

    /**
     * Activate a new account
     *
     * @return void
     */
    public function activateAction()
    {
		User::activate($this->route_params['token']);
		
		$this->user = Auth::getUser();

        $this->redirect('/signup/activated');
    }

    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
	
}
