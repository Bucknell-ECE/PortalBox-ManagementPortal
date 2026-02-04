import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Badge {
	/**
	 * Get a list of badges for the specified user
	 *
	 * @param {int} id - the unique id of the user
	 * @return {String[]} - the list of badges the user has earned
	 * @throws {SessionTimeOutError} - if the user session has expired
	 * @throws {String} - if any other error occurs
	 */
	static async listForUser(id) {
		const response = await fetch("/api/v2/badges.php?user_id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to retrieve specified log segment";
	}
}