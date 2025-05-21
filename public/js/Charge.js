import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Charge {
	/**
	 * Get a list of charges matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<Charge> a list of charges that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/charges.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list charges";
		});
	}

	/**
	 * Get a charge by id
	 *
	 * @param int id the unique id of the Charge to retreive
	 * @return Charge specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static read(id) {
		return fetch("/api/charges.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find charge: " + id;
		});
	}

}
