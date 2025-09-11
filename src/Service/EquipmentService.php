<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\CardType;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\ActivationModel;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\EquipmentQuery;

/**
 * Handle requests from Portal Boxes
 *
 * Unlike other services, this service does not use Cookie or API Keys to
 * authenticate. Instead methods require a card id that must be assigned to an
 * active user with the requisite permissions
 */
class EquipmentService {
	public const ERROR_NO_AUTHORIZATION_HEADER = 'No Authorization header provided.';
	public const ERROR_INVALID_AUTHORIZATION_HEADER = 'Improperly formatted Authorization header. Please use "Bearer " + token syntax.';
	
	public const ERROR_REGISTRATION_NOT_AUTHORIZED = 'You are not authorized to register a portalbox.';
	public const ERROR_DEVICE_ALREADY_REGISTERED = 'A device with the given MAC address already exists.';
	public const ERROR_INCOMPLETE_SETUP_NO_LOCATIONS = 'You must first setup a location.';

	public const ERROR_ACTIVATION_NOT_AUTHORIZED = 'You are not authorized to activate the specified portalbox.';

	public const ERROR_INVALID_STATUS_CHANGE_BODY = 'We did not recognize the requested device status change.';
	public const ERROR_SHUTDOWN_NOT_AUTHORIZED = 'You are not authorized to shutdown portalboxes.';
	public const ERROR_EQUIPMENT_NOT_FOUND = 'We have no record of that portalbox.';

	public const DEFAULT_DEVICE_NAME = 'Unassigned Portalbox';

	protected ActivationModel $activationModel;
	protected CardModel $cardModel;
	protected EquipmentModel $equipmentModel;
	protected EquipmentTypeModel $equipmentTypeModel;
	protected LocationModel $locationModel;
	protected LoggedEventModel $loggedEventModel;

	public function __construct(
		ActivationModel $activationModel,
		CardModel $cardModel,
		EquipmentModel $equipmentModel,
		EquipmentTypeModel $equipmentTypeModel,
		LocationModel $locationModel,
		LoggedEventModel $loggedEventModel
	) {
		$this->activationModel = $activationModel;
		$this->cardModel = $cardModel;
		$this->equipmentModel = $equipmentModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
		$this->locationModel = $locationModel;
		$this->loggedEventModel = $loggedEventModel;
	}

