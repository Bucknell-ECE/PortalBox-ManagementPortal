<?php

namespace Portalbox\Model;

use Portalbox\Entity\Charge;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * ChargeModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class ChargeModel extends AbstractModel {
	/**
	 * Save a new Charge to the database
	 *
	 * @param Charge charge - the charge to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Charge|null - the charge or null if the charge could not be saved
	 */
	public function create(Charge $charge) : ?Charge {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO charges (user_id, equipment_id, time, amount, charge_policy_id, charge_rate, charged_time) VALUES (:user_id, :equipment_id, :time, :amount, :charge_policy_id, :charge_rate, :charged_time)';
		$query = $connection->prepare($sql);

		$query->bindValue(':user_id', $charge->user_id(), PDO::PARAM_INT);
		$query->bindValue(':equipment_id', $charge->equipment_id(), PDO::PARAM_INT);
		$query->bindValue(':time', $charge->time());
		$query->bindValue(':amount', $charge->amount());
		$query->bindValue(':charge_policy_id', $charge->charge_policy_id(), PDO::PARAM_INT);
		$query->bindValue(':charge_rate', $charge->charge_rate());
		$query->bindValue(':charged_time', $charge->charged_time());

		if($query->execute()) {
			return $charge->set_id($connection->lastInsertId('charges_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a charge by its unique ID
	 *
	 * @param int id - the unique id of the charge
	 * @throws DatabaseException - when the database can not be queried
	 * @return Charge|null - the charge or null if the charge could not be found
	 */
	public function read(int $id) : ?Charge {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, user_id, equipment_id, time, amount, charge_policy_id, charge_rate, charged_time FROM charges WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new Charge())
					->set_id($data['id'])
					->set_user_id($data['user_id'])
					->set_equipment_id($data['equipment_id'])
					->set_time($data['time'])
					->set_amount($data['amount'])
					->set_charge_policy_id($data['charge_policy_id'])
					->set_charge_rate($data['charge_rate'])
					->set_charged_time($data['charged_time']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified Charge to the database
	 *
	 * @param Charge charge - the charge to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Charge|null - the charge or null if the charge could not be saved
	 */
	public function update(Charge $charge) : ?Charge {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE charges SET user_id = :user_id, equipment_id = :equipment_id, time = :time, amount = :amount, charge_policy_id = :charge_policy_id, charge_rate = :charge_rate, charged_time = :charged_time WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $charge->id(), PDO::PARAM_INT);
		$query->bindValue(':user_id', $charge->user_id(), PDO::PARAM_INT);
		$query->bindValue(':equipment_id', $charge->equipment_id(), PDO::PARAM_INT);
		$query->bindValue(':time', $charge->time());
		$query->bindValue(':amount', $charge->amount());
		$query->bindValue(':charge_policy_id', $charge->charge_policy_id(), PDO::PARAM_INT);
		$query->bindValue(':charge_rate', $charge->charge_rate());
		$query->bindValue(':charged_time', $charge->charged_time());

		if($query->execute()) {
			return $charge;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a charge secified by its unique ID
	 *
	 * @param int id - the unique id of the charge
	 * @throws DatabaseException - when the database can not be queried
	 * @return Charge|null - the charge or null if the charge could not be found
	 */
	public function delete(int $id) : ?Charge {
		$charge = $this->read($id);

		if(NULL !== $charge) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM charges WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $charge;
	}
}