<?php

namespace Portalbox\Entity;

/**
 * A card which can be used by a user to authenticate to a portalbox
 */
class UserCard extends Card {
	/**
	 * The id of the user this card was issued to
	 */
	protected int $user_id;

	/**
	 * The user this card was issued to
	 */
	protected ?User $user = null;

	/**
	 * Get the type of the card
	 *
	 * @return int - type one of the predefined constants exposed by CardType
	 */
	public function type_id(): int {
		return CardType::USER;
	}

	/**
	 * Get the id of the user to whom this card was issued
	 *
	 * @return int - the id of the user to whom this card was issued
	 */
	public function user_id(): ?int {
		return $this->user_id;
	}

	/**
	 * Set the id of the user to whom this card was issued
	 *
	 * @param int user_id - the id of the user to whom this card was issued
	 * @return self
	 */
	public function set_user_id(?int $user_id): self {
		$this->user_id = $user_id;
		$this->user = null; //Create new user from user_id
		return $this;
	}

	/**
	 * Get the user to whom this card was issued
	 *
	 * @return User|null - user to whom this card was issued
	 */
	public function user(): ?User {
		return $this->user;
	}

	/**
	 * Set the user to whom this card was issued
	 *
	 * @param User|null user - user to whom this card was issued
	 * @return self
	 */
	public function set_user(?User $user): self {
		$this->user = $user;
		if (null === $user) {
			$this->user_id = -1;
		} else {
			$this->user_id = $user->id();
		}

		return $this;
	}
}
