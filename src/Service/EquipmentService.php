<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\CardType;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;

/**
 * Handle requests from Portal Boxes
 *
 * Unlike other services, this service does not use Cookie or API Keys to
 * authenticate. Instead methods require a card id that must be assigned to an
 * active user with the requisite permissions
 */
class EquipmentService {
	public const ERROR_NO_AUTHORIZATION_HEADER = 'No Authorization header provided';
	public const ERROR_INVALID_AUTHORIZATION_HEADER = 'Improperly formatted Authorization header. Please use "Bearer " + token syntax';
	public const ERROR_REGISTRATION_NOT_AUTHORIZED = 'You are not authorized to register a portalbox.';
	public const ERROR_MAC_ADDRESS_REQUIRED = 'MAC address is required';
	public const ERROR_DEVICE_ALREADY_REGISTERED = 'A device with the given MAC address already exists';
	public const ERROR_INCOMPLETE_SETUP_NO_LOCATIONS = 'You must first setup a location';

	public const DEFAULT_DEVICE_NAME = 'Unassigned Portalbox';

	protected CardModel $cardModel;
	protected EquipmentModel $equipmentModel;
	protected EquipmentTypeModel $equipmentTypeModel;
	protected LocationModel $locationModel;

	public function __construct(
		CardModel $cardModel,
		EquipmentModel $equipmentModel,
		EquipmentTypeModel $equipmentTypeModel,
		LocationModel $locationModel
	) {
		$this->cardModel = $cardModel;
		$this->equipmentModel = $equipmentModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
		$this->locationModel = $locationModel;
	}

	/**
	 * Register a new portal box
	 *
	 * @param array $headers  the request headers
	 * @param array $params  the request parameters
	 * @return Equipment  the portal box as registered with the system
	 * @throws AuthenticationException  if the request headers do not contain a
	 *      HTTP_AUTHORIZATION header which is a properly formatted Bearer token
	 *      when the token is the id of a user card
	 * @throws AuthorizationException  if the card id does not map to a user
	 *      card or the user mapped to be the card id does not have the
	 *      CREATE_EQUIPMENT permission
	 * @throws InvalidArgumentException  if the request params do not contain
	 *      the device mac address, or if the mac is already present in the
	 *      system
	 */
	public function register(array $headers, array $params): Equipment {
		if(!array_key_exists('HTTP_AUTHORIZATION', $headers)) {
			throw new AuthenticationException(self::ERROR_NO_AUTHORIZATION_HEADER);
		}
		$header = $headers['HTTP_AUTHORIZATION'];

		if(strlen($header) < 8 || strcmp('Bearer ', substr($header, 0 , 7)) != 0) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$cardId = filter_var(substr($header, 7), FILTER_VALIDATE_INT);
		if($cardId === false) {
			throw new AuthenticationException(self::ERROR_INVALID_AUTHORIZATION_HEADER);
		}

		$card = $this->cardModel->read($cardId);
		if ($card === null || $card->type_id() !== CardType::USER) {
			throw new AuthorizationException(self::ERROR_REGISTRATION_NOT_AUTHORIZED);
		}

		$authenticatedUser = $card->user();
		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_REGISTRATION_NOT_AUTHORIZED);
		}

		if (!array_key_exists('mac', $params)) {
			throw new InvalidArgumentException(self::ERROR_MAC_ADDRESS_REQUIRED);
		}
		$mac = $params['mac'];

		$query = (new EquipmentQuery())
			->set_exclude_out_of_service(true)
			->set_mac_address($mac);
		if (!empty($this->equipmentModel->search($query))) {
			throw new InvalidArgumentException(self::ERROR_DEVICE_ALREADY_REGISTERED);
		}

		$equipmentTypes = $this->equipmentTypeModel->search();

		$locations = $this->locationModel->search();
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
}
