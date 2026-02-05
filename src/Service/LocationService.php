<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\LocationModel;
use Portalbox\Session;
use Portalbox\Type\Location;

/**
 * Manage Locations
 */
class LocationService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create locations';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create locations';
	public const ERROR_INVALID_LOCATION_DATA = 'We can not construct a location from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_NAME_IS_INVALID = '\'name\' must not be an empty string';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read locations';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified location(s)';
	public const ERROR_LOCATION_NOT_FOUND = 'We have no record of that location';

	public const ERROR_UNAUTHENTICATED_MODIFY = 'You must be authenticated to modify locations';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not authorized to modify locations';

	protected Session $session;
	protected LocationModel $locationModel;

	public function __construct(
		Session $session,
		LocationModel $locationModel
	) {
		$this->session = $session;
		$this->locationModel = $locationModel;
	}

	/**
	 * Deserialize a Location from a dictionary
	 *
	 * @param array data - a dictionary representing a Location
	 * @return Location - an object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data): Location {
		if (!array_key_exists('name', $data)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}
		
		$name = strip_tags($data['name']);
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_INVALID);
		}

		return (new Location())->set_name($name);
	}

	/**
	 * Create a location from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Location  the location which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      locations
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): Location {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_LOCATION)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_LOCATION_DATA);
		}

		$location = json_decode($data, TRUE);
		if (!is_array($location)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_LOCATION_DATA);
		}

		return $this->locationModel->create($this->deserialize($location));
	}

	/**
	 * Read a location by id
	 *
	 * @param int $id  the unique id of the location to read
	 * @return Location  the location
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the location
	 * @throws NotFoundException  if the location is not found
	 */
	public function read(int $id): Location {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_LOCATION)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$location = $this->locationModel->read($id);
		if ($location === null) {
			throw new NotFoundException(self::ERROR_LOCATION_NOT_FOUND);
		}

		return $location;
	}

	/**
	 * Read all locations
	 *
	 * @return Location[]  the locations
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the locations
	 */
	public function readAll(): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::LIST_LOCATIONS)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		return $this->locationModel->search();
	}

	/**
	 * Modify a location using data read from the specified data stream
	 *
	 * @param int $id  the unique id of the location to modify
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Location  the location as modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not update
	 *      locations
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 * @throws NotFoundException  if the location is not found
	 */
	public function update(int $id, string $filePath): Location {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_MODIFY);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_LOCATION)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_LOCATION_DATA);
		}

		$location = json_decode($data, TRUE);
		if (!is_array($location)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_LOCATION_DATA);
		}

		$location = $this->locationModel->update(
			$this->deserialize($location)->set_id($id)
		);
		if ($location === null) {
			throw new NotFoundException(self::ERROR_LOCATION_NOT_FOUND);
		}

		return $location;
	}
}
