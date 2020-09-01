import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class User {
	/**
	 * Creates a new User model instance.
	 *
	 * @param {integer} id - The unique id of the User.
	 * @param (object)  role - the user's role
	 * @param {string}  name
	 * @param {string}  email
	 * @param {string}  comment
	 * @param {string}  profile_image_url
	 * @param {bool}    is_active
	 * @param {array}   authorizations
	 * 
	 */
	constructor(id, role, name, email, comment, profile_image_url, is_active, authorizations) {
		this.id = id;
		this.role = role;
		this.name = name;
		this.email = email;
		this.comment = comment;
		this.profile_image_url = profile_image_url;
		this.is_active = is_active;
		this.authorizations = authorizations;
	}

	/**
	 * Get whether the user has a specified permission
	 * 
	 * @param {interger} the permission for which to check
	 * @return {bool} true iff the user has te permission
	 */
	has_permission(permission) {
		return this.role.permissions.includes(permission);
	}

	/**
	 * Get a list of users matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<User> a list of users that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static authenticate(id_token = '') {
		return fetch("/api/login.php", {"credentials": "same-origin", headers: {"Authorization": "Bearer " + id_token}}).then(response => {
			if(response.ok) {
				return response.json();
			} else {
				response.text().then(text => {
					throw response.statusText + ": " + text;
				});
			}
		}).then(data => {
			return new User(
				data.id,
				data.role,
				data.name,
				data.email,
				data.comment,
				null,
				true,
				data.authorizations
			);
		});
	}

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
	 * Modify the authorizations (only) of the user specified by id
	 *
	 * @param int id the unique id of the User to modify
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static authorize(id, data) {
		return fetch("/api/users.php?id=" + id, {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PATCH"
		}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}
	
			throw "API was unable to save user";
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
