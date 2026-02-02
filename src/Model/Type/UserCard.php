<?php

namespace Portalbox\Model\Type;

use Portalbox\Model\UserModel;
use Portalbox\Type\User;

/**
 * A database aware user card (can read the user from the database)
 */
class UserCard extends \Portalbox\Type\UserCard {
	/** The model used to read users */
	private UserModel $model;

	public function __construct(UserModel $model) {
		$this->model = $model;
	}

	/** Get the user to whom this card was issued */
	public function user(): ?User {
		if ($this->user === null) {
			$this->user = $this->model->read($this->user_id());
		}

		return $this->user;
	}
}
