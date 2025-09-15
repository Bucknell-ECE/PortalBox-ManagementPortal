<?php

namespace Portalbox;

use Portalbox\Config;
use Portalbox\Model\ActivationModel;
use Portalbox\Model\CardModel;
use Portalbox\Model\CardTypeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Service\CardTypeService;
use Portalbox\Service\EquipmentService;
use Portalbox\Session\Session;

/**
 * Dependency Containers are programming magic wiring up objects with the other
 * objects they depend on thus enabling dependency inversion. The nice part is
 * we can create Services and persistence adapters Factory style in one place
 * avoiding mistakes of forgetting a dependency here or there.
 * 
 * Why not PHP-DI? Because of how hard it is to deploy dependencies in a certain
 * environment. That said we are following the same interface as PHP-DI so in
 * theory we can replace this class with PHP-DI in the future.
 */
class DependencyContainer {
	/**
	 * A cache of already created objects that can be reused
	 *
	 * @var array<string, mixed>
	 */
	protected array $cache = [];

	/**
	 * Get an autowired instance of a class
	 *
	 * @param string $type  the type of the object desired.
	 * @return mixed an object of the specified type
	 */
	public function get(string $type): mixed {
		if (!array_key_exists($type, $this->cache)) {
			$this->cache[$type] = $this->make($type);
		}

		return $this->cache[$type];
	}

	/**
	 * Create an autowired instance of a class
	 *
	 * @param string $type  the type of the object to be created.
	 * @return mixed an object of the specified type
	 */
	private function make(string $type): mixed {
		switch ($type) {
			case Config::class:
				return new Config();
			case ActivationModel::class:
				return new ActivationModel($this->get(Config::class));
			case CardModel::class:
				return new CardModel($this->get(Config::class));
			case CardTypeModel::class:
				return new CardTypeModel($this->get(Config::class));
			case EquipmentModel::class:
				return new EquipmentModel($this->get(Config::class));
			case EquipmentTypeModel::class:
				return new EquipmentTypeModel($this->get(Config::class));
			case LocationModel::class:
				return new LocationModel($this->get(Config::class));
			case LoggedEventModel::class:
				return new LoggedEventModel($this->get(Config::class));
			case RoleModel::class:
				return new RoleModel($this->get(Config::class));
			case UserModel::class:
				return new UserModel($this->get(Config::class));
			case CardTypeService::class:
				return new CardTypeService(
					$this->get(Session::class),
					$this->get(CardTypeModel::class)
				);
			case EquipmentService::class:
				return new EquipmentService(
					$this->get(ActivationModel::class),
					$this->get(CardModel::class),
					$this->get(EquipmentModel::class),
					$this->get(EquipmentTypeModel::class),
					$this->get(LocationModel::class),
					$this->get(LoggedEventModel::class)
				);
			case Session::class:
				return new Session();
			default:
				throw new Exception('DependencyContainer does not have instructions for building instances of ' . $type);
		}
	}
}
