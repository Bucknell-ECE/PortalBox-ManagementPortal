import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class BadgeRule {
	/**
	 * Get a list of badge rules
	 *
	 * @return Array<BadgeRule> a list of badge rules
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async list() {
		const response = await fetch("/api/v2/badge-rules.php", { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list badge rules";
	}

	/**
	 * Get a badge rule by id
	 *
	 * @param int id the unique id of the BadgeRule to retrieve
	 * @return BadgeRule specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		const response = await fetch("/api/v2/badge-rules.php?id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to find badge rule: " + id;
	}

	/**
	 * Add a badge rule to those tracked by API
	 *
	 * @return BadgeRule as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/v2/badge-rules.php", {
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

		throw "API was unable to save new badge rule";
	}

	/**
	 * Modify the badge rule specified by id
	 *
	 * @param int id the unique id of the BadgeRule to modify
	 * @return BadgeRule as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async modify(id, data) {
		const response = await fetch("/api/v2/badge-rules.php?id=" + id, {
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

		throw "API was unable to save badge rule";
	}

	/**
	 * Delete the badge rule specified by id
	 *
	 * @param int id the unique id of the BadgeRule to delete
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async delete(id) {
		const response = await fetch("/api/v2/badge-rules.php?id=" + id, {
			credentials: "include",
			method: "DELETE"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to delete badge rule";
	}
}
