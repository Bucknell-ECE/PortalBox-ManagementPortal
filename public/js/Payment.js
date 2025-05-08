import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Payment {
	/**
	 * Get a list of payments matching a query
	 *
	 * @param {String} query - a url query string of search parameters
	 * @return Array<Payment> a list of payments that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static list(query = '') {
		return fetch("/api/payments.php?" + query, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list payments";
		});
	}

	/**
	 * Get a payment by id
	 *
	 * @param int id the unique id of the Payment to retreive
	 * @return Payment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static read(id) {
		return fetch("/api/payments.php?id=" + id, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to find payment: " + id;
		});
	}

	/**
	 * Add payment to those tracked by API
	 *
	 * @return Payment as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static create(data) {
		return fetch("/api/payments.php", {
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
	
			throw "API was unable to save new payment";
		});
	}

	/**
	 * Modify the payment specified by id
	 *
	 * @param int id the unique id of the Payment to modify
	 * @return Payment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static modify(id, data) {
		return fetch("/api/payments.php?id=" + id, {
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
	
			throw "API was unable to save payment";
		});
	}
}
