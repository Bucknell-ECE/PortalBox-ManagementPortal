/**
 * Hide away the details of working with the REST API
 */
export class ChargePolicy {
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