	/**
	 * Register a new portal box
	 *
	 * @param string $mac  the mac address of the portal box
	 * @param array $headers  the request headers
	 * @return Equipment  the portal box as registered with the system
	 * @throws AuthenticationException  if the request headers do not contain a
	 *      HTTP_AUTHORIZATION header which is a properly formatted Bearer token
	 *      when the token is the id of a user card
	 * @throws AuthorizationException  if the card id does not map to a user
	 *      card or the user mapped to be the card id does not have the
	 *      CREATE_EQUIPMENT permission
	 * @throws InvalidArgumentException  if the mac is already present in the
	 *      system
	 */
	public function register(string $mac, array $headers): Equipment {
		if(!array_key_exists('HTTP_AUTHORIZATION', $headers)) {
			throw new AuthenticationException(self::ERROR_NO_AUTHORIZATION_HEADER);
		}
		$header = $headers['HTTP_AUTHORIZATION'];

		if(strlen($header) < 8 || strcmp('Bearer ', substr($header, 0 , 7)) != 0) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card_id = filter_var(substr($header, 7), FILTER_VALIDATE_INT);
		if($card_id === false) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card = $this->cardModel->read($card_id);
		if ($card === null || $card->type_id() !== CardType::USER) {
			throw new AuthorizationException(self::ERROR_REGISTRATION_NOT_AUTHORIZED);
		}

		$authenticatedUser = $card->user();
		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_REGISTRATION_NOT_AUTHORIZED);
		}

		$query = (new EquipmentQuery())
			->set_exclude_out_of_service(true)
			->set_mac_address($mac);
		if (!empty($this->equipmentModel->search($query))) {
			throw new InvalidArgumentException(self::ERROR_DEVICE_ALREADY_REGISTERED);
		}

		$equipmentTypes = $this->equipmentTypeModel->search();

		$locations = $this->locationModel->search('id');
		if (empty($locations)) {
			throw new InvalidArgumentException(self::ERROR_INCOMPLETE_SETUP_NO_LOCATIONS);
		}

		return $this->equipmentModel->create(
			(new Equipment())
				->set_name(self::DEFAULT_DEVICE_NAME)
				->set_type($equipmentTypes[0])
				->set_location($locations[0])
				->set_mac_address($mac)
		);
	}

	/**
	 * Activate a portal box
	 *
	 * @param string $mac  the mac address of the portal box
	 * @param array $headers  the request headers
	 * @return Equipment  the portal box
	 * @throws AuthenticationException  if the request headers do not contain a
	 *      HTTP_AUTHORIZATION header which is a properly formatted Bearer token
	 *      when the token is the id of a user card
	 * @throws AuthorizationException  if the card id does not map to a user
	 *      card or the user mapped to be the card id does not have permission
	 *      to activate equipment of the type assigned to the portal box
	 */
	public function activate(string $mac, array $headers): Equipment {
		if(!array_key_exists('HTTP_AUTHORIZATION', $headers)) {
			throw new AuthenticationException(self::ERROR_NO_AUTHORIZATION_HEADER);
		}
		$header = $headers['HTTP_AUTHORIZATION'];

		if(strlen($header) < 8 || strcmp('Bearer ', substr($header, 0 , 7)) != 0) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card_id = filter_var(substr($header, 7), FILTER_VALIDATE_INT);
		if($card_id === false) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card = $this->cardModel->read($card_id);
		if ($card === null || $card->type_id() !== CardType::USER) {
			throw new AuthorizationException(self::ERROR_ACTIVATION_NOT_AUTHORIZED);
		}

		$query = (new EquipmentQuery())
			->set_exclude_out_of_service(true)
			->set_mac_address($mac);
		$equipment = $this->equipmentModel->search($query);
		if (empty($equipment)) {
			// in order to avoid leaking system details we throw an authorization
			// exception here where we would typically throw a not found
			// exception if we had completed authorization
			throw new AuthorizationException(self::ERROR_ACTIVATION_NOT_AUTHORIZED);
		}

		// get the first item in the list... in theory there should only be one
		// item in the list, but we can afford to be defensive
		$equipment = reset($equipment);

		if (!in_array($equipment->type_id(), $card->user()->authorizations())) {
			$this->loggedEventModel->create(
				(new LoggedEvent())
					->set_type_id(LoggedEventType::UNSUCCESSFUL_AUTHENTICATION)
					->set_card_id($card_id)
					->set_equipment_id($equipment->id())
					->set_time(date('Y-m-d H:i:s'))
			);
			throw new AuthorizationException(self::ERROR_ACTIVATION_NOT_AUTHORIZED);
		}

		$connection = $this->activationModel->configuration()->writable_db_connection();
		$connection->beginTransaction();

		try {
			$this->activationModel->create($equipment->id());
			$this->loggedEventModel->create(
				(new LoggedEvent())
					->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
					->set_card_id($card_id)
					->set_equipment_id($equipment->id())
					->set_time(date('Y-m-d H:i:s'))
			);
			$connection->commit();
		} catch (\Throwable $t) {
			$connection->rollBack();
			throw $t;
		}

		return $equipment;
	}

	/**
	 * Record a device status change
	 *
	 * @param string $filePath  the path to a file from which to read status
	 *      change data
	 * @param string $mac  the mac address of the portal box
	 * @param array $headers  the request headers
	 * @return Equipment  the portal box
	 * @throws AuthenticationException  depending on the status change, mac, and
	 *      headers
	 * @throws AuthorizationException  depending on the status change, mac, and
	 *      headers
	 */
	public function changeStatus(
		string $filePath,
		string $mac,
		array $headers
	): Equipment {
		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_STATUS_CHANGE_BODY);
		}

		if ($data === 'shutdown') {
			return $this->shutdown($mac, $headers);
		}

		// if ($data === 'startup') {
		// 	return $this->startup($mac);
		// }

		throw new InvalidArgumentException(self::ERROR_INVALID_STATUS_CHANGE_BODY);
	}

	/**
	 * Shutdown a portal box
	 *
	 * @param string $mac  the mac address of the portal box
	 * @param array $headers  the request headers
	 * @return Equipment  the portal box
	 * @throws AuthenticationException  if the request headers do not contain a
	 *      HTTP_AUTHORIZATION header which is a properly formatted Bearer token
	 *      when the token is the id of a user card
	 * @throws AuthorizationException  if the card id does not map to a shutdown
	 *      card.
	 */
	private function shutdown(string $mac, array $headers): Equipment {
		if(!array_key_exists('HTTP_AUTHORIZATION', $headers)) {
			throw new AuthenticationException(self::ERROR_NO_AUTHORIZATION_HEADER);
		}
		$header = $headers['HTTP_AUTHORIZATION'];

		if(strlen($header) < 8 || strcmp('Bearer ', substr($header, 0 , 7)) != 0) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card_id = filter_var(substr($header, 7), FILTER_VALIDATE_INT);
		if($card_id === false) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card = $this->cardModel->read($card_id);
		if ($card === null || $card->type_id() !== CardType::SHUTDOWN) {
			throw new AuthorizationException(self::ERROR_SHUTDOWN_NOT_AUTHORIZED);
		}

		$query = (new EquipmentQuery())
			->set_exclude_out_of_service(true)
			->set_mac_address($mac);
		$equipment = $this->equipmentModel->search($query);
		if (empty($equipment)) {
			throw new NotFoundException(self::ERROR_EQUIPMENT_NOT_FOUND);
		}

		// get the first item in the list... in theory there should only be one
		// item in the list, but we can afford to be defensive
		$equipment = reset($equipment);

		$this->loggedEventModel->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::PLANNED_SHUTDOWN)
				->set_card_id($card_id)
				->set_equipment_id($equipment->id())
				->set_time(date('Y-m-d H:i:s'))
		);

		return $equipment;
	}
}
