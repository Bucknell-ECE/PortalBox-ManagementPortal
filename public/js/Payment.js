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
	static async list(query = '') {
		const response = await fetch("/api/payments.php?" + query, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list payments";
	}

	/**
	 * Get a payment by id
	 *
	 * @param int id the unique id of the Payment to retrieve
	 * @return Payment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		const response = await fetch("/api/payments.php?id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to find payment: " + id;
	}

	/**
	 * Add payment to those tracked by API
	 *
	 * @return Payment as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/payments.php", {
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

		throw "API was unable to save new payment";
	}

	/**
	 * Modify the payment specified by id
	 *
	 * @param int id the unique id of the Payment to modify
	 * @return Payment specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async modify(id, data) {
		const response = await fetch("/api/payments.php?id=" + id, {
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

		throw "API was unable to save payment";
	}
}
