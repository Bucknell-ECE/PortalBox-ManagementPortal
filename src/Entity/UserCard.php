<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox shutsdown when presented with cards of this type.
 * 
 * @package Portalbox\Entity
 */
class UserCard extends AbstractEntity implements Card {

	/**
	 * The id of the user this card was issued to
	 */
	private $user_id;

	/**
	 * Get the type of the card
	 *
	 * @return int - type one of the predefined constants exposed by CardType
	 */
	public function type_id() : int {
		return CardType::USER;
	}

	/**
	 * Get the id of the user to whom this card was issued
	 *
	 * @return int - the id of the user to whom this card was issued
	 */
	public function user_id() : int {
		return $this->user_id;
	}

	/**
	 * Set the id of the user to whom this card was issued
	 *
	 * @param int user_id - the id of the user to whom this card was issued
	 * @return UserCard - returns this in order to support fluent syntax.
	 */
	public function set_user_id(int $user_id) : UserCard {
		$this->user_id = $user_id;
		$this->user = NULL;
		return $this;
	}

	/**
	 * Get the user to whom this card was issued
	 *
	 * @return User|null - user to whom this card was issued
	 */
	public function user() : ?User {
		return $this->user;
	}

	/**
	 * Set the user to whom this card was issued
	 *
	 * @param User|null user - user to whom this card was issued
	 * @return UserCard - returns this in order to support fluent syntax.
	 */
	public function set_user(?User $user) : UserCard {
		$this->user = $user;
		if(NULL === $user) {
			$this->user_id = -1;
		} else {
			$this->user_id = $user->id();
		}

		return $this;
	}
}
