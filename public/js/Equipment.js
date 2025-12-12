import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Equipment {
	/**
	 * Get a list of equipment matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<Equipment> a list of equipment that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async list(query = '') {
		const response = await fetch("/api/equipment.php?" + query, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list equipment";
	}

	/**
	 * Get an equipment by id
	 *
	 * @param int id the unique id of the Equipment to retrieve
	 * @return Equipment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		let response = await fetch("/api/equipment.php?id=" + id, { "credentials": "same-origin" });

		if(!response.ok) {
			if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find equipment: " + id;
		}

		return await response.json();
	}

	/**
	 * Add equipment to those tracked by API
	 *
	 * @return Equipment as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/equipment.php", {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PUT"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save new equipment";
	}

	/**
	 * Modify the equipment specified by id
	 *
	 * @param int id the unique id of the Equipment to modify
	 * @return Equipment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async modify(id, data) {
		const response = await fetch("/api/equipment.php?id=" + id, {
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

		throw "API was unable to save equipment";
	}
}
