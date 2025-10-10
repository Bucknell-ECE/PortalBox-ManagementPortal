<?php

declare(strict_types=1);

namespace Portalbox\Query;

/**
 * UserQuery presents a standard interface for User search queries
 */
class UserQuery {
	/**
	 * The email address of the user for which to search
	 */
	protected ?string $email = null;

	/**
	 * The name of the user for which to search
	 */
	protected ?string $name = null;

	/**
	 * The comment for the user to search for
	 */
	protected ?string $comment = null;

	/**
	 * Equipment id to find authorized users for
	 */
	protected ?int $equipment_id = null;

	/**
	 * Int for determining if inactive users should be included
	 */
	protected ?bool $include_inactive = null;

	/**
	 * The role id to which all user is a result set must be assigned
	 */
	protected ?int $role_id = null;

	/**
	 * Get the email address of the user for which to search
	 */
	public function email(): ?string {
		return $this->email;
	}

	/**
	 * Set the email address of the user for which to search
	 */
	public function set_email(string $email): self {
		$this->email = $email;
		return $this;
	}

	/**
	 * Get the name of the user for which to search
	 */
	public function name(): ?string {
		return $this->name;
	}

	/**
	 * Set the name of the user for which to search
	 */
	public function set_name(string $name): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the comment of the user for which to search
	 */
	public function comment(): ?string {
		return $this->comment;
	}
	/**
	 * Set the comment of the user for which to search
	 */
	public function set_comment(string $comment): self {
		$this->comment = $comment;
		return $this;
	}

	/**
	 * Get the equipment id to search for authorized users with
	 */
	public function equipment_id(): ?int {
		return $this->equipment_id;
	}

	/**
	 * Set the equipment id to search for authorized users with
	 */
	public function set_equipment_id(int $equipment_id): self {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	public function include_inactive(): ?bool {
		return $this->include_inactive;
	}

	public function set_include_inactive(bool $include_inactive): self {
		$this->include_inactive = $include_inactive;
		return $this;
	}

	public function role_id(): ?int {
		return $this->role_id;
	}

	public function set_role_id(int $role_id): self {
		$this->role_id = $role_id;
		return $this;
	}
}
