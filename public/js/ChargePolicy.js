/**
 * Hide away the details of working with the REST API
 */
export class ChargePolicy {
	static format(id) {
		switch (id) {
			case 1:
				return "Manually Adjusted";
			case 2:
				return "No Charge";
			case 3:
				return "Per Use";
			case 4:
				return "Per Minute";
			default:
				return "Invalid";
		}
	}

	/**
	 * Get a list of charge policies
	 *
	 * @return Array<ChargePolicy> a list of charge policies
	 */
	static list() {
		return Promise.resolve([
			{
				"id":2,
				"name":"No Charge"
			},
			{
				"id": 3,
				"name":"Per Use"
			},
			{
				"id":4,
				"name":"Per Minute"
			}
		]);
	}
}
