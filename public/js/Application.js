import { SessionTimeOutError } from 'SessionTimeOutError.js';
import { Equipment } from 'Equipment.js';
import { Location } from 'Location.js';

import * as Permission from 'Permission.js';

/*
* Card Types and Charge Policies are integral to the functioning of
* the IoT Portal Box application and would need be changed in a
* coordinated manner not only here but also the DB and the IoT 
* application. Therefore it is reasonable to hard code them at this time.
*/
const card_types = [
	{
		"id": 1,
		"name":"shutdown"
	},
	{
		"id": 2,
		"name":"proxy"
	},
	{
		"id": 3,
		"name":"training"
	},
	{
		"id": 4,
		"name":"user"
	},
];
const charge_policies = [
	{
		"id": 2,
		"name":"No Charge"
	},
	{
		"id": 3,
		"name":"Per Use"
	},
	{
		"id": 4,
		"name":"Per Minute"
	},
];

class Application extends Moostaka {
	constructor() {
		super();

		this.user = null;
	}

	/**
	 * handleError takes action based on the error reported.
	 * 
	 * @param {*} error - the error being reported tyically from the fetch API
	 *        but could also be a {string} message to report to the user
	 */
	handleError(error) {
		if(error instanceof SessionTimeOutError) {
			this.render("#main", "session_time_out");
			this.render("#page-menu", "unauthenticated/menu");
		} else {
			this.render("#main", "error", {"error": error});
		}
	}

	/**
	 * Private utility method for setting up routes when application is
	 * configured with an authenticated user
	 */
	_init_routes_for_authenticated_user() {
		this.flush();

		let home_icons = {
			api_keys: false,
			cards: false,
			equipment: false,
			equipment_types: false,
			locations: false,
			logs: false,
			roles: false,
			users: false
		};

		// User needs CREATE_EQUIPMENT Permission to make use of /equipment/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT)) {
			this.route("/equipment/add", _ => {
				let p1 = EquipmentType.list();
				let p2 = Location.list();

				Promise.all([p1, p2]).then(values => {
					moostaka.render("#main", "admin/equipment/add", {"types": values[0], "locations": values[1]}, {}, () => {
						let form = document.getElementById("add-equipment-form");
						form.addEventListener("submit", (e) => { add_equipment(e); });
					});
				}).catch(handleError);
			});
		}

		// User needs both read and modify to make use of /equipment/id route with editing
		if(this.user.has_permission(Permission.READ_EQUIPMENT) && this.user.has_permission(Permission.MODIFY_EQUIPMENT)) {
			this.route("/equipment/:id", params => {
				let p0 = Equipment.read(params.id);
				let p1 = EquipmentType.list();
				let p2 = Location.list();

				Promise.all([p0, p1, p2]).then(values => {
					let equipment = values[0];
					equipment["service_hours"] = Math.floor(equipment["service_minutes"] / 60) + "h " + equipment["service_minutes"] % 60 + "min";
					this.render("#main", "admin/equipment/view", {"equipment": equipment, "types": values[1], "locations": values[2]}, {}, () => {
						document.getElementById("type_id").value = values[0].type_id;
						document.getElementById("location_id").value = values[0].location_id;
						let form = document.getElementById("edit-equipment-form");
						form.addEventListener("submit", (e) => { update_equipment(values[0], e); });
					});
				}).catch(this.handleError);
			});
		} else if(this.user.has_permission(Permission.READ_EQUIPMENT)) {
			// render a read only view
		}

		// User needs LIST_EQUIPMENT Permission to make use of /equipment route
		if(this.user.has_permission(Permission.LIST_EQUIPMENT)) {
			home_icons.equipment = true;
			this.route("/equipment", _ => {
				// get search params if any
				let search = {};
				let searchParams = (new URL(document.location)).searchParams;
				for(let p of searchParams) {
					if(0 < p[1].length) {
						search[p[0]] = p[1];
					}
				}
				if(0 < Object.keys(search).length) {
					search.customized = true;
				} else {
					// if search is an empty object mustache disregards it entirely
					// fool mustache by setting a value any value :)
					search.customized = false;
				}

				Equipment.list(searchParams.toString()).then(equipment => {
					this.render("#main", "admin/equipment/list", {"equipment": equipment, "search":search});
				}).catch(this.handleError);
			});
		}

		// User needs LIST_LOCATIONS Permission to make use of /locations route
		if(this.user.has_permission(Permission.LIST_LOCATIONS)) {
			home_icons.locations = true;
			this.route("/locations", _ => {

				Location.list().then(locations => {
					moostaka.render("#main", "admin/locations/list", {"locations": locations});
				}).catch(this.handleError);;
			});
		}

		// Everyone gets a home route; what it presents them is controlled by home_icons
		this.route("/", _ => {
			moostaka.render("#main", "authenticated/top-menu", {"features": home_icons});
		});
	}
	
	/**
	 * Private utility method for setting up routes when application is
	 * configured without an authenticated user
	 */
	_init_routes_for_unauthenticated_user() {
		this.flush();
		this.route("/", _params => {
			Equipment.list().then(equipment => {
				this.render("#main", "unauthenticated/availability", {"equipment": equipment});
			}).catch(this.handleError);
		});
	}

	/**
	 * Set the current user
	 * 
	 * @param User|null user - the authenticated user or null if no authenticated user
	 */
	set_user(user) {
		if(user) {
			// Transition to authenticated user session
			this.user = user;
			this._init_routes_for_authenticated_user();
			this.navigate(location.pathname); // need to explicitly update content
		} else {
			// Transition to unauthenticated session
			this.user = null;
			document.getElementById("page-menu").innerHTML = "";
			this._init_routes_for_unauthenticated_user();
			this.render("#page-menu", "unauthenticated/menu", {});
			this.navigate(location.pathname);
		}
	}
}

export { Application };
