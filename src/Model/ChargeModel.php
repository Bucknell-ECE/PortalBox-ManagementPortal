<?php

namespace Portalbox\Model;

use Portalbox\Entity\Charge;
use Portalbox\Exception\DatabaseException;
use Portalbox\Query\ChargeQuery;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Query\UserQuery;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\UserModel;

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
		$sql = 'SELECT c.id, c.user_id, u.name AS user_name, c.equipment_id, e.name AS equipment_name, c.time, c.amount, c.charge_policy_id, c.charge_rate, c.charged_time FROM charges AS c INNER JOIN equipment as e ON e.id = c.equipment_id INNER JOIN users AS u on u.id = c.user_id WHERE c.id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildChargeFromArray($data);
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

	/**
	 * Search for Charges
	 * 
	 * @param ChargeQuery query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return Charge[]|null - a list of charges which match the search query
	 */
	public function search(ChargeQuery $query) : ?array {
		if(NULL === $query) {
			// no query... bail
			return NULL;
		}
		

		$connection = $this->configuration()->readonly_db_connection();
		
		$sql = 'SELECT c.id, c.user_id, u.name AS user_name, c.equipment_id, e.name AS equipment_name, c.time, c.amount, c.charge_policy_id, c.charge_rate, c.charged_time FROM charges AS c INNER JOIN equipment as e ON e.id = c.equipment_id INNER JOIN users AS u on u.id = c.user_id';
		
		$where_clause_fragments = array();
		$parameters = array();
		
		if($query->equipment_id()) {
			$where_clause_elements[] = 'c.equipment_id = :equipment_id';
			$parameters[':equipment_id'] = $query->equipment_id();
		}
		if(NULL !== $query->user_id()) {
			$where_clause_fragments[] = 'c.user_id = :user_id';
			$parameters[':user_id'] = $query->user_id();
		}
		
		if($query->on_or_after()) {
			$where_clause_elements[] = 'c.time >= :after';
			$parameters[':after'] = $query->on_or_after();
		}
		if($query->on_or_before()) {
			$where_clause_elements[] = 'c.time <= :before';
			$parameters[':before'] = $query->on_or_before();
		}
		if(0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= join(' AND ', $where_clause_fragments);
		}
		$sql .= ' ORDER BY time DESC';

		$statement = $connection->prepare($sql);
		// run search
		foreach($parameters as $k => $v) {
			$statement->bindValue($k, $v);
		}
		

		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			
			if(FALSE !== $data) {
				
				return $this->buildChargesFromArrays($data);
				
			} else {
				
				return null;
			}
		} else {
			
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildChargeFromArray(array $data) : Charge {
		return (new Charge())
					->set_id($data['id'])
					->set_user_id($data['user_id'])
					->set_equipment_id($data['equipment_id'])
					->set_time($data['time'])
					->set_amount($data['amount'])
					->set_charge_policy_id($data['charge_policy_id'])
					->set_charge_rate($data['charge_rate'])
					->set_charged_time($data['charged_time']);
	}

	private function buildChargesFromArrays(array $data) : array {
		
		$charges = [];
		$machines = [];
		$users = [];

		$e_model = new EquipmentModel($this->Configuration());
		$u_model = new UserModel($this->Configuration());

		$e_query = new EquipmentQuery();
		$e_query->set_include_out_of_service(true);	
		$u_query = new UserQuery();
		$u_query->set_include_inactive(TRUE);

		$machines = $e_model->search($e_query);
		$users = $u_model->search($u_query);

		foreach($data as $datum) {
			$charges[] = $this->buildChargeFromArray($datum);
			// echo print_r($datum, true) . "<br>";
		}
		// echo count($charges). "<br>";
		// $x = 0;
		foreach($charges as $charge) {
			// echo $x . ":";
			// echo $charge->equipment_id() . "<br>";
			// $x += 1;
			$e_id = $charge->equipment_id();
			
			$machine = array_filter($machines,
				function ($e) use ($e_id) {
					return $e->id() == $e_id;
				});
				
			// echo array_pop($machine)->id();
			$charge->set_equipment(array_pop($machine));
			
			$u_id = $charge->user_id();

			$user = array_filter($users,
				function ($e) use ($u_id) {
					return $e->id() == $u_id;
				});
				
			$charge->set_user(array_pop($user));
		}
		
		return $charges;
	}
}