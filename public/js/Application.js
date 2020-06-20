import { SessionTimeOutError } from './SessionTimeOutError.js';
import { APIKey } from './APIKey.js';
import { Equipment } from './Equipment.js';
import { EquipmentType } from './EquipmentType.js';
import { Location } from './Location.js';

import * as Permission from './Permission.js';

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

		let manage_icons = {
			cards: false,
			equipment: false,
			equipment_types: false,
			locations: false,
			users: false
		}
		let report_icons = {
			logs: false
		}
		let system_icons = {
			api_keys: false,
			roles: false
		}
		let home_icons = {
			manage: null,
			reports: null,
			system: null
		};

		// User needs LIST_API_KEYS Permission to make use of /api-keys route
		if(this.user.has_permission(Permission.LIST_EQUIPMENT_TYPES)) {
			if(!home_icons.system) { home_icons.system = system_icons }
			home_icons.system.api_keys = true;
			this.route("/api-keys", _ => {
				APIKey.list().then(keys => {
					this.render("#main", "admin/api-keys/list", {"keys": keys});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs CREATE_EQUIPMENT Permission to make use of /equipment/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT)) {
			this.route("/equipment/add", _ => {
				let p1 = EquipmentType.list();
				let p2 = Location.list();

				Promise.all([p1, p2]).then(values => {
					this.render("#main", "admin/equipment/add", {"types": values[0], "locations": values[1]}, {}, () => {
						let form = document.getElementById("add-equipment-form");
						form.addEventListener("submit", (e) => { add_equipment(e); });
					});
				}).catch(e => this.handleError(e));
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
				}).catch(e => this.handleError(e));
			});
		} else if(this.user.has_permission(Permission.READ_EQUIPMENT)) {
			// render a read only view
		}

		// User needs LIST_EQUIPMENT Permission to make use of /equipment route
		if(this.user.has_permission(Permission.LIST_EQUIPMENT)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.equipment = true;
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
					// js treats an object with no attributes as falsy
					// fool js by setting a value any value :)
					search.customized = false;
				}

				Equipment.list(searchParams.toString()).then(equipment => {
					this.render("#main", "admin/equipment/list", {"equipment": equipment, "search":search});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_EQUIPMENT_TYPES Permission to make use of /equipment-types route
		if(this.user.has_permission(Permission.LIST_EQUIPMENT_TYPES)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.equipment_types = true;
			this.route("/equipment-types", _ => {
				EquipmentType.list().then(types => {
					this.render("#main", "authenticated/equipment-types/list", {"types": types});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs CREATE_LOCATION Permission to make use of /location/add route
		if(this.user.has_permission(Permission.CREATE_LOCATION)) {
			this.route("/locations/add", _ => {
				this.render("#main", "authenticated/locations/add", {}, {}, () => {
					let form = document.getElementById("add-location-form");
					form.addEventListener("submit", (e) => this.add_location(e) );
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_LOCATIONS Permission to make use of /locations route
		if(this.user.has_permission(Permission.LIST_LOCATIONS)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.locations = true;
			this.route("/locations", _ => {
				Location.list().then(locations => {
					this.render("#main", "authenticated/locations/list", {"locations": locations});
				}).catch(e => this.handleError(e));
			});
		}

		// Everyone gets a home route; what it presents them is controlled by home_icons
		this.route("/", _ => {
			this.render("#main", "authenticated/top-menu", {"features": home_icons});
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
			}).catch(e => this.handleError(e));
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
			this.render("#page-menu", "authenticated/menu", {"user": user});
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

	/**
	 * Helper which iterates the fields in a form creating an object
	 * which has key value pairs corresponding to the name and value
	 * of the fields with a name attribute. If name is of the form
	 * "foo.bar" then the value will be nested as ret["foo"]["bar"]
	 * 
	 * @param HTMLFormElement form - the form from which to retrieve data
	 * @return Object - an object with an attribute for each form input or
	 *     group of inputs.
	 */
	get_form_data(form) {
		// should check that form is a form

		let data = {};
		for(let i = 0, len = form.elements.length; i < len; i++) {
			let field = form.elements[i];
			if(field.hasAttribute("name")) {
				let parts = field.name.split('.').reverse();
				let key = parts.pop();
				let destination = data;
				while(parts.length > 0) {
					if(undefined === destination[key]) {
						destination[key] = {};
					}

					destination = destination[key];
					key = parts.pop();
				}

				if(field.hasAttribute("type") && "checkbox" == field.type) {
					if(field.checked) {
						destination[key] = true;
					} else {
						destination[key] = false;
					}
				} else {
					destination[key] = field.value;
				}
			}
		}

		return data;
	}

	/**
	 * Callback that handles adding an api key to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_api_key(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		APIKey.create(data).then(_ => {
			this.navigate("/api-keys");
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles deleting an api key from the backend. Bound to the
	 * delete button in the View API Key view [views/admin/api-keys/view.mst] 
	 * 
	 * @param {string} id - the numeric id as a tring of the key to delete
	 */
	delete_api_key(id) {
		if(window.confirm("Are you sure you want to delete the API key")) { 
			fetch("/api/api-keys.php?id=" + id, {
				credentials: "include",
				method: "DELETE"
			}).then(response => {
				if(response.ok) {
					moostaka.navigate("/api-keys");
				} else if(403 == response.status) {
					throw new SessionTimeOutError();
				}

				throw "API was unable to save new API key";
			}).catch(handleError);
		}
	}

	/**
	 * Callback that handles updating cards on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_api_key(key, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/api-keys.php?id=" + key.id, {
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

			throw "API was unable to save API key";
		}).then(_data => {
			moostaka.navigate("/api-keys");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Callback that handles adding a card to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_card(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		Card.create(data).then(_ => {
			this.navigate("/cards");
		}).catch(e => this.handleError(e));
	}

	list_cards(params, auth_level) {
		let url = "/api/cards.php";

		// if('card_id' in params) {
		//     url += '?search=';
		//     url += encodeURIComponent(params.card_id);
		// }

		fetch(url, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list cards";
		}).then(cards => {
			moostaka.render("#main", auth_level + "/cards/list", {"cards": cards});
		}).catch(handleError);
	}

	/**
	 * Callback that handles updating cards on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_card(card, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/cards.php?id=" + card.id, {
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

			throw "API was unable to save card";
		}).then(_data => {
			moostaka.navigate("/cards");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Callback that handles updating charges on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_charge(charge, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/charges.php?id=" + charge.id, {
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

			throw "API was unable to save charge";
		}).then(_data => {
			moostaka.navigate("/charges");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Callback that handles adding equipment to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_equipment(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		Equipment.create(data).then(_data => {
			this.navigate("/equipment");
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating equipment on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_equipment(equipment, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/equipment.php?id=" + equipment.id, {
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

			throw "API was unable to save equipment";
		}).then(_data => {
			moostaka.navigate("/equipment");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Callback that handles adding an equipment type to the backend.
	 * Bound to the form.submit() in moostaka.render() for the view
	 */
	add_equipment_type(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		EquipmentType.create(data).then(_ => {
			this.navigate("/equipment-types");

		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating an equipment type on backend.
	 * Bound to the form.submit() in moostaka.render() for the view.
	 */
	update_equipment_type(type, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/equipment-types.php?id=" + type.id, {
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

			throw "API was unable to save equipment type";
		}).then(_data => {
			moostaka.navigate("/equipment-types");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Callback that handles adding a location to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_location(event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

		Location.create(data).then(_ => {
			this.navigate("/locations");
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating a location on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_location(location, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/locations.php?id=" + location.id, {
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

			throw "API was unable to save location";
		}).then(_data => {
			moostaka.navigate("/locations");
			// notify user of success
		}).catch(handleError);
	}

	/**
	 * Retrieve the log and optionally filter it. By default, a filter is applied
	 * to limit the log to just the past week
	 * 
	 * @param {Object} search - a dictionary of filters (keys and the value to
	 *     use when filtering)
	 */
	list_log(search) {
		if(0 < Object.keys(search).length) {
			search.customized = true;
		} else {
			// we will inject a minimal search so we don't pull the entire log by default
			let oneWeekAgo = new Date();
			oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
			search["after"] = oneWeekAgo.toISOString();
		}
		let queryString = Object.keys(search).map(key => key + '=' + search[key]).join('&');

		let p0 = fetch("/api/logs.php?" + queryString, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to retrieve specified log segment";
		});
		let p1 = fetch("/api/equipment.php", {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list equipment";
		});
		let p2 = fetch("/api/locations.php", {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list locations";
		});
		
		Promise.all([p0, p1, p2]).then(values => {
			moostaka.render("#main", "admin/logs/list", {"search":search, "log_messages":values[0], "equipment":values[1], "locations":values[2], "queryString":queryString}, {}, () => {
				//fix up selects
				if(search.hasOwnProperty("equipment_id")) {
					document.getElementById("equipment_id").value = search.equipment_id;
				}
				if(search.hasOwnProperty("location_id")) {
					document.getElementById("location_id").value = search.location_id;
				}
			});
		}).catch(handleError);
	}

	/**
	 * Retrieves log as currently filtered in csv format and allows user
	 * to save as a CSV file
	 */
	save_log(search) {
		let url = '/api/logs.php?' + search;

		fetch(url, {
			credentials: "include",
			headers: {
				"Accept": "text/csv"
			}
		}).then(response => {
			if(response.ok) {
				return response.text();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to create report from log";
		}).then(data => {
			let blob = new Blob([data], {type: "text/csv;charset=utf-8"});
			saveAs(blob, "log.csv");    // provided by Eli Grey's FileSaver.js
		}).catch(handleError);
	}

	/**
	 * Called when the search form inputs change. Determines if the form represents
	 * a search and if so calls list_log to runthe search and display the results
	 * 
	 * @param {HTMLFormElement} search_form - the form encapsulating the inputs
	 *     which the user has used to indicate how they wishthe log to be searched/
	 *     filtered
	 */
	search_log(search_form) {
		// look at search params to insure we have a search
		let search = {};
		let searchParams = get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length) {
				search[k] = searchParams[k];
			}
		}
		
		if(0 < Object.keys(search).length) {
			list_log(search);
		}
	}

	/**
	 * Callback that handles adding a payment to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_payment(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		Payment.create(data).then(data => {
			this.navigate("/users/" + data.user_id);
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles confirming a payment. Bound to the form.submit()
	 * in moostaka.render() for the view
	 */
	confirm_payment(event) {
		event.preventDefault();
		let payment = get_form_data(event.target);

		moostaka.render("#main", "admin/users/confirm_payment", {"payment": payment}, {}, () => {
			let form = document.getElementById("confirm-payment-form");
			form.addEventListener("submit", (e) => { add_payment(e); });
		});
	}

	/**
	 * Callback that handles adding a user to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 */
	add_user(event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		User.create(data).then(_ => {
			this.navigate("/users");
		}).catch(e => this.handleError(e));
	}

	/**
	 * Render an optionally sorted list of users
	 */
	list_users(params, auth_level) {
		let url = "/api/users.php?";

		let queryString = Object.keys(params).map(key => key + '=' + params[key]).join('&');

		fetch(url + queryString, {"credentials": "same-origin"}).then(response => {
			if(response.ok) {
				return response.json();
			} else if(403 == response.status) {
				throw new SessionTimeOutError();
			}

			throw "API was unable to list users";
		}).then(users => {
			if(0 < Object.keys(params).length) {
				params.customized = true;
			}
			moostaka.render("#main", auth_level + "/users/list", {"users": users, "search":params});
		}).catch(handleError);
	}

	search_users(search_form, auth_level) {
		// look at search params to insure we have a search
		let search = {};
		let searchParams = get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length || ("boolean" == typeof(searchParams[k]) && searchParams[k])) {
				search[k] = searchParams[k];
			}
		}

		if(0 < Object.keys(search).length) {
			list_users(search, auth_level);
		}
	}

	sort_users(sort_column, auth_level) {
		let search_form = document.getElementById('user_search_form');
		// look at search params to insure we have a search
		let search = {};
		let searchParams = get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length || ("boolean" == typeof(searchParams[k]) && searchParams[k])) {
				search[k] = searchParams[k];
			}
		}

		search.sort = sort_column;
		list_users(search, auth_level);
	}

	/**
	 * Callback that handles updating a user on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_user(user, event) {
		event.preventDefault();
		let data = get_form_data(event.target);

		fetch("/api/users.php?id=" + user.id, {
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

			throw "API was unable to save user";
		}).then(_data => {
			moostaka.navigate("/users");
			// notify user of success
		}).catch(handleError);
	}
}

export { Application };
