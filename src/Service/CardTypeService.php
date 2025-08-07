<?php

declare(strict_types=1);

namespace Portalbox\Service;

use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\CardTypeModel;
use Portalbox\Session\SessionInterface;

class CardTypeService {
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read card types';

	protected SessionInterface $session;
	protected CardTypeModel $cardTypeModel;

	public function __construct(
		SessionInterface $session,
		CardTypeModel $cardTypeModel
	) {
		$this->session = $session;
		$this->cardTypeModel = $cardTypeModel;
	}

	/**
	 * Read all card types
	 *
	 * @return CardType[]  the list of all card types
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      all card types
	 */
	public function readAll() {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException();
		}

		if (!$authenticatedUser->role()->has_permission(Permission::LIST_CARD_TYPES)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		return $this->cardTypeModel->search();
	}
}
