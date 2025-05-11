import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Role {
	/**
	 * Get a list of roles matching a query
	 *
	 * @param {String} query - a url query string of search parameters
	 * @return {Array<Equipment>} a list of equipment that match the query
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/roles.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list roles";
		});
	}

	/**
	 * Get a role by id
	 *
	 * @param {int} id - the unique id of the Role to retreive
	 * @return {Role} - the role specified by the id
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static read(id) {
		return fetch("/api/roles.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find role: " + id;
		});
	}

	/**
	 * Add role to those tracked by API
	 *
	 * @param {Object} data - a dictionary representation of the Role
	 * @return {Role} as tracked by API
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static create(data) {
		return fetch("/api/roles.php", {
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
	
			throw "API was unable to save new role";
		});
	}

	/**
	 * Modify the role specified by id
	 *
	 * @param {int} id - the unique id of the Role to modify
	 * @param {Object} data - a dictionary representation of the Role
	 * @return {Role} as tracked by API
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static modify(id, data) {
		return fetch("/api/roles.php?id=" + id, {
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
	
			throw "API was unable to save role";
		});
	}
}
