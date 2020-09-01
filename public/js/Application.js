import { SessionTimeOutError } from './SessionTimeOutError.js';
import { APIKey } from './APIKey.js';
import { ChargePolicy } from './ChargePolicy.js';
import { Equipment } from './Equipment.js';
import { EquipmentType } from './EquipmentType.js';
import { Location } from './Location.js';
import { LoggedEvent } from './LoggedEvent.js';
import { Payment } from './Payment.js';
import { Role } from './Role.js';
import { User } from './User.js';

import * as Permission from './Permission.js';

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

		// User needs CREATE_API_KEY Permission to make use of /location/add route
		if(this.user.has_permission(Permission.CREATE_API_KEY)) {
			this.route("/api-keys/add", _ => {
				this.render("#main", "authenticated/api-keys/add", {}, {}, () => {
					document
						.getElementById("add-api-key-form")
						.addEventListener("submit", (e) => this._add_api_key(e) );
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_API_KEYS Permission to make use of /api-keys route
		if(this.user.has_permission(Permission.LIST_API_KEYS)) {
			if(!home_icons.system) { home_icons.system = system_icons }
			home_icons.system.api_keys = true;
			this.route("/api-keys", _ => {
				APIKey.list().then(keys => {
					this.render("#main", "admin/api-keys/list", {"keys": keys});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_API_KEY to make use of /api-keys/id
		if(this.user.has_permission(Permission.READ_API_KEY)) {
			// User needs MODIFY_API_KEY to make use of /api-keys/id for editing
			this.route("/api-keys/:id", params => this.read_api_key(params.id, this.user.has_permission(Permission.MODIFY_API_KEY), this.user.has_permission(Permission.DELETE_API_KEY)));
		}

		// User needs CREATE_EQUIPMENT Permission to make use of /equipment/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT)) {
			this.route("/equipment/add", _ => {
				let p1 = EquipmentType.list();
				let p2 = Location.list();

				Promise.all([p1, p2]).then(values => {
					this.render("#main", "admin/equipment/add", {"types": values[0], "locations": values[1]}, {}, () => {
						document
							.getElementById("add-equipment-form")
							.addEventListener("submit", (e) => { this.add_equipment(e); });
					});
				}).catch(e => this.handleError(e));
			});
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

		// User needs READ_EQUIPMENT to make use of /api-keys/id
		if(this.user.has_permission(Permission.READ_EQUIPMENT)) {
			// User needs MODIFY_EQUIPMENT to make use of /api-keys/id for editing
			this.route("/equipment/:id", params => this.read_equipment(params.id, this.user.has_permission(Permission.MODIFY_EQUIPMENT)));
		}

		// User needs CREATE_EQUIPMENT_TYPE Permission to make use of /equipment-types/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT_TYPE)) {
			this.route("/equipment-types/add", _ => {
				ChargePolicy.list().then(charge_policies => {
					this.render("#main", "authenticated/equipment-types/add", {"charge_policies":charge_policies}, {}, () => {
						document
							.getElementById("add-equipment-type-form")
							.addEventListener("submit", (e) => this.add_equipment_type(e) );
					});
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

		// User needs READ_EQUIPMENT_TYPE to make use of /equipment-types/id
		if(this.user.has_permission(Permission.READ_EQUIPMENT_TYPE)) {
			// User needs MODIFY_EQUIPMENT_TYPE to make use of /equipment-types/id for editing
			this.route("/equipment-types/:id", params => this.read_equipment_type(params.id, this.user.has_permission(Permission.MODIFY_EQUIPMENT_TYPE)));
		}

		// User needs CREATE_LOCATION Permission to make use of /locations/add route
		if(this.user.has_permission(Permission.CREATE_LOCATION)) {
			this.route("/locations/add", _ => {
				this.render("#main", "authenticated/locations/add", {}, {}, () => {
					document
						.getElementById("add-location-form")
						.addEventListener("submit", (e) => this.add_location(e) );
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

		// User needs READ_LOCATION to make use of /locations/id
		if(this.user.has_permission(Permission.READ_LOCATION)) {
			// User needs MODIFY_LOCATION to make use of /locations/id for editing
			this.route("/locations/:id", params => this.read_location(params.id, this.user.has_permission(Permission.MODIFY_LOCATION)));
		}

		// User needs LIST_LOGS Permission to make use of /locations route
		if(this.user.has_permission(Permission.LIST_LOGS)) {
			if(!home_icons.reports) { home_icons.reports = report_icons }
			home_icons.reports.logs = true;
			this.route("/logs", _ => this.list_log({}));
		}

		// User needs CREATE_ROLE Permission to make use of /roles/add route
		if(this.user.has_permission(Permission.CREATE_ROLE)) {
			this.route("/roles/add", _ => {
				Permission.list().then(permissions => {
					this.render("#main", "authenticated/roles/add", { "possible_permissions":permissions }, {}, () => {
						document
							.getElementById("add-role-form")
							.addEventListener("submit", (e) => this._add_role(e) );
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_ROLES Permission to make use of /roles route
		if(this.user.has_permission(Permission.LIST_ROLES)) {
			if(!home_icons.system) { home_icons.system = system_icons }
			home_icons.system.roles = true;
			this.route("/roles", _ => {
				Role.list().then(roles => {
					this.render("#main", "authenticated/roles/list", {"roles":roles});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_ROLE to make use of /roles/id
		if(this.user.has_permission(Permission.READ_ROLE)) {
			// User needs MODIFY_ROLE to make use of /roles/id for editing
			this.route("/roles/:id", params => this._read_role(params.id, this.user.has_permission(Permission.MODIFY_ROLE), this.user.has_permission(Permission.DELETE_ROLE)));
		}

		// User needs CREATE_USER Permission to make use of /users/add route
		if(this.user.has_permission(Permission.CREATE_USER)) {
			this.route("/users/add", _ => {
				let p0 = EquipmentType.list();
				let p1 = Role.list();

				Promise.all([p0,p1]).then(values => {
					this.render("#main", "authenticated/users/add", {"equipment_types":values[0], "roles":values[1]}, {}, () => {
						document
							.getElementById("add-user-form")
							.addEventListener("submit", (e) => this._add_user(e) );
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_USERS Permission to make use of /users route
		if(this.user.has_permission(Permission.LIST_USERS)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.users = true;
			this.route("/users", _ => {
				User.list().then(users => {
					this.render("#main", "authenticated/users/list", {"users": users});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_USER to make use of /users/id
		if(this.user.has_permission(Permission.READ_USER)) {
			// User needs MODIFY_USER to make use of /users/id for editing user attributes eg email address
			// User needs CREATE_EQUIPMENT_AUTHORIZATION or DELETE_EQUIPMENT_AUTHORIZATION to manage authorizations
			this.route("/users/:id", params => this._read_user(params.id, this.user.has_permission(Permission.MODIFY_USER), this.user.has_permission(Permission.CREATE_EQUIPMENT_AUTHORIZATION) | this.user.has_permission(Permission.DELETE_EQUIPMENT_AUTHORIZATION)));
		}

		// Everyone gets a home route; what it presents them is controlled by home_icons
		this.route("/", _ => {
			this.render("#main", "authenticated/top-menu", {"features":home_icons});
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
	 * of the fields with a name attribute.
	 * 
	 * If name is of the form
	 * "foo.bar" then the value will be nested as ret["foo"]["bar"]
	 * 
	 * @param HTMLFormElement form - the form from which to retrieve data
	 * @return Object - an object with an attribute for each form input or
	 *     group of inputs.
	 */
	_get_form_data(form) {
		// should check that form is a form

		let data = {};
		for(let i = 0, len = form.elements.length; i < len; i++) {
			let field = form.elements[i];
			if(field.hasAttribute("name")) {
				let parts = field.name.split('.').reverse();
				let key = parts.pop();
				if(1 == parts.length && field.hasAttribute("type") && "checkbox" == field.type) {
					if(undefined === data[key]) {
						data[key] = [];
					}
					if(field.checked) {
						data[key].push(parts.pop());
					}
				} else {
					// checkboxes are weird they have a checked property
					if(field.hasAttribute("type") && "checkbox" == field.type) {
						if(field.checked) {
							data[key] = true;
						} else {
							data[key] = false;
						}
					} else {
						// text inputs, selects, radio buttons have a value property
						data[key] = field.value;
					}
				}
			}
		}

		return data;
	}

	/**
	 * Callback that handles adding an api key to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	_add_api_key(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		APIKey.create(data).then(_ => {
			this.navigate("/api-keys");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles deleting an api key from the backend. Bound to the
	 * delete button in the View API Key view [views/admin/api-keys/view.mst] 
	 * 
	 * @param {string} id - the numeric id as a tring of the key to delete
	 */
	_delete_api_key(id) {
		if(window.confirm("Are you sure you want to delete the API key")) { 
			APIKey.delete(id).then(_ => {
				this.navigate("/api-keys")
			}).catch(e => this.handleError(e));
		}
	}

	/**
	 * Helper method to view an api key.
	 *
	 * @param {Integer} id - the unique id of the api key to view
	 * @param {bool} editable - whether to show controls for editing the api key.
	 * @param {bool} deletable - whether to show controls for deleting the api key.
	 */
	read_api_key(id, editable, deletable) {
		APIKey.read(id).then(key => {
			this.render("#main", "authenticated/api-keys/view", {"key":key, "editable":editable, "deletable":deletable}, {}, () => {
				document
					.getElementById("edit-api-key-form")
					.addEventListener("submit", (e) => { this.update_api_key(id, e); });
				document
					.getElementById("delete-api-key-button")
					.addEventListener("click", _ => { this._delete_api_key(id); });
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating cards on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the location to modify
	 * @param {Event} event - the form submission event
	 */
	update_api_key(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		APIKey.modify(id, data).then(_ => {
			this.navigate("/api-keys");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding a card to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_card(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Card.create(data).then(_ => {
			this.navigate("/cards");
			// notify user of success
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
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating cards on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_card(card, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

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
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating charges on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_charge(charge, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

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
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding equipment to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_equipment(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Equipment.create(data).then(_data => {
			this.navigate("/equipment");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Helper method to view an equipment.
	 *
	 * @param {Integer} id - the unique id of the equipment to view
	 * @param {bool} editable - whether to show controls for editing the equipment.
	 */
	read_equipment(id, editable) {
		let p0 = Equipment.read(id);
		let p1 = EquipmentType.list();
		let p2 = Location.list();

		Promise.all([p0, p1, p2]).then(values => {
			let equipment = values[0];
			equipment["service_hours"] = Math.floor(equipment["service_minutes"] / 60) + "h " + equipment["service_minutes"] % 60 + "min";
			this.render("#main", "authenticated/equipment/view", {"equipment": equipment, "types": values[1], "locations": values[2], "editable":editable}, {}, () => {
				document.getElementById("type_id").value = values[0].type_id;
				document.getElementById("location_id").value = values[0].location_id;
				document.getElementById("edit-equipment-form").addEventListener("submit", (e) => { this.update_equipment(id, e); });
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating equipment on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the equipment to modify
	 * @param {Event} event - the form submission event
	 */
	update_equipment(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Equipment.modify(id, data).then(_ => {
			this.navigate("/equipment");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding an equipment type to the backend.
	 * Bound to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_equipment_type(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		EquipmentType.create(data).then(_ => {
			this.navigate("/equipment-types");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Helper method to view an equipment type.
	 *
	 * @param {Integer} id - the unique id of the equipment type to view
	 * @param {bool} editable - whether to show controls for editing the equipment type.
	 */
	read_equipment_type(id, editable) {
		let p0 = EquipmentType.read(id);
		let p1 = ChargePolicy.list();

		Promise.all([p0,p1]).then(values => {
			let type = values[0];
			this.render("#main", "admin/equipment-types/view", {"type":type, "charge_policies":values[1], "editable": editable}, {}, () => {
				document.getElementById("charge_policy_id").value = type.charge_policy_id;
				document
					.getElementById("edit-equipment-type-form")
					.addEventListener("submit", (e) => { this.update_equipment_type(id, e); });
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating an equipment type on backend.
	 * Bound to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the location to modify
	 * @param {Event} event - the form submission event
	 */
	update_equipment_type(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		EquipmentType.modify(id, data).then(_ => {
			this.navigate("/equipment-types");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding a location to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 *  @param {Event} event - the form submission event
	 */
	add_location(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Location.create(data).then(_ => {
			this.navigate("/locations");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Helper method to view a location.
	 *
	 * @param {Integer} id - the unique id of the location to view
	 * @param {bool} editable - whether to show controls for editing the location.
	 */
	read_location(id, editable) {
		let p0 = Location.read(id);
		let p1 = Equipment.list("location_id=" + id);
		
		Promise.all([p0,p1]).then(values => {
			this.render("#main", "authenticated/locations/view", {"location": values[0], "equipment": values[1], "editable": editable}, {}, () => {
				document
					.getElementById("edit-location-form")
					.addEventListener("submit", (e) => { this.update_location(id, e); });
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating a location on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the location to modify
	 * @param {Event} event - the form submission event
	 */
	update_location(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Location.modify(id, data).then(_ => {
			this.navigate("/locations");
			// notify user of success
		}).catch(e => this.handleError(e));
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

		let p0 = LoggedEvent.list(queryString);
		let p1 = Equipment.list();
		let p2 = Location.list();
		
		Promise.all([p0, p1, p2]).then(values => {
			this.render("#main", "authenticated/logs/list", {"search":search, "log_messages":values[0], "equipment":values[1], "locations":values[2], "queryString":queryString}, {}, () => {
				//fix up selects
				if(search.hasOwnProperty("equipment_id")) {
					document.getElementById("equipment_id").value = search.equipment_id;
				}
				if(search.hasOwnProperty("location_id")) {
					document.getElementById("location_id").value = search.location_id;
				}
			});
		}).catch(e => this.handleError(e));
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
		}).catch(e => this.handleError(e));
	}

	/**
	 * Called when the search form inputs change. Determines if the form represents
	 * a search and if so calls list_log to runthe search and display the results
	 * 
	 * @param {HTMLFormElement} search_form - the form encapsulating the inputs
	 *     which the user has used to indicate how they wish the log to be searched/
	 *     filtered
	 */
	search_log(search_form) {
		// look at search params to insure we have a search
		let search = {};
		let searchParams = this._get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length) {
				search[k] = searchParams[k];
			}
		}
		
		if(0 < Object.keys(search).length) {
			this.list_log(search);
		}
	}

	/**
	 * Callback that handles adding a payment to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_payment(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Payment.create(data).then(data => {
			this.navigate("/users/" + data.user_id);
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles confirming a payment. Bound to the form.submit()
	 * in moostaka.render() for the view
	 */
	confirm_payment(event) {
		event.preventDefault();
		let payment = this._get_form_data(event.target);

		moostaka.render("#main", "admin/users/confirm_payment", {"payment": payment}, {}, () => {
			document
				.getElementById("confirm-payment-form")
				.addEventListener("submit", (e) => { add_payment(e); });
		});
	}

	/**
	 * Callback that handles adding a role to the backend. Bound to the
	 * form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	_add_role(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Role.create(data).then(_ => {
			this.navigate("/roles");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles deleting a role from the backend. Bound to the
	 * delete button in the View API Key view [views/admin/roles/view.mst] 
	 * 
	 * @param {string} id - the numeric id as a tring of the key to delete
	 */
	// _delete_role(id) {
	// 	if(window.confirm("Are you sure you want to delete the Role")) { 
	// 		Role.delete(id).then(_ => {
	// 			this.navigate("/roles")
	// 		}).catch(e => this.handleError(e));
	// 	}
	// }

	/**
	 * Helper method to view a role.
	 *
	 * @param {Integer} id - the unique id of the role to view
	 * @param {bool} editable - whether to show controls for editing the role.
	 * @param {bool} deletable - whether to show controls for deleting the role.
	 */
	_read_role(id, editable, deletable) {
		let p0 = Role.read(id);
		let p1 = Permission.list();

		Promise.all([p0, p1]).then(values => {
			let role = values[0];
			let permissions = role.permissions;
			role.permissions = permissions.sort((a, b) => a - b).map(p => Permission.name_for_permission(p));

			this.render("#main", "authenticated/roles/view", {"role":role, "possible_permissions":values[1], "editable":editable, "deletable":deletable}, {}, () => {
				for(const permission of permissions) {
					document.getElementById("permissions." + permission).checked = true;
				}
				document
					.getElementById("edit-role-form")
					.addEventListener("submit", (e) => { this._update_role(id, e); });
				// document
				// 	.getElementById("delete-role-button")
				// 	.addEventListener("click", _ => { this._delete_role(id); });
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating roles on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the role to modify
	 * @param {Event} event - the form submission event
	 */
	_update_role(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		Role.modify(id, data).then(_ => {
			this.navigate("/roles");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding a user to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	_add_user(event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		User.create(data).then(_ => {
			this.navigate("/users");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles authorizing a user on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the user to authorize
	 * @param {Event} event - the form submission event
	 */
	_authorize_user(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		User.authorize(id, data).then(_ => {
			this.navigate("/users/" + id);
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Helper method to view a user.
	 *
	 * @param {Integer} id - the unique id of the user to view
	 * @param {bool} editable - whether to show controls for editing the user.
	 * @param {bool} authorizable - whether to show controls for authorizing the user.
	 */
	_read_user(id, editable, authorizable) {
		let p0 = User.read(id);
		let p1 = EquipmentType.list();
		let p2 = Role.list();

		Promise.all([p0,p1,p2]).then(values => {
			let user = values[0];
			let equipment_types = values[1];
			let roles = values[2].filter(role => "unauthenticated" != role.name);
			let authorized_equipment_types = equipment_types.filter(type => user.authorizations.includes(type.id));
			this.render("#main", "authenticated/users/view", {"user":user, "equipment_types":equipment_types, "roles":roles, "authorized_equipment_types":authorized_equipment_types, "editable": editable, "authorizable":authorizable}, {}, () => {
				let selector = document.getElementById("role_id");
				if(selector) {
					selector.value = user.role.id;
				}
				for(const authorization of user.authorizations) {
					document.getElementById("authorizations." + authorization).checked = true;
				}
				let form = null;
				form = document.getElementById("edit-user-form");
				if(form) {
					form.addEventListener("submit", (e) => { this._update_user(id, e); });
				}
				form = document.getElementById("authorize-user-form");
				if(form) {
					form.addEventListener("submit", (e) => { this._authorize_user(id, e); });
				}
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles updating a user on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the user to modify
	 * @param {Event} event - the form submission event
	 */
	_update_user(id, event) {
		event.preventDefault();
		let data = this._get_form_data(event.target);

		User.modify(id, data).then(_ => {
			this.navigate("/users");
			// notify user of success
		}).catch(e => this.handleError(e));
	}
}

export { Application };
