import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class EquipmentType {
	/**
	 * Get a list of equipment types matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<EquipmentType> a list of equipment types that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/equipment-types.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list equipment";
		});
	}

	/**
	 * Get an equipment type by id
	 *
	 * @param int id the unique id of the EquipmentType to retreive
	 * @return EquipmentType specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static read(id) {
		return fetch("/api/equipment-types.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find equipment: " + id;
		});
	}

	/**
	 * Add equipment type to those tracked by API
	 *
	 * @return EquipmentType as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static create(data) {
		return fetch("/api/equipment-types.php", {
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
	
			throw "API was unable to save new equipment type";
		});
	}

	/**
	 * Modify the equipment type specified by id
	 *
	 * @param int id the unique id of the EquipmentType to modify
	 * @return EquipmentType specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static modify(id, data) {
		return fetch("/api/equipment-types.php?id=" + id, {
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
	
			throw "API was unable to save equipment";
		});
	}
}
