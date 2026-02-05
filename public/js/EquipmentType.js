import { SessionTimeOutError } from './SessionTimeOutError.js';
import { ChargePolicy } from './ChargePolicy.js';

/**
 * Hide away the details of working with the REST API
 */
export class EquipmentType {
	/**
	 * Creates a new EquipmentType instance.
	 *
	 * @param {integer} id  the unique id of the EquipmentType.
	 * @param {string} name
	 * @param {bool} requires_training
	 * @param {string} charge_rate
	 * @param {int} charge_policy
	 * @param {bool} allow_proxy
	 */
	constructor(id, name, requires_training, charge_rate, charge_policy, allow_proxy) {
		this.id = id;
		this.name = name;
		this.requires_training = requires_training
		this.charge_rate = charge_rate;
		this.charge_policy = charge_policy;
		this.allow_proxy = allow_proxy;
	}

	get formatted_charge_policy() {
		return ChargePolicy.format(this.charge_policy);
	}

	/**
	 * Get a list of equipment types matching a query
	 *
	 * @param String query a url query string of search parameters
	 * @return Array<EquipmentType> a list of equipment types that match the query
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async list(query = '') {
		const response = await fetch("/api/equipment-types.php?" + query, { "credentials": "same-origin" });

		if(response.ok) {
			const data = await response.json();
			return data.map(
				(datum) => new EquipmentType(
					datum.id,
					datum.name,
					datum.requires_training,
					datum.charge_rate,
					datum.charge_policy,
					datum.allow_proxy
				)
			);
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list equipment";
	}

	/**
	 * Get an equipment type by id
	 *
	 * @param int id the unique id of the EquipmentType to retrieve
	 * @return EquipmentType specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		const response = await fetch("/api/equipment-types.php?id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			const data = await response.json();
			return new EquipmentType(
				data.id,
				data.name,
				data.requires_training,
				data.charge_rate,
				data.charge_policy,
				data.allow_proxy
			);
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to find equipment: " + id;
	}

	/**
	 * Add equipment type to those tracked by API
	 *
	 * @return EquipmentType as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/equipment-types.php", {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PUT"
		});

		if(response.ok) {
			const data = await response.json();
			return new EquipmentType(
				data.id,
				data.name,
				data.requires_training,
				data.charge_rate,
				data.charge_policy,
				data.allow_proxy
			);
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save new equipment type";
	}

	/**
	 * Modify the equipment type specified by id
	 *
	 * @param int id the unique id of the EquipmentType to modify
	 * @return EquipmentType specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async modify(id, data) {
		const response = await fetch("/api/equipment-types.php?id=" + id, {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "POST"
		});

		if(response.ok) {
			const data = await response.json();
			return new EquipmentType(
				data.id,
				data.name,
				data.requires_training,
				data.charge_rate,
				data.charge_policy,
				data.allow_proxy
			);
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save equipment";
	}
}
