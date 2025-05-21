import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class LoggedEvent {
	/**
	 * Get a list of log messages
	 *
	 * @param {String} query - a url query string of search parameters
	 * @return {Array<LoggedEvent>} - a list of log messages
	 * @throws {SessionTimeOutError} - if the user session has expired
	 * @throws {String} - if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/logs.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to retrieve specified log segment";
		});
	}
}