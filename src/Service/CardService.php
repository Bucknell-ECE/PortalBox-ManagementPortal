<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\Card;
use Portalbox\Entity\CardType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\CardQuery;
use Portalbox\Session\SessionInterface;

/**
 * Manage Cards
 */
class CardService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create cards';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create cards';
	public const ERROR_INVALID_CARD_DATA = 'We can not create a card from the provided data';
	public const ERROR_CARD_ID_IS_REQUIRED = '\'id\' is a required field';
	public const ERROR_CARD_TYPE_IS_REQUIRED = '\'type_id\' is a required field';
	public const ERROR_CARD_TYPE_IS_INVALID = '\'type_id\' must correspond to a valid card type id';
	public const ERROR_USER_ID_IS_REQUIRED = '\'user_id\' is a required field when type is set to user card';
	public const ERROR_USER_ID_IS_INVALID = '\'user_id\' must correspond to a valid user';
	public const ERROR_EQUIPMENT_TYPE_ID_IS_REQUIRED = '\'equipment_type_id\' is a required field when type is set to training card';
	public const ERROR_EQUIPMENT_TYPE_ID_IS_INVALID = '\'equipment_type_id\' must correspond to a valid equipment type';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read cards';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified card(s)';
	public const ERROR_CARD_NOT_FOUND = 'We have no record of that card';
	public const ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT = 'The value of equipment_type_id must be an integer';
	public const ERROR_ID_FILTER_MUST_BE_INT = 'The value of search must be an integer';
	public const ERROR_USER_FILTER_MUST_BE_INT = 'The value of user_id must be an integer';

	protected SessionInterface $session;
	protected CardModel $cardModel;
	protected EquipmentTypeModel $equipmentTypeModel;
	protected UserModel $userModel;

	public function __construct(
		SessionInterface $session,
		CardModel $cardModel,
		EquipmentTypeModel $equipmentTypeModel,
		UserModel $userModel
	) {
		$this->session = $session;
		$this->cardModel = $cardModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
		$this->userModel = $userModel;
	}

	/**
	 * Create a card from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Card  The card which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      cards
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): Card {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_CARD)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_CARD_DATA);
		}

		$card = json_decode($data, TRUE);
		if (!is_array($card)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_CARD_DATA);
		}

		return $this->cardModel->create($this->deserialize($card));
	}

	/**
	 * Deserialize a Card entity object from a dictionary
	 *
	 * @param array data - a dictionary representing a Card
	 * @return Card - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a required field is not specified
	 */
	private function deserialize(array $data): Card {
		$id = filter_var($data['id'] ?? '', FILTER_VALIDATE_INT);
		if ($id === false) {
			throw new InvalidArgumentException(self::ERROR_CARD_ID_IS_REQUIRED);
		}

		if (!array_key_exists('type_id', $data)) {
			throw new InvalidArgumentException(self::ERROR_CARD_TYPE_IS_REQUIRED);
		}

		switch ($data['type_id']) {
			case CardType::USER:
				$userId = filter_var($data['user_id'] ?? '', FILTER_VALIDATE_INT);
				if ($userId === false) {
					throw new InvalidArgumentException(self::ERROR_USER_ID_IS_REQUIRED);
				}

				if ($this->userModel->read($userId) === null) {
					throw new InvalidArgumentException(self::ERROR_USER_ID_IS_INVALID);
				}

				return (new UserCard())
					->set_id($id)
					->set_user_id($userId);
			case CardType::TRAINING:
				$typeId = filter_var($data['equipment_type_id'] ?? '', FILTER_VALIDATE_INT);
				if ($typeId === false) {
					throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPE_ID_IS_REQUIRED);
				}

				if ($this->equipmentTypeModel->read($typeId) === null) {
					throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPE_ID_IS_INVALID);
				}

				return (new TrainingCard())
					->set_id($id)
					->set_equipment_type_id($typeId);
			case CardType::PROXY:
				return (new ProxyCard())->set_id($id);
			case CardType::SHUTDOWN:
				return (new ShutdownCard())->set_id($id);
			default:
				throw new InvalidArgumentException(self::ERROR_CARD_TYPE_IS_INVALID);
		}
	}

	/**
	 * Read a card by id
	 *
	 * @param int $cardId  the unique id of the card to read
	 * @return Card  the card
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the card
	 * @throws NotFoundException  if the card is not found
	 */
	public function read(int $cardId): Card {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::READ_CARD)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$card = $this->cardModel->read($cardId);
		if ($card === null) {
			throw new NotFoundException(self::ERROR_CARD_NOT_FOUND);
		}

		return $card;
	}

	/**
	 * Read all cards matching the filters
	 *
	 * @param array<string, string>  filters that all cards in the result set
	 *      must match
	 * @return Card[]  the cards
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the cards passing the filter
	 * @throws InvalidArgumentException  if the filter specifies an equipment
	 *      type id or user id that is not an integer
	 */
	public function readAll(array $filters): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$query = new CardQuery();

		if(isset($filters['equipment_type_id']) && !empty($filters['equipment_type_id'])) {
			$equipment_type_id = filter_var($filters['equipment_type_id'], FILTER_VALIDATE_INT);
			if ($equipment_type_id === false) {
				throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT);
			}

			$query->set_equipment_type_id($equipment_type_id);
		}

		if(isset($filters['card']) && !empty($filters['card'])) {
			$id = filter_var($filters['card'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException(self::ERROR_ID_FILTER_MUST_BE_INT);
			}

			$query->set_id($id);
		}

		if(isset($filters['user_id']) && !empty($filters['user_id'])) {
			$user_id = filter_var($filters['user_id'], FILTER_VALIDATE_INT);
			if ($user_id === false) {
				throw new InvalidArgumentException(self::ERROR_USER_FILTER_MUST_BE_INT);
			}

			$query->set_user_id($user_id);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::LIST_CARDS)) {
			if ($role->has_permission(Permission::LIST_OWN_CARDS)) {
				if ($authenticatedUser->id() !== $query->user_id()) {
					throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
				}
			} else {
				throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
			}
		}

		return $this->cardModel->search($query);
	}
}
