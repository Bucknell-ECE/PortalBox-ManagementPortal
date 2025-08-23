<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\Card;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Query\CardQuery;
use Portalbox\Session\SessionInterface;

/**
 * Manage Cards
 */
class CardService {
	public const ERROR_UNAUTHENTICATED_READ = 'You ust be authenticated to read cards';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified card(s)';
	public const ERROR_CARD_NOT_FOUND = 'We have no record of that card';
	public const ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT = 'The value of equipment_type_id must be an integer';
	public const ERROR_USER_FILTER_MUST_BE_INT = 'The value of user_id must be an integer';

	protected SessionInterface $session;
	protected CardModel $cardModel;

	public function __construct(
		SessionInterface $session,
		CardModel $cardModel
	) {
		$this->session = $session;
		$this->cardModel = $cardModel;
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
