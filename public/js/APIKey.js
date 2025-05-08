import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class APIKey {
	/**
	 * Get a list of api keys
	 *
	 * @return Array<APIKey> a list of api keys
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static list() {
		return fetch("/api/api-keys.php", {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list api keys";
		});
	}

	/**
	 * Get an api key by id
	 *
	 * @param int id the unique id of the APIKey to retreive
	 * @return APIKey specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static read(id) {
		return fetch("/api/api-keys.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find api key: " + id;
		});
	}

	/**
	 * Add api key to those tracked by API
	 *
	 * @return APIKey as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static create(data) {
		return fetch("/api/api-keys.php", {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PUT"
		}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}
	
			throw "API was unable to save new api key";
		});
	}

	/**
	 * Modify the api key specified by id
	 *
	 * @param int id the unique id of the APIKey to modify
	 * @return APIKey specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static modify(id, data) {
		return fetch("/api/api-keys.php?id=" + id, {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "POST"
		}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}
	
			throw "API was unable to save api key";
		});
	}

	/**
	 * 
	 */
	static delete(id) {
		return fetch("/api/api-keys.php?id=" + id, {
			credentials: "include",
			method: "DELETE"
		}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to delete API key";
		})
	}
}
