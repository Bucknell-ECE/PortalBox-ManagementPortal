import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class User {
	/**
	 * Creates a new User model instance.
	 *
	 * @param {integer} id - The unique id of the User.
	 * @param {object}  role - the user's role
	 * @param {string}  name
	 * @param {string}  email
	 * @param {string}  comment
	 * @param {string}  profile_image_url
	 * @param {bool}    is_active
	 * @param {array}   authorizations
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
	 * @param {integer} the permission for which to check
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
	static async authenticate(id_token = '') {
		const response = await fetch("/api/login.php", { "credentials": "same-origin", headers: { "Authorization": "Bearer " + id_token } });

		if(!response.ok) {
			const text = await response.text();

			throw response.statusText + ": " + text;
		}

		const data = await response.json();
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
	}

	/**
	 * Get a list of users matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<User> a list of users that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async list(query = '') {
		const response = await fetch("/api/v2/users.php?" + query, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list users";
	}

	/**
	 * Get a user by id
	 *
	 * @param int id the unique id of the User to retrieve
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		const response = await fetch("/api/v2/users.php?id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to find user: " + id;
	}

	/**
	 * Add user to those tracked by API
	 *
	 * @return User as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/v2/users.php", {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PUT"
		});

		if(response.ok) {
			return await response.text();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to import users";
	}

	/**
	 * Upload a csv file of users to be added to those tracked by API
	 *
	 * @param {File} file  the csv file of data to import
	 * @return {string}  the number of users which were added
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async import(file) {
		const response = await fetch("/api/v2/users.php", {
			body: file,
			credentials: "include",
			headers: {
				"Content-Type": file.type
			},
			method: "PUT"
		});

		if(!response.ok) {
			return response.text();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save new user";
	}

	/**
	 * Modify the authorizations (only) of the user specified by id
	 *
	 * @param int id the unique id of the User to modify
	 * @param string pin  the user's desired pin
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async changePIN(id, pin) {
		const response = await fetch("/api/v2/users.php?id=" + id, {
			body: JSON.stringify({pin}),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PATCH"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save pin";
	}

	/**
	 * Modify the authorizations (only) of the user specified by id
	 *
	 * @param int id the unique id of the User to modify
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async authorize(id, data) {
		const response = await fetch("/api/v2/users.php?id=" + id, {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PATCH"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save authorizations";
	}

	/**
	 * Modify the user specified by id
	 *
	 * @param int id the unique id of the User to modify
	 * @return User specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async modify(id, data) {
		const response = await fetch("/api/v2/users.php?id=" + id, {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "POST"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save user";
	}
}
