<?php

namespace App\Models;

use PDO;
use \App\Models\PaymentMethod;

/**
 *Expense model
 *
 * PHP version 7.4
 */
class Expense extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
	
	/**
     * Save user's default expenses category
     *
     * @return void
     */
	 public static function saveDefaultExpenses($userId)
	 {
			$sql = 'INSERT INTO expenses_category_assigned_to_users (user_id, name) SELECT :user_id, name FROM expenses_category_default';

			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

			$stmt->execute();
	 }

    /**
     * Save the expense model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save($expenseCategory, $paymentMethod)
    {
		
        $this->validate();
		$expenseId = static::findExpenseID($expenseCategory);
		$paymentMethodId = PaymentMethod::getPaymentMethodID($paymentMethod);

        if (empty($this->errors)) {
			
			$sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment) VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':expense_category_assigned_to_user_id', $expenseId, PDO::PARAM_INT);
            $stmt->bindValue(':payment_method_assigned_to_user_id', $paymentMethodId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount);
            $stmt->bindValue(':date_of_expense', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':expense_comment', $this->comment, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
		
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
		//Amount
		$this->amount = str_replace("," , ".", $this->amount);
		
		if (is_numeric($this->amount) == false)
		{
			$this->errors[] = 'Invalid amount';
		}
		
		if($this->amount < 0)
		{
			$this->errors[] = 'Invalid amount';
		}
		
		$this->amount = round($this->amount, 2);
		
		//Date
		$currentDate = date('Y-m-d');
		
		if($this->date > $currentDate)
		{
			$this->errors[] = 'Invalid date! The date cannot be later than the current date.';
		}
		
		//Comment
		if(strlen($this->comment)>70)
		{
			$this->errors[] = 'The comment can contain up to 70 characters!';
		}
		
		//Category
		if ($this->expenseCategories == '') {
            $this->expenseCategories[] = 'Category is required';
        }
		
		//Payment method
		if ($this->paymentMethods == '') {
            $this->paymentMethods[] = 'Category is required';
        }
    }
	
	/**
     * Find the expense ID
     *
     * @return ID
     */
    public static function findExpenseID($name)
    {
        $sql = 'SELECT id FROM expenses_category_assigned_to_users WHERE name=:name AND user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
        return $result[0];
    }
	
	/**
	* Calculation sum of the expense category assigned to user
	*
	* @return sum
	*/
	public static function sumOfExpenseCategoryAssignedToUser($expenseId, $startDate, $endDate)
	{
		$sql = 'SELECT SUM(amount) FROM expenses WHERE user_id=:user_id AND expense_category_assigned_to_user_id=:expense_category_assigned_to_user_id AND date_of_expense>=:startDate AND date_of_expense<=:endDate';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':expense_category_assigned_to_user_id', $expenseId, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
		
		$stmt->execute();

        $temporarySumResult = $stmt->fetch(PDO::FETCH_NUM);
		
		return $temporarySumResult[0];
	}
	
	/**
	* Set sum of the expense category assigned to user in database as temporary_sum
	*
	* @return void
	*/
	public static function setTemporarySum($expenseId, $temporarySum)
	{	
		$sql = 'UPDATE expenses_category_assigned_to_users SET temporary_sum=:temporary_sum WHERE id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':temporary_sum', $temporarySum);
        $stmt->bindValue(':id', $expenseId, PDO::PARAM_INT);

		$stmt->execute();
	}
	
	/**
	* Find all of expenses ID assigned to user
	*
	*  @var array
	*/
	public static function findExpensesIDAssignedToUser()
	{
		$sql = 'SELECT id FROM expenses_category_assigned_to_users WHERE user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->execute();

       $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		
		return $result;
	}
	
	 /**
     * Find the expense model by ID if the temporary sum is greater than zero
     *
     * @return the Expense object
     */
    public static function findExpenseIfSumGreaterThanZero()
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id=:user_id AND temporary_sum>0';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }
	
	/**
     * Find the expense model by expense ID
     *
     * @return the Expense object
     */
    public static function findByID($expenseId)
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $expenseId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }

	/**
     * Calculation sum of user's expenses
     *
     * @return sum
     */
    public static function temporaryExpensesSum()
    {
        $sql = 'SELECT SUM(temporary_sum) FROM expenses_category_assigned_to_users WHERE user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

		$stmt->execute();

        $temporarySumResult = $stmt->fetch();
		
        return $temporarySumResult[0];
    }
	
	/**
     * Find the expense model by user ID
     *
     * @return the Expense object
     */
    public static function findExpensesByUserID()
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE  user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }
	
	/**
     * Save a new category of expense
     *
     * @return void
     */
	 public function saveNewExpense($name)
	 {
		$this->validateExpenseName();
		
		 if (empty($this->errors)) {
			 				 
			$sql = 'INSERT INTO expenses_category_assigned_to_users (user_id, name) VALUES (:user_id, :name)';

			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
			$stmt->bindValue(':name', $name, PDO::PARAM_STR);

			return $stmt->execute();

        }

        return false;
	 }
	 
	 /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validateExpenseName()
    {
		//Name
		if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
    }
	
	public function validateExpenseLimit($limit)
    {
		//Limit
		$limit = str_replace("," , ".", $limit);
		
		if (is_numeric($limit) == false)
		{
			$this->errors[] = 'Invalid limit';
		}
		
		if($limit < 0)
		{
			$this->errors[] = 'Invalid limit';
		}
		
		$limit = round($limit, 2);
		
		return $limit;
    }
	
	/**
     * Update the category of expense
     *
     * @param array $data Data from the edit modal form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateExpense($id, $name)
    {
        $this->validateExpenseName();

        if (empty($this->errors)) {
			
			$sql = 'UPDATE expenses_category_assigned_to_users SET name=:name WHERE id=:id';

			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':name', $name, PDO::PARAM_STR);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);

			return $stmt->execute();
			
		}

        return false;
    }
	
	/**
     * Update limit of expense
     *
     * @param array $data Data from the edit modal form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateLimit($id, $limit)
    {
        $limit = $this->validateExpenseLimit($limit);

        if (empty($this->errors)) {
			
			$sql = 'UPDATE expenses_category_assigned_to_users SET expense_limit=:limit WHERE id=:id';

			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':limit', $limit);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);

			return $stmt->execute();
			
		}

        return false;
    }
	
	/**
	* Check if the records of expense exist
	*
	* @return mixed expense object if found, false otherwise
	*/
	public static function isRecordExist($expenseId)
	{
		$sql = 'SELECT * FROM expenses WHERE user_id=:user_id AND expense_category_assigned_to_user_id=:expense_category_assigned_to_user_id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':expense_category_assigned_to_user_id', $expenseId, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();
		
		$expense = $stmt->fetch();
		
		if (empty($expense)) {
			
			return false;
		}
		
		return $expense->id;
	}
	
	/**
     * Delete the category of expense
     *
     * @return void
     */
	 public static function deleteExpense($oldID)
	 {
		$sql = 'DELETE FROM expenses_category_assigned_to_users WHERE user_id=:user_id AND id=:id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':id', $oldID, PDO::PARAM_INT);

         $stmt->execute();
	 }

	/**
     * Get limit of expense
     *
     * @return limit
     */
    public static function getLimit($expenseId)
    {
        $sql = 'SELECT expense_limit FROM expenses_category_assigned_to_users WHERE id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
		$stmt->bindValue(':id', $expenseId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
        return $result[0];
    }
	
	/**
	* Check if the name of expense exist
	*
	* @return PaymentMethodId if found, false otherwise
	*/
	public static function isExpenseExist($name)
	{
		$sql = 'SELECT id FROM expenses_category_assigned_to_users WHERE user_id=:user_id AND name=:name';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();
		
		$expense = $stmt->fetch();
		
		if (empty($expense)) {
			
			return false;
		}
		
		return true;
	}
	
}
