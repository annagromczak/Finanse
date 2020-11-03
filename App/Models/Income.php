<?php

namespace App\Models;

use PDO;

/**
 * Income model
 *
 * PHP version 7.4
 */
class Income extends \Core\Model
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
     * Save user's default incomes category
     *
     * @return void
     */
	 public static function saveDefaultIncomes($userId)
	 {   

			$sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name) SELECT :user_id, name FROM incomes_category_default';

			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

			$stmt->execute();
			
	 }

    /**
     * Save the income model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save($incomeCategory)
    {
		
        $this->validate();
		$incomeId = static::findIncomeID($incomeCategory);

        if (empty($this->errors)) {
			
			$sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment) VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':income_category_assigned_to_user_id', $incomeId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount);
            $stmt->bindValue(':date_of_income', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);

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
		if ($this->incomeCategories == '') {
            $this->incomeCategories[] = 'Category is required';
        }
    }
	
	/**
     * Find the income ID
     *
     * @return ID
     */
    public static function findIncomeID($name)
    {
        $sql = 'SELECT id FROM incomes_category_assigned_to_users WHERE name=:name AND user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
        return $result[0];
    }
	
	/**
	* Calculation sum of the income category assigned to user
	*
	* @return sum
	*/
	public static function sumOfIncomeCategoryAssignedToUser($incomeId, $startDate, $endDate)
	{
		$sql = 'SELECT SUM(amount) FROM incomes WHERE user_id=:user_id AND income_category_assigned_to_user_id=:income_category_assigned_to_user_id AND date_of_income>=:startDate AND date_of_income<=:endDate';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':income_category_assigned_to_user_id', $incomeId, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
		
		$stmt->execute();

        $temporarySumResult = $stmt->fetch(PDO::FETCH_NUM);
		
        return $temporarySumResult[0];
	}
	
	/**
	* Set sum of the income category assigned to user in database as temporary_sum
	*
	* @return void
	*/
	public static function setTemporarySum($incomeId, $temporarySum)
	{	
		$sql = 'UPDATE incomes_category_assigned_to_users SET temporary_sum=:temporary_sum WHERE id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':temporary_sum', $temporarySum);
        $stmt->bindValue(':id', $incomeId, PDO::PARAM_INT);

		$stmt->execute();
	}
	
	/**
	* Find all of incomes ID assigned to user
	*
	*  @var array
	*/
	public static function findIncomesIDAssignedToUser()
	{
		$sql = 'SELECT id FROM incomes_category_assigned_to_users WHERE user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->execute();

       $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		
		return $result;
	}
	
	 /**
     * Find the income model by ID if the temporary sum is greater than zero
     *
     * @return the Income object
     */
    public static function findIncomeIfSumGreaterThanZero()
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id=:user_id AND temporary_sum>0';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }
	
	/**
     * Find the income model by ID
     *
     * @return the Income object
     */
    public static function findByID($incomeId)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $incomeId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }

	/**
     * Calculation sum of user's incomes
     *
     * @return sum
     */
    public static function temporaryIncomesSum()
    {
        $sql = 'SELECT SUM(temporary_sum) FROM incomes_category_assigned_to_users WHERE user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

		$stmt->execute();

        $temporarySumResult = $stmt->fetch();
		
        return $temporarySumResult[0];
    }
	
	/**
     * Find the income model by user ID
     *
     * @return the Expense object
     */
    public static function findIncomesByUserID()
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE  user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }
	
	/**
     * Save a new category of income
     *
     * @return void
     */
	 public function saveNewIncome($name)
	 {
		$this->validateNewIncome();
		
		 if (empty($this->errors)) {
			
			$sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name) VALUES (:user_id, :name)';

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
    public function validateNewIncome()
    {
		//Name
		if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
    }
	
	/**
     * Update the category of income
     *
     * @param array $data Data from the edit modal form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateIncome($id, $newName)
    {
        $this->validateNewIncome();

        if (empty($this->errors)) {

            $sql = 'UPDATE incomes_category_assigned_to_users SET name = :name WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $newName, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }
	
	/**
	* Check if the records of income exist
	*
	* @return mixed income object if found, false otherwise
	*/
	public static function isRecordExist($incomeId)
	{
		$sql = 'SELECT * FROM incomes WHERE user_id=:user_id AND income_category_assigned_to_user_id=:income_category_assigned_to_user_id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':income_category_assigned_to_user_id', $incomeId, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();
		
		$income = $stmt->fetch();
		
		if (empty($income)) {
			
			return false;
		}
		
		return $income->id;
	}
	
	/**
     * Delete the category of income
     *
     * @return void
     */
	 public static function deleteIncome($oldID)
	 {
		$sql = 'DELETE FROM incomes_category_assigned_to_users WHERE user_id=:user_id AND id=:id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':id', $oldID, PDO::PARAM_INT);

         $stmt->execute();
	 }

	/**
	* Check if the name of income exist
	*
	* @return PaymentMethodId if found, false otherwise
	*/
	public static function isIncomeExist($name)
	{
		$sql = 'SELECT id FROM incomes_category_assigned_to_users WHERE user_id=:user_id AND name=:name';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();
		
		$income = $stmt->fetch();
		
		if (empty($income)) {
			
			return false;
		}
		
		return true;
	}
	
	
}
