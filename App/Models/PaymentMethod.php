<?php

namespace App\Models;

use PDO;

/**
 *Payment Method model
 *
 * PHP version 7.4
 */
class PaymentMethod extends \Core\Model
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
     * Save user's default payment methods
     *
     * @return void
     */
	 public static function saveDefaultPaymentMethods($userId)
	 {
		$sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name) SELECT :user_id, name FROM payment_methods_default';

		$db = static::getDB();
		$stmt = $db->prepare($sql);

		$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

		$stmt->execute();
	 }
	 
	/**
     * Find the payment method ID
     *
     * @return payment method ID
     */
    public static function getPaymentMethodID($paymentMethodName)
    {
        $sql = 'SELECT id FROM payment_methods_assigned_to_users WHERE name=:name AND user_id=:user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
		
        $stmt->bindValue(':name', $paymentMethodName, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		return $result[0];
    }
	
	/**
     * Find the payment method model by user ID
     *
     * @return the Expense object
     */
    public static function findPaymentMethodsByUserId()
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id=:user_id AND active=0';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }
	
	/**
     * Save a new payment method
     *
     * @return void
     */
	 public function saveNewPaymentMethod($name)
	 {
		$this->validatePaymentMethod();
		
		 if (empty($this->errors)) {
			
			$sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name) VALUES (:user_id, :name)';

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
    public function validatePaymentMethod()
    {
		//Name
		if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
    }
	
	 /**
     * Update the payment method
     *
     * @param array $data Data from the edit modal form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updatePaymentMethod($id, $name)
    {

        $this->validatePaymentMethod();

        if (empty($this->errors)) {

            $sql = 'UPDATE payment_methods_assigned_to_users SET name = :name WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }
	
	/**
	* Check if the metod exist
	*
	* @return PaymentMethodId if found, false otherwise
	*/
	public static function isMethodExist($name)
	{
		$sql = 'SELECT id FROM payment_methods_assigned_to_users WHERE user_id=:user_id AND name=:name';

		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);

		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

		$stmt->execute();
		
		$paymentMethod = $stmt->fetch();
		
		if (empty($paymentMethod)) {
			
			return false;
		}
		
		return $paymentMethod->id;
	}
	 
	 /**
     * Change the payment method to inactive
     *
     * @return void
     */
	 public static function inactiveMethod($oldID)
	 {
		$sql = 'UPDATE payment_methods_assigned_to_users SET active=1 WHERE id = :id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);

		$stmt->bindValue(':id', $oldID, PDO::PARAM_INT);

         $stmt->execute();
	 }
	 
	 /**
     * Change the payment method to active
     *
     * @return void
     */
	 public static function activeMethod($oldID)
	 {
		$sql = 'UPDATE payment_methods_assigned_to_users SET active=0 WHERE id = :id';

		$db = static::getDB();
		$stmt = $db->prepare($sql);

		$stmt->bindValue(':id', $oldID, PDO::PARAM_INT);

         $stmt->execute();
	 }
}
