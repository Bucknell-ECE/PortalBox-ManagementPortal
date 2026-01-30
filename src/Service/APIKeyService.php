<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\APIKey;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Session;

/**
 * Manage API Keys
 */
class APIKeyService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create API keys';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create API keys';
	public const ERROR_INVALID_API_KEY_DATA = 'We can not construct an API key from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_NAME_IS_INVALID = '\'name\' must not be an empty string';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read API keys';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified API key(s)';
	public const ERROR_API_KEY_NOT_FOUND = 'We have no record of that API key';

	public const ERROR_UNAUTHENTICATED_MODIFY = 'You must be authenticated to modify API keys';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not authorized to modify API keys';

	public const ERROR_UNAUTHENTICATED_DELETE = 'You must be authenticated to delete API keys';
	public const ERROR_UNAUTHORIZED_DELETE = 'You are not authorized to delete API keys';

	protected Session $session;
	protected APIKeyModel $apiKeyModel;

	public function __construct(
		Session $session,
		APIKeyModel $apiKeyModel
	) {
		$this->session = $session;
		$this->apiKeyModel = $apiKeyModel;
	}

	/**
	 * Deserialize an APIKey entity object from a dictionary
	 *
	 * @param array data - a dictionary representing a Payment
	 * @return APIKey - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	private function deserialize(array $data): APIKey {
		if (!array_key_exists('name', $data)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}

		$name = strip_tags($data['name']);
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_INVALID);
		}

		return (new APIKey())
			->set_name($name);
	}

	/**
	 * Create an API key from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return APIKey  The API key which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      API keys
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): APIKey {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_API_KEY)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_API_KEY_DATA);
		}

		$key = json_decode($data, TRUE);
		if (!is_array($key)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_API_KEY_DATA);
		}

		return $this->apiKeyModel->create($this->deserialize($key));
	}

	/**
	 * Read an API key by id
	 *
	 * @param int $id  the unique id of the API key to read
	 * @return APIKey  the API key
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the API key
	 * @throws NotFoundException  if the API key is not found
	 */
	public function read(int $id): APIKey {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::READ_API_KEY)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$key = $this->apiKeyModel->read($id);
		if ($key === null) {
			throw new NotFoundException(self::ERROR_API_KEY_NOT_FOUND);
		}

		return $key;
	}

	/**
	 * Read all API keys
	 *
	 * @param array<string, string>  filters that all api keys in the result set
	 *      must match
	 * @return APIKey[]  the API keys
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the API keys
	 */
	public function readAll(array $filters): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::LIST_API_KEYS)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$query = new APIKeyQuery();
		if(isset($filters['token']) && !empty($filters['token'])) {
			$query->set_token($filters['token']);
		}

		return $this->apiKeyModel->search($query);
	}

	/**
	 * Modify an API key using data read from the specified data stream
	 *
	 * @param int $id  the unique id of the API key to modify
	 * @param string $filePath  the path to a file from which to read json data
	 * @return APIKey  the API key as modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not update
	 *      API keys
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 * @throws NotFoundException  if the API key is not found
	 */
	public function update(int $id, string $filePath): APIKey {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_MODIFY);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_API_KEY)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_API_KEY_DATA);
		}

		$key = json_decode($data, TRUE);
		if (!is_array($key)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_API_KEY_DATA);
		}

		$key = $this->apiKeyModel->update(
			$this->deserialize($key)->set_id($id)
		);
		if ($key === null) {
			throw new NotFoundException(self::ERROR_API_KEY_NOT_FOUND);
		}

		return $key;
	}

	/**
	 * Delete an API key
	 *
	 * @param int $id  the unique id of the API key to delete
	 * @return APIKey  the API key which was deleted
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not delete
	 *      API keys
	 * @throws NotFoundException  if the API key is not found
	 */
	public function delete(int $id): APIKey {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_DELETE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::DELETE_API_KEY)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_DELETE);
		}

		$key = $this->apiKeyModel->delete($id);

		if ($key === null) {
			throw new NotFoundException(self::ERROR_API_KEY_NOT_FOUND);
		}

		return $key;
	}
}
