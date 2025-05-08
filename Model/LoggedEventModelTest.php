<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;

/**
 * Test LoggedEvent Model... because this model is read only we need to
 * be creative to test it. Therefore we will create Logged event using
 * the underlying connection then the model to read them before using
 * the underlying connection again to delete them.
 */
final class LoggedEventModelTest extends TestCase {
	/**
	 * A location that exists in the db
	 */
	private static Location $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static EquipmentType $type;

	/**
	 * The equipment connected to a portalbox
	 */
	private static Equipment $equipment;

	/**
	 * A card we can use as test data
	 */
	private static ShutdownCard $card;

	/**
	 * The configuration
	 */
	private static Config $config;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision a location in the db
		$model = new LocationModel(self::$config);

		$name = 'Robotics Shop';

		self::$location = $model->create(
			(new Location())
				->set_name($name)
		);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

		$name = 'Floodlight';
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		self::$type = $model->create(
			(new EquipmentType())
				->set_name($name)
				->set_requires_training($requires_training)
				->set_charge_policy_id($charge_policy_id)
				->set_allow_proxy(false)
		);

		// provision a piece of equipment in the db
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789ab';
		$timeout = 0;
		$is_in_service = TRUE;
		$service_minutes = 500;

		self::$equipment = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address)
				->set_timeout($timeout)
				->set_is_in_service($is_in_service)
				->set_service_minutes($service_minutes)
		);

		$model = new CardModel(self::$config);

		$card_id = 9812347165;

		self::$card = $model->create(
			(new ShutdownCard())
				->set_id($card_id)
		);
	}

	public static function tearDownAfterClass() : void {
		$model = new CardModel(self::$config);
		$model->delete(self::$card->id());

		$model = new EquipmentModel(self::$config);
		$model->delete(self::$equipment->id());

		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());

		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		parent::tearDownAfterClass();
	}

	public function testRead(): void {
		$model = new LoggedEventModel(self::$config);

		// drop to SQL to create an event to read
		$connection = $model->configuration()->writable_db_connection();
		$sql = <<<EOQ
		INSERT INTO log
			(event_type_id, card_id, equipment_id, time)
		VALUES
			(:event_type_id, :card_id, :equipment_id, :time)
		EOQ;
		$statement = $connection->prepare($sql);

		$statement->bindValue(':event_type_id', LoggedEventType::PLANNED_SHUTDOWN);
		$statement->bindValue(':card_id', self::$card->id());
		$statement->bindValue(':equipment_id', self::$equipment->id());
		$statement->bindValue(':time', '2025-02-28 13:55:42');

		self::assertTrue($statement->execute());
		$log_id = (int)$connection->lastInsertId('log_id_seq');

		$event = $model->read($log_id);

		self::assertNotNull($event);

		$statement = $connection->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$log_id]));
	}
}