import { SessionTimeOutError } from 'SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class User {
	/**
	 * Get a list of users matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<User> a list of users that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/users.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list users";
		});
	}

	/**
	 * Get a user by id
	 *
	 * @param int id the unique id of the User to retreive
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static read(id) {
		return fetch("/api/users.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find user: " + id;
		});
	}

	/**
	 * Add user to those tracked by API
	 *
	 * @return User as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static create(data) {
		return fetch("/api/users.php", {
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
	
			throw "API was unable to save new user";
		});
	}

	/**
	 * Modify the user specified by id
	 *
	 * @param int id the unique id of the User to modify
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static modify(id, data) {
		return fetch("/api/users.php?id=" + id, {
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
	
			throw "API was unable to save user";
		});
	}
}
