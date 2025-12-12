import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API to read Usage data
 */
export class Usage {
	/**
	 * Get usage data across all locations
	 *
	 * @return {Array<Object>} a list of usage metrics
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static async listAllUsage() {
		const response = await fetch("/api/v2/location-usage.php");

		if(!response.ok) {
			if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find location: " + id + " usage";
		}

		const data = await response.json();

		return Usage.#convertDictionaryToListOfRecords(data);
	}

	/**
	 * Get usage data for a location
	 *
	 * @param {Number|String} id  the unique id of the Location
	 * @return {Array<Object>} a list of usage metrics
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static async listLocationUsage(id) {
		const response = await fetch(
			"/api/v2/location-usage.php?id=" + id,
			{ "credentials": "same-origin" }
		);

		if(!response.ok) {
			if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find location: " + id + " usage";
		}

		const data = await response.json();

		return Usage.#convertDictionaryToListOfRecords(data);
	}

	/**
	 * Get usage data for a device attached to a portalbox
	 *
	 * @param {Number|String} id  the unique id of the Equipment
	 * @return {Array<Object>} a list of usage metrics
	 * @throws {SessionTimeOutError} if the user session has expired
	 * @throws {String} if any other error occurs
	 */
	static async listEquipmentUsage(id) {
		const response = await fetch(
			"/api/v2/equipment-usage.php?id=" + id,
			{ "credentials": "same-origin" }
		);

		if(!response.ok) {
			if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find equipment: " + id + " usage";
		}

		const data = await response.json();

		return Usage.#convertDictionaryToListOfRecords(data);
	}

	/**
	 * The backend returns data in a dictionary like object:
	 *
	 * {'2025-01-01': 25, '2025-01-02': 12, ...}
	 *
	 * but our UI needs a list of data points:
	 *
	 * [
	 *    {date: '2025-01-01', count: 25},
	 *    {date: '2025-01-02', count: 12},
	 *    ...
	 * ]
	 *
	 * This method performs this conversion
	 */
	static #convertDictionaryToListOfRecords(data) {
		const usage = [];
		for (let prop in data){
			if (data.hasOwnProperty(prop)){
				usage.push({
					'date': prop,
					'count': data[prop]
				});
			}
		}

		return usage;
	}
}
