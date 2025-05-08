import { SessionTimeOutError } from './SessionTimeOutError.js';
import { APIKey } from './APIKey.js';
import { Card } from './Card.js';
import { Charge } from './Charge.js';
import { ChargePolicy } from './ChargePolicy.js';
import { Equipment } from './Equipment.js';
import { EquipmentType } from './EquipmentType.js';
import { Location } from './Location.js';
import { LoggedEvent } from './LoggedEvent.js';
import { Payment } from './Payment.js';
import { Role } from './Role.js';
import { User } from './User.js';
import { CardType } from './CardType.js';

import * as Permission from './Permission.js';

/*
 * In Javascript NULL and undefined values do not respond to .toLowerCase()
 * in fact calling .toLowerCase() on NULL will raise an error stopping
 * script execution which is problematic. lowerCase() is therefore a cover
 * for .toLowerCase() which checks that the value is not falsy before invoking
 * .toLowerCase()
 */
function lowerCase(value) {
	if (value) {
		return value.toLowerCase();
	}
	return value;
}

class Application {
	user = null;

	/**
	 * Instantiate the SPA
	 *
	 * @param {Object} opts - a dictionary of options. Supported keys are:
	 *        {string} defaultRoute - the default route. defaults to '/'
	 *        {string} viewLocation - a url fragment for locating page
	 *                templates. defaults to '/views'
	 */
	constructor(opts) {
		this.routes = [];

		// define the defaults
		this.defaultRoute = "/";
		this.viewLocation = "/views";

		// redirect to default route if none defined
		if (location.pathname === "") {
			history.pushState("data", this.defaultRoute, this.defaultRoute);
		}

		// override the defaults
		if (opts) {
			this.defaultRoute =
				typeof opts.defaultRoute !== "undefined"
					? opts.defaultRoute
					: this.defaultRoute;
			this.viewLocation =
				typeof opts.viewLocation !== "undefined"
					? opts.viewLocation
					: this.viewLocation;
		}

		let self = this;
		// hook up events
		document.addEventListener(
			"click",
			(event) => {
				if (event.target.matches("a[href], a[href] *")) {
					// walk up the tree until we find the href
					let element = event.target;
					while (element instanceof HTMLElement) {
						if (element.href) {
							if (
								element.href.startsWith(location.host) ||
								element.href.startsWith(
									location.protocol + "//" + location.host,
								)
							) {
								// staying in application
								event.preventDefault();
								let url = element.href
									.replace("http://", "")
									.replace("https://", "")
									.replace(location.host, "");
								let text = document.title;
								if (
									element.title instanceof String &&
									element.title != ""
								) {
									text = element.title;
								} else if (
									event.target.title instanceof String &&
									event.target.title != ""
								) {
									text = event.target.title;
								}
								history.pushState({}, text, element.href);
								self.navigate(url);
							} // else do nothing so browser does the default thing ie browse to new location
							return;
						} else {
							element = element.parentElement;
						}
					}
				} // else not a link; ignore
			},
			false,
		);

		// pop state
		window.onpopstate = (e) => {
			self.navigate(location.pathname);
		};
	}

	/**
	 * handle navigation events routing to a "route" if one is defined which
	 * matches the specified pathname
	 *
	 * @param {string} pathname the uri to change to
	 */
	navigate(pathname) {
		if (this.onnavigate) {
			this.onnavigate(pathname);
		}

		// if no path, go to default
		if (!pathname || pathname === "/") {
			pathname = this.defaultRoute;
		}

		let hashParts = pathname.split("/");
		let params = {};
		for (let i = 0, len = this.routes.length; i < len; i++) {
			if (typeof this.routes[i].pattern === "string") {
				let routeParts = this.routes[i].pattern.split("/");
				let thisRouteMatch = true;

				// if segment count is not equal the pattern will not match
				if (routeParts.length !== hashParts.length) {
					thisRouteMatch = false;
				} else {
					for (let x = 0; x < routeParts.length; x++) {
						// A wildcard is found, lets break and return what we have already
						if (routeParts[x] === "*") {
							break;
						}

						// if not optional params we check it
						if (routeParts[x].substring(0, 1) !== ":") {
							if (
								lowerCase(routeParts[x]) !==
								lowerCase(hashParts[x])
							) {
								thisRouteMatch = false;
							}
						} else {
							// this is an optional param that the user will want
							let partName = routeParts[x].substring(1);
							params[partName] = hashParts[x];
						}
					}
				}

				// if route is matched
				if (thisRouteMatch === true) {
					return this.routes[i].handler(params);
				}
			} else {
				if (pathname.substring(1).match(this.routes[i].pattern)) {
					return this.routes[i].handler({
						hash: pathname.substring(1).split("/"),
					});
				}
			}
		}

		// no routes were matched. try defaultRoute if that is not the route being attempted
		if (this.defaultRoute != pathname) {
			history.pushState("data", "home", this.defaultRoute);
			this.navigate(this.defaultRoute);
		} // else we should perhaps implement an error route?
	}

	/**
	 * Render a template into a specified HTMLElement node
	 *
	 * @param {HTMLElement|string} an element node or a string specifying an
	 *      HTMLElement node e.g. '#content' for <div id="content"></div>
	 * @param {string} view - the name of an .mst file in the views location
	 *      to use as a template
	 * @param {?object} params - additional parameters for the Mustache template
	 *      renderer
	 * @param {?object} options - a dictionary of options. supported keys:
	 *      {array<string>} tags - a list of delimiters for Mustache to use
	 *      {bool} append - if true then new content to the element's contents
	 *            otherwise replace the contents of element with the render
	 *            output. Default: false
	 * @param {?function} callback - a callback function to invoke once the
	 *      template has been rendered into place
	 */
	render(element, view, params, options, callback) {
		let destination = null;
		if (element instanceof HTMLElement) {
			destination = element;
		} else {
			destination = document.querySelector(element);
		}
		if (!params) {
			params = {};
		}
		if (!options) {
			options = {};
		}
		if (typeof options.tags === "undefined") {
			Mustache.tags = ["{{", "}}"];
		} else {
			Mustache.tags = options.tags;
		}
		if (typeof options.append === "undefined") {
			options.append = false;
		}

		let url = this.viewLocation + "/" + view.replace(".mst", "") + ".mst";
		fetch(url)
			.then((response) => {
				return response.text();
			})
			.then((template) => {
				if (options.append === true) {
					destination.innerHTML += Mustache.render(template, params);
				} else {
					while (destination.firstChild) {
						destination.removeChild(destination.firstChild);
					}
					destination.innerHTML = Mustache.render(template, params);
				}
				if (callback instanceof Function) {
					callback();
				}
			});
	}

	/**
	 * Render a template and the as rendered template to the callback as a
	 * string parameter
	 *
	 * @param {string} view - the name of an .mst file in the views location
	 *      to use as a template
	 * @param {?object} params - additional parameters for the Mustache template
	 *      renderer
	 * @param {?object} options - a dictionary of options. supported keys:
	 *      {array<string>} tags - a list of delimiters for Mustache to use
	 *      {bool} markdown - if true and window.markdownit exists call
	 *          window.markdownit.render() on the template data before
	 *          rendering content with Mustache.
	 * @param {function} callback - a callback function to invoke once the
	 *      template has been rendered. The string value of the rendered
	 *      template will be passed to the function as its first and only
	 *      parameter
	 */
	getHtml(view, params, options, callback) {
		if (!(callback instanceof Function)) {
			throw new TypeError("callback is not a function");
		}
		if (!params) {
			params = {};
		}
		if (!options) {
			options = {};
		}
		if (typeof options.tags === "undefined") {
			Mustache.tags = ["{{", "}}"];
		} else {
			Mustache.tags = options.tags;
		}
		if (typeof options.markdown === "undefined") {
			options.markdown = false;
		}

		let url = this.viewLocation + "/" + view.replace(".mst", "") + ".mst";
		fetch(url)
			.then((response) => {
				return response.text();
			})
			.then((template) => {
				if (window.markdownit && options.markdown === true) {
					let md = window.markdownit();
					callback(Mustache.render(md.render(template), params));
				} else {
					callback(Mustache.render(template, params));
				}
			});
	}

	/**
	 * Add a route to the SPA
	 *
	 * @param {string} pattern - a pattern string that if matched to the URI
	 *      will cause the specified handler to be invoked
	 * @param {function} handler - a callback function to invoke when the user
	 *      navigates to this route.
	 */
	route(pattern, handler) {
		// should check if pattern is already routed and if so
		// throw error or overwrite?
		this.routes.push({ pattern: pattern, handler: handler });
	}

	/**
	 * clear all routes currently configured useful if transitioning between
	 * authenticated and unauthenticated states
	 */
	flush() {
		this.routes = [];
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
		this.route("/profile", _ => {
			// User should always be able to access their own profile
			this.loadProfilePage(this.user.id);
		});

		 // 3. Improved route handler to ensure profile page is not overridden
	  	if(this.user.has_permission(Permission.READ_OWN_USER)) {
			this.route("/profile", _ => {
		  		console.log("Profile route handler called");
		  
		  		// Don't use read_user - create a dedicated profile loading function
		  		this.loadProfilePage(this.user.id);
			});
	  	}

		// User needs CREATE_API_KEY Permission to make use of /api-keys/add route
		if(this.user.has_permission(Permission.CREATE_API_KEY)) {
			this.route("/api-keys/add", _ => {
				this.render("#main", "authenticated/api-keys/add", {}, {}, () => {
					document
						.getElementById("add-api-key-form")
						.addEventListener("submit", (e) => this.add_api_key(e));
				});
			});
		}

		// User needs LIST_API_KEYS Permission to make use of /api-keys route
		if(this.user.has_permission(Permission.LIST_API_KEYS)) {
			if(!home_icons.system) { home_icons.system = system_icons }
			home_icons.system.api_keys = true;
			this.route("/api-keys", _ => {
				APIKey.list().then(keys => {
					this.render("#main", "authenticated/api-keys/list", {"keys": keys});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_API_KEY to make use of /api-keys/id
		if(this.user.has_permission(Permission.READ_API_KEY)) {
			// User needs MODIFY_API_KEY to make use of /api-keys/id for editing
			this.route("/api-keys/:id", params => this.read_api_key(params.id, this.user.has_permission(Permission.MODIFY_API_KEY), this.user.has_permission(Permission.DELETE_API_KEY)));
		}

		// User needs CREATE_CARD Permission to make use of /cards/add route
		if(this.user.has_permission(Permission.CREATE_CARD)) {
			this.route("/cards/add", _ => {
				let p1 = CardType.list();
				let p2 = User.list();
				let p3 = EquipmentType.list();

				Promise.all([p1, p2, p3]).then(values => {
					this.render('#main', "authenticated/cards/add", {"types": values[0], "users": values[1], "equipment_types": values[2]}, {}, () => {
						let form = document.getElementById("add-card-form");
						form.addEventListener("submit", (e) => { app.add_card(e); });
						let equipment_type_selector_label = document.getElementById("equipment_type_id_label");
						let equipment_type_selector = document.getElementById("equipment_type_id");
						let user_selector_label = document.getElementById("user_id_label");
						let user_selector = document.getElementById("user_id");
						let user_id_input = document.getElementById("user_id_input");
						equipment_type_selector_label.style.display = "none";
						equipment_type_selector.style.display = "none";
						equipment_type_selector.disabled = true;
						equipment_type_selector.required = false;
						user_selector_label.style.display = "none";
						user_selector.style.display = "none";
						user_id_input.style.display = "none";
						user_selector.disabled = true;
						user_selector.required = false;
						let type_id_selector = document.getElementById("type_id");
						type_id_selector.addEventListener('change', (event) => {
							this.card_options_selector(event.target.value);
						});
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_CARDS Permission to make use of /cards route
		if(this.user.has_permission(Permission.LIST_CARDS)) {
			if(!home_icons.manage_icons) { home_icons.manage_icons = manage_icons }
			home_icons.manage_icons.cards = true;
			this.route("/cards", _ => {
				Card.list().then(cards => {
					this.render("#main", "authenticated/cards/list", {"cards": cards});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_CARD to make use of /cards/id
		if(this.user.has_permission(Permission.READ_CARD)) {
			// User needs MODIFY_CARD to make use of /cards/id for editing
			this.route("/cards/:id", params => {
				this.read_card(params.id, this.user.has_permission(Permission.MODIFY_CARD));
			});
		}

		// User needs CREATE_EQUIPMENT Permission to make use of /equipment/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT)) {
			this.route("/equipment/add", _ => {
				let p1 = EquipmentType.list();
				let p2 = Location.list();

				Promise.all([p1, p2]).then(values => {
					this.render("#main", "authenticated/equipment/add", {"types": values[0], "locations": values[1]}, {}, () => {
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
					this.render(
						"#main",
						"authenticated/equipment/list",
						{
							"equipment": equipment,
							"search":search,
							"create_equipment_permission": this.user.has_permission(Permission.CREATE_EQUIPMENT)
						},
						{},
						() => {
							this.set_icon_colors(document);
						}
					);
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_EQUIPMENT to make use of /api-keys/id
		if(this.user.has_permission(Permission.READ_EQUIPMENT)) {
			// User needs MODIFY_EQUIPMENT to make use of /equipment/id for editing
			this.route("/equipment/:id", params => this.read_equipment(params.id, this.user.has_permission(Permission.MODIFY_EQUIPMENT)));
		}

		// User needs CREATE_EQUIPMENT_TYPE Permission to make use of /equipment-types/add route
		if(this.user.has_permission(Permission.CREATE_EQUIPMENT_TYPE)) {
			this.route("/equipment-types/add", _ => {
				ChargePolicy.list().then(charge_policies => {
					this.render("#main", "authenticated/equipment-types/add", {"charge_policies":charge_policies}, {}, () => {
						document
							.getElementById("add-equipment-type-form")
							.addEventListener("submit", (e) => this.add_equipment_type(e));
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
					this.render("#main", "authenticated/equipment-types/list", {"types": types, "create_equipment_type_permission": this.user.has_permission(Permission.CREATE_EQUIPMENT_TYPE)});
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
						.addEventListener("submit", (e) => this.add_location(e));
				});
			});
		}

		// User needs LIST_LOCATIONS Permission to make use of /locations route
		if(this.user.has_permission(Permission.LIST_LOCATIONS)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.locations = true;
			this.route("/locations", _ => {
				Location.list().then(locations => {
					this.render("#main", "authenticated/locations/list", {"locations": locations, "create_location_permission": this.user.has_permission(Permission.CREATE_LOCATION)});
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

		// User needs CREATE_USER Permission to make use of /users/add route
		if(this.user.has_permission(Permission.CREATE_PAYMENT)) {
			this.route("/users/:id/add_payment", params => {
				User.read(params.id).then(user => {
					this.render("#main", "authenticated/users/add_payment", {"user":user}, {}, () => {
						document
							.getElementById("add-payment-form")
							.addEventListener("submit", (e) => this.confirm_payment(user, e));
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs CREATE_ROLE Permission to make use of /roles/add route
		if(this.user.has_permission(Permission.CREATE_ROLE)) {
			this.route("/roles/add", _ => {
				Permission.list().then(permissions => {
					this.render("#main", "authenticated/roles/add", { "possible_permissions":permissions }, {}, () => {
						document
							.getElementById("add-role-form")
							.addEventListener("submit", (e) => this.add_role(e));
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_ROLES Permission to make use of /roles route
		if(this.user.has_permission(Permission.LIST_ROLES) && this.user.has_permission(Permission.VIEW_ROLES)) {
			if(!home_icons.system) { home_icons.system = system_icons }
			home_icons.system.roles = true;
			this.route("/roles", _ => {
				Role.list().then(roles => {
					this.render("#main", "authenticated/roles/list", {"roles":roles});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs READ_ROLE to make use of /roles/id
		if(this.user.has_permission(Permission.READ_ROLE) && this.user.has_permission(Permission.VIEW_ROLES)) {
			// User needs MODIFY_ROLE to make use of /roles/id for editing
			this.route("/roles/:id", params => this.read_role(params.id, this.user.has_permission(Permission.MODIFY_ROLE), this.user.has_permission(Permission.DELETE_ROLE)));
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
							.addEventListener("submit", (e) => this.add_user(e));
					});
				}).catch(e => this.handleError(e));
			});
		}

		// User needs LIST_USERS Permission to make use of /users route
		if(this.user.has_permission(Permission.LIST_USERS)) {
			if(!home_icons.manage) { home_icons.manage = manage_icons }
			home_icons.manage.users = true;
			this.route("/users", _ => {
				this.list_users({search: {}});
			});
		}

		// User needs READ_USER to make use of /users/id
		if(this.user.has_permission(Permission.READ_USER)) {
			// User needs MODIFY_USER to make use of /users/id for editing user attributes eg email address
			// User needs CREATE_EQUIPMENT_AUTHORIZATION or DELETE_EQUIPMENT_AUTHORIZATION to manage authorizations
			this.route("/users/:id", params => this.read_user(params.id, this.user.has_permission(Permission.MODIFY_USER),
				this.user.has_permission(Permission.CREATE_EQUIPMENT_AUTHORIZATION) | this.user.has_permission(Permission.DELETE_EQUIPMENT_AUTHORIZATION),
				this.user.has_permission(Permission.MODIFY_ROLE), this.user.has_permission(Permission.CREATE_PAYMENT)));
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
				this.render("#main", "unauthenticated/availability", {"equipment": equipment}, {}, () => {
					this.set_icon_colors(document);
				});
			}).catch(e => this.handleError(e));
		});
	}
	/**
	 * Improved loadProfilePage method to handle missing role permissions
	 */
	loadProfilePage(userId) {
		console.log("Loading profile page for user ID:", userId);
		
		// Get the user data
		let p0 = User.read(userId).catch(e => {
		console.error("Error reading user:", e);
		return null;
		});
		
		// Get equipment types - may fail but that's okay
		let p1 = EquipmentType.list().catch(e => {
		console.error("Error loading equipment types:", e);
		return []; // Return empty array on error
		});
		
		// For roles - instead of using Role.list() which requires permissions,
		// just use the current user's role information
		let p2 = Promise.resolve([this.user.role]);
		
		// Get charges and payments
		let p3 = Charge.list("user_id=" + userId).catch(e => {
		console.error("Error loading charges:", e);
		return []; // Return empty array on error
		});
		
		let p4 = Payment.list("user_id=" + userId).catch(e => {
		console.error("Error loading payments:", e);
		return []; // Return empty array on error
		});
	
		Promise.all([p0, p1, p2, p3, p4])
		.then(values => {
			if (!values[0]) {
			throw new Error("Failed to load user data");
			}
			
			console.log("Profile data loaded successfully");
			
			let currency_formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 });
			let date_formatter = new Intl.DateTimeFormat();
			let user = values[0];
			let equipment_types = values[1];
			let roles = values[2].filter(role => role && role.name !== "unauthenticated");
			
			// Handle case where authorizations might be undefined
			if (!user.authorizations) user.authorizations = [];
			
			let authorized_equipment_types = equipment_types.filter(type => 
			user.authorizations.includes(type.id));
		
			// Handle empty arrays to prevent reduce errors
			let total_charges = values[3].length ? 
			values[3].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0) : 0;
			
			let total_payments = values[4].length ? 
			values[4].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0) : 0;
			
			let balance = currency_formatter.format(Number(Math.round((total_payments - total_charges)+'e2')+'e-2'));
			
			let ledger = values[3].concat(values[4]).map(e => {
			e.ts = new Date(e.time);
			e.time = date_formatter.format(e.ts);
			return e;
			}).sort((a, b) => {
			return a.ts - b.ts;
			});
		
			ledger = ledger.reduce(function(new_ledger, transaction) {
			transaction.amount = parseFloat(transaction.amount);
			if("charge_policy" in transaction) {
				transaction.amount *= -1;
			}
		
			if(new_ledger.length > 0) {
				transaction.balance = new_ledger[new_ledger.length-1].balance + transaction.amount;
			} else {
				transaction.balance = transaction.amount;
			}
		
			new_ledger.push(transaction);
			return new_ledger;
			}, []).map((transaction) => {
			transaction.balance = currency_formatter.format(transaction.balance);
			transaction.amount = currency_formatter.format(transaction.amount);
			return transaction;
			});
		
			console.log("Rendering profile template");
			
			this.render("#main", "authenticated/profile", {
			"user": user,
			"equipment_types": equipment_types,
			"roles": roles,
			"authorized_equipment_types": authorized_equipment_types,
			"ledger": ledger,
			"balance": balance,
			"editable": true   // Profile is always editable by the owner
			}, {}, () => {
			// Set up form submission
			let form = document.getElementById("edit-user-form");
			if(form) {
				console.log("Setting up profile form submission handler");
				
				// Ensure we replace any existing handler
				const newForm = form.cloneNode(true);
				form.parentNode.replaceChild(newForm, form);
				form = newForm;
				
				form.addEventListener("submit", (e) => {
				e.preventDefault();
				this.updateProfile(userId, e);
				});
			}
			
			let transaction_button = document.getElementById("transaction-button");
			if(transaction_button) {
				transaction_button.addEventListener("click", (e) => {
				this.toggle_transactions();
				});
			}
			
			if(values[3].length + values[4].length > 20) {
				this.toggle_transactions();
			}
			
			this.set_icon_colors(document);
			});
		})
		.catch(e => {
			console.error("Error in loadProfilePage:", e);
			this.handleError(e);
		});
	}
	
	/**
	 * Updated updateProfile method to maintain role information
	 */
	updateProfile(userId, event) {
		console.log("Updating profile for user ID:", userId);
		
		// Get form data
		let data = this.get_form_data(event.target);
		console.log("Profile form data:", JSON.stringify(data));
		
		// Always use current role ID to prevent role changes via profile
		if (!data.role_id || userId === this.user.id) {
		console.log("Using current role_id");
		data.role_id = this.user.role.id;
		}
		
		if (data.is_active === undefined) {
		console.log("Setting default is_active to true");
		data.is_active = true;
		} else if (typeof data.is_active === 'string') {
		data.is_active = (data.is_active.toLowerCase() === 'true');
		}
		
		// Validate PIN
		if (data.pin && !/^\d{4}$/.test(data.pin)) {
		alert('PIN must be exactly 4 digits');
		return;
		}
		
		// For profile updates, preserve existing authorizations
		if (userId === this.user.id && !data.authorizations) {
		data.authorizations = this.user.authorizations || [];
		}
		
		// Use a direct fetch for more control
		fetch("/api/users.php?id=" + userId, {
		method: "POST",
		headers: {
			"Content-Type": "application/json"
		},
		credentials: "same-origin",
		body: JSON.stringify(data)
		})
		.then(response => {
		if (!response.ok) {
			return response.text().then(text => {
			throw new Error(`Server error: ${text}`);
			});
		}
		return response.json();
		})
		.then(result => {
		console.log("Profile update successful", result);
		alert("Profile updated successfully");
		
		// Stay on profile page
		this.navigate("/profile");
		})
		.catch(e => {
		console.error("Profile update failed:", e);
		alert("Failed to update profile: " + e.message);
		this.handleError(e);
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
	get_form_data(form) {
		// Check that form is a form element
		if (!(form instanceof HTMLFormElement)) {
		console.error("get_form_data called with non-form element:", form);
		return {};
		}
	
		let data = {};
		
		// First, extract role_id specifically if it exists
		// This is very important for the user form
		const roleIdElement = form.elements['role_id'];
		if (roleIdElement) {
		data.role_id = parseInt(roleIdElement.value, 10);
		console.log("Found role_id in form:", data.role_id);
		}
		
		// Process all form elements
		for(let i = 0, len = form.elements.length; i < len; i++) {
		let field = form.elements[i];
		
		// Skip elements without a name
		if(!field.hasAttribute("name") || !field.name) {
			continue;
		}
		
		// Skip role_id since we already processed it
		if(field.name === 'role_id') {
			continue;
		}
		
		let parts = field.name.split('.').reverse();
		let key = parts.pop();
		
		// Handle array-structured data (e.g., name.foo.bar)
		if(1 == parts.length && field.hasAttribute("type") && "checkbox" == field.type) {
			if(undefined === data[key]) {
			data[key] = [];
			}
			if(field.checked) {
			data[key].push(parseInt(parts.pop(), 10));
			}
		} else {
			// Handle different field types appropriately
			if(field.hasAttribute("type") && "checkbox" == field.type) {
			data[key] = field.checked;
			} else if(field.hasAttribute("type") && "hidden" == field.type) {
			// Make sure hidden fields are captured properly
			data[key] = field.value;
			} else {
			// Standard fields (text, select, etc.)
			data[key] = field.value;
			}
		}
		}
		
		console.log("Final form data:", JSON.stringify(data));
		
		return data;
	}

	/**
	 * Callback that handles adding an api key to the backend. Bound
	 * to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_api_key(event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

		APIKey.create(data).then(_ => {
			this.navigate("/api-keys");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles deleting an api key from the backend. Bound to the
	 * delete button in the View API Key view [views/admin/api-keys/view.mst]
	 *
	 * @param {string} id - the numeric id as a string of the key to delete
	 */
	delete_api_key(id) {
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
					.addEventListener("click", _ => { this.delete_api_key(id); });
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
		let data = this.get_form_data(event.target);

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
		let data = this.get_form_data(event.target);

		Card.create(data).then(_data => {
			this.navigate("/cards");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	read_card(id) {
		let p0 = Card.read(id);
		let p1 = CardType.list();
		let p2 = User.list();
		let p3 = EquipmentType.list();

		Promise.all([p0]).then(values => {
			this.render("#main", "authenticated/cards/view", {"card": values[0]});
		}).catch(e => this.handleError(e));
	}

	list_cards(params, auth_level) {
		let queryString = Object.keys(params).map(key => key + '=' + params[key]).join('&');

		Card.list(queryString).then(cards => {
			this.render('#main', "authenticated/cards/list", {"cards": cards});
		}).catch(e => this.handleError(e));
	}

	search_cards(search_form, auth_level) {
		let search = {};
		let searchParams = this.get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);

		for(let k of keys) {
			if(0 < searchParams[k].length || ("boolean" == typeof(searchParams[k]) && searchParams[k])) {
				search[k] = searchParams[k];
			}
		}

		if(0 < Object.keys(search).length) {
			this.list_cards({"search": search.card_id}, auth_level);
		}
	}

	/**
	 * Callback that handles updating charges on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 */
	update_charge(charge, event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

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
		let data = this.get_form_data(event.target);

		Equipment.list().then(equipment_list => {

			let contains = equipment_list.reduce((accumulator, equipment) => ((equipment.mac_address === data.mac_address) || accumulator), false);

			if(contains) {
				this.handleError(Error('Cannot save equipment. MAC address already exists.'));
			} else {
				Equipment.create(data).then(_data => {
					this.navigate("/equipment");
					// notify user of success
				}).catch(e => this.handleError(e));
			}
		});
	}

	/**
	 * Helper method to view an equipment.
	 *
	 * @param {Integer} id - the unique id of the equipment to view
	 * @param {bool} editable - whether to show controls for editing the equipment.
	 */
	read_equipment(id, editable) {

		let p1 = EquipmentType.list();
		let p2 = Location.list();
		let p3 = null;
		Equipment.read(id).then(value => {
			p3 = User.list("equipment_id="+value.type_id);

			Promise.all([p1, p2, p3]).then(values => {
				let equipment = value;
				equipment["service_hours"] = Math.floor(equipment["service_minutes"] / 60) + "h " + equipment["service_minutes"] % 60 + "min";
				let authorized_users = values[2];

				this.render("#main", "authenticated/equipment/view", {
					"equipment": equipment,
					"users": authorized_users,
					"types": values[0],
					"default_type" : values[0].find(type => type.id == value.type_id).name,
					"locations": values[1],
					"default_location" : values[1].find(location => location.id == value.location_id).name,

					"editable":editable}, {}, () => {

					// Commented out since they were preventing a default value from being shown
					// document.getElementById("type_id").value = values[0].type_id;
					// document.getElementById("location_id").value = values[0].location_id;

					document.getElementById("edit-equipment-form").addEventListener("submit", (e) => { this.update_equipment(id, e, equipment); });

					this.set_icon_colors(document);
				});
			})
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
		let data = this.get_form_data(event.target);


		Equipment.list().then(equipment_list => {
			equipment_list = equipment_list.filter((equipment) => (equipment.id != id));

			let contains = equipment_list.reduce((accumulator, equipment) => ((equipment.mac_address === data.mac_address) || accumulator), false);

			if(contains) {
				this.handleError(Error('Cannot save equipment. MAC address already exists.'));
			} else {
				Equipment.modify(id, data).then(_ => {
					history.pushState("", "", "/equipment");
					this.navigate("/equipment");
					// notify user of success
				}).catch(e => this.handleError(e));
			}
		});
	}

	/**
	 * Callback that handles adding an equipment type to the backend.
	 * Bound to the form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_equipment_type(event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

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
		let p2 = User.list("equipment_id="+id);

		Promise.all([p0,p1,p2]).then(values => {
			let type = values[0];
			this.render("#main", "authenticated/equipment-types/view", {
					"type":type,
					"charge_policies":values[1],
					"editable": editable,
					"users": values[2]
				}, {}, () => {
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
		let data = this.get_form_data(event.target);

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
		let data = this.get_form_data(event.target);

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
		let data = this.get_form_data(event.target);

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
		let p3 = EquipmentType.list();

		Promise.all([p0, p1, p2, p3]).then(values => {
			let equipment_type = null;
			if('equipment_type_id' in search) {
				equipment_type = values[3].filter(e => e.id == search.equipment_type_id)[0];
			}

			if(equipment_type != null) {
				values[1] = values[1].filter(e => e.type == equipment_type.name);
			}

			this.render("#main", "authenticated/logs/list", {"search":search, "log_messages":values[0], "equipment":values[1], "locations":values[2], "equipment-type": values[3], "queryString":queryString}, {}, () => {
				//fix up selects
				if(search.hasOwnProperty("equipment_id")) {
					document.getElementById("equipment_id").value = search.equipment_id;
				}
				if(search.hasOwnProperty("location_id")) {
					document.getElementById("location_id").value = search.location_id;
				}
				if(search.hasOwnProperty("equipment_type_id")) {
					document.getElementById("equipment_type_id").value = search.equipment_type_id;
				}
			});
		}).catch(e => this.handleError(e));
	}

	/**
	 * Retrieves log as currently filtered in csv format and allows user
	 * to save as a CSV file
	 */
	save(search) {
		// let url = '/api/logs.php?' + search;
		let url = '/api/' + search;

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
		let searchParams = this.get_form_data(search_form);
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
		let data = this.get_form_data(event.target);

		Payment.create(data).then(data => {
			this.navigate("/users/" + data.user_id);
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles confirming a payment. Bound to the form.submit()
	 * in moostaka.render() for the view
	 *
	 * @param {User} user - the user account for which a payment is being
	 *      confirmed.
	 * @param {Event} event - the form submission event
	 */
	confirm_payment(user, event) {
		event.preventDefault();
		let payment = this.get_form_data(event.target);

		this.render("#main", "authenticated/users/confirm_payment", {"user": user, "payment": payment}, {}, () => {
			document
				.getElementById("confirm-payment-form")
				.addEventListener("submit", (e) => { this.add_payment(e); });
		});
	}

	/**
	 * Callback that handles adding a role to the backend. Bound to the
	 * form.submit() in moostaka.render() for the view
	 *
	 * @param {Event} event - the form submission event
	 */
	add_role(event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

		Role.create(data).then(_ => {
			this.navigate("/roles");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles deleting a role from the backend. Bound to the
	 * delete button in the View API Key view [views/admin/roles/view.mst]
	 *
	 * @param {string} id - the numeric id as a string of the key to delete
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
	read_role(id, editable, deletable) {
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
					.addEventListener("submit", (e) => { this.update_role(id, e); });
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
	update_role(id, event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

		Role.modify(id, data).then(_ => {
			this.navigate("/roles");
			// notify user of success
		}).catch(e => this.handleError(e));
	}

	/**
	 * Callback that handles adding a user to the backend with enhanced error handling
	 *
	 * @param {Event} event - the form submission event
	 */
	add_user(event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);
		
		// Validate PIN format
		if (data.pin && !/^\d{4}$/.test(data.pin)) {
		alert('PIN must be exactly 4 digits');
		return;
		}
		
		// Ensure authorizations is an array
		if (data.authorizations && !Array.isArray(data.authorizations)) {
		// If not array, convert from object format to array format
		let authArray = [];
		for (let key in data.authorizations) {
			if (data.authorizations[key]) {
			authArray.push(parseInt(key));
			}
		}
		data.authorizations = authArray;
		}
		
		// Log the data for debugging
		console.log("Creating user with data:", JSON.stringify(data));
		
		// Make the API call with enhanced error handling
		fetch("/api/users.php", {
		body: JSON.stringify(data),
		credentials: "include",
		headers: {
			"Content-Type": "application/json"
		},
		method: "PUT"
		})
		.then(response => {
		if (!response.ok) {
			return response.text().then(text => {
			throw new Error(`Server responded with ${response.status}: ${text}`);
			});
		}
		return response.json();
		})
		.then(_ => {
		this.navigate("/users");
		// notify user of success
		alert("User created successfully");
		})
		.catch(e => {
		console.error("Error creating user:", e);
		alert("Failed to create user: " + e.message);
		this.handleError(e);
		});
	}

	/**
	 * Callback that handles authorizing a user on backend. Bound
	 * to the form.submit() in moostaka.render() for the view.
	 *
	 * @param {Integer} id - the unique id of the user to authorize
	 * @param {Event} event - the form submission event
	 */
	authorize_user(id, event) {
		event.preventDefault();
		let data = this.get_form_data(event.target);

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
	read_user(id, editable, authorizable, role_editable, payment_permission) {
		let p0 = User.read(id);
		let p1 = EquipmentType.list();
		let p2 = Role.list();
		let p3 = Charge.list("user_id=" + id);
		let p4 = Payment.list("user_id=" + id);
	
		Promise.all([p0, p1, p2, p3, p4]).then(values => {
		let currency_formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 });
		let date_formatter = new Intl.DateTimeFormat();
		let user = values[0];
		let equipment_types = values[1];
		let roles = values[2].filter(role => "unauthenticated" != role.name);
		
		console.log("Current user role ID:", user.role.id);
		console.log("Available roles:", roles);
		
		let authorized_equipment_types = equipment_types.filter(type => user.authorizations.includes(type.id));
	
		let total_charges = values[3].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0);
		let total_payments = values[4].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0);
		let balance = currency_formatter.format(Number(Math.round((total_payments - total_charges)+'e2')+'e-2'));
		let ledger = values[3].concat(values[4]).map(e => {
			e.ts = new Date(e.time);
			e.time = date_formatter.format(e.ts);
			return e;
		}).sort((a, b) => {
			return a.ts - b.ts;
		});
	
		ledger = ledger.reduce(function(new_ledger, transaction) {
			transaction.amount = parseFloat(transaction.amount);
			if("charge_policy" in transaction) {
			transaction.amount *= -1;
			}
	
			if(new_ledger.length > 0) {
			transaction.balance = new_ledger[new_ledger.length-1].balance + transaction.amount;
			} else {
			transaction.balance = transaction.amount;
			}
	
			new_ledger.push(transaction);
			return new_ledger;
		}, []).map((transaction) => {
			transaction.balance = currency_formatter.format(transaction.balance);
			transaction.amount = currency_formatter.format(transaction.amount);
			return transaction;
		});
	
		this.render("#main", "authenticated/users/view", {
			"user": user,
			"equipment_types": equipment_types,
			"roles": roles,
			"authorized_equipment_types": authorized_equipment_types,
			"ledger": ledger,
			"balance": balance,
			"editable": editable,
			"authorizable": authorizable,
			"role_editable": role_editable,
			"create_payment_permission": payment_permission
		}, {}, () => {
			// Set up event handlers for form submission
			for(const authorization of user.authorizations) {
			let authElement = document.getElementById("authorizations." + authorization);
			if (authElement) {
				authElement.checked = true;
			}
			}
			
			// Explicitly set the role_id select to match the user's current role
			let roleSelect = document.getElementById("role_id");
			if(roleSelect) {
			// Force the selection to match the user's role
			for(let i = 0; i < roleSelect.options.length; i++) {
				if(parseInt(roleSelect.options[i].value) === parseInt(user.role.id)) {
				roleSelect.options[i].selected = true;
				console.log("Setting option", roleSelect.options[i].value, "as selected");
				break;
				}
			}
			}
			
			let form = document.getElementById("edit-user-form");
			if(form) {
			form.addEventListener("submit", (e) => { 
				e.preventDefault();
				
				// Get form data
				let data = this.get_form_data(e.target);
				
				// Ensure role_id is preserved
				if (!data.role_id) {
				data.role_id = parseInt(user.role.id);
				console.log("Added missing role_id:", data.role_id);
				}
				
				console.log("Submitting user update with data:", data);
				
				this.update_user(id, e); 
			});
			}
			
			let authForm = document.getElementById("authorize-user-form");
			if(authForm) {
			authForm.addEventListener("submit", (e) => { this.authorize_user(id, e); });
			}
			
			let transaction_button = document.getElementById("transaction-button");
			if(transaction_button) {
			transaction_button.addEventListener("click", (e) => {this.toggle_transactions();});
			}
			
			if(values[3].length + values[4].length > 20) {
			this.toggle_transactions();
			}
			
			this.set_icon_colors(document);
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
	update_user(id, event) {
		event.preventDefault();
		
		// First fetch the current user data to ensure we preserve the role if needed
		User.read(id).then(existingUser => {
		// Get form data
		let data = this.get_form_data(event.target);
		
		console.log("Update user called for ID:", id);
		console.log("Form data:", JSON.stringify(data));
		console.log("Existing user role:", existingUser.role.id);
		
		// Ensure role_id is preserved if it's missing or invalid
		if (!data.role_id) {
			data.role_id = existingUser.role.id;
			console.log("Using existing role ID:", data.role_id);
		}
		
		// Convert role_id to integer
		data.role_id = parseInt(data.role_id, 10);
		
		// Validate PIN format
		if (data.pin && !/^\d{4}$/.test(data.pin)) {
			alert('PIN must be exactly 4 digits');
			return;
		}
		
		// Make sure is_active is properly formatted
		if (typeof data.is_active === 'string') {
			data.is_active = (data.is_active.toLowerCase() === 'true');
		} else if (data.is_active === undefined) {
			data.is_active = existingUser.is_active;
		}
		
		// Ensure authorizations are handled properly
		if (!data.authorizations) {
			data.authorizations = existingUser.authorizations;
		}
		
		// Double-check all required fields are present
		const requiredFields = ['name', 'email', 'role_id', 'is_active'];
		const missingFields = requiredFields.filter(field => data[field] === undefined);
		
		if (missingFields.length > 0) {
			console.error("Missing required fields:", missingFields);
			alert("Cannot update user: Missing required fields: " + missingFields.join(", "));
			return;
		}
		
		console.log("Final data being sent to API:", JSON.stringify(data));
		
		// Make the API request
		User.modify(id, data)
			.then(_ => {
			console.log("User update succeeded");
			this.navigate("/users/" + id);
			alert("User information updated successfully");
			})
			.catch(e => {
			console.error("Update user error:", e);
			alert("Failed to update user: " + e);
			this.handleError(e);
			});
		}).catch(e => {
		console.error("Error fetching existing user data:", e);
		this.handleError(e);
		});
	}
	
	/**
	 * Helper method to continue user update after ensuring role_id is present
	 * 
	 * @param {Integer} id - the unique id of the user to modify
	 * @param {Object} data - the form data to submit
	 */
	continueUserUpdate(id, data) {
		// Validate PIN format
		if (data.pin && !/^\d{4}$/.test(data.pin)) {
		alert('PIN must be exactly 4 digits');
		return;
		}
		
		// Make sure is_active is properly formatted
		if (typeof data.is_active === 'string') {
		data.is_active = (data.is_active.toLowerCase() === 'true');
		}
		
		// Double-check all required fields are present
		const requiredFields = ['name', 'email', 'role_id', 'is_active'];
		const missingFields = requiredFields.filter(field => data[field] === undefined);
		
		if (missingFields.length > 0) {
		console.error("Missing required fields:", missingFields);
		alert("Cannot update user: Missing required fields: " + missingFields.join(", "));
		return;
		}
		
		console.log("Final data being sent to API:", JSON.stringify(data));
		
		// Make the API request
		User.modify(id, data)
		.then(_ => {
			console.log("User update succeeded");
			this.navigate("/users/" + id);
			// Notify user of success
			alert("User information updated successfully");
		})
		.catch(e => {
			console.error("Update user error:", e);
			alert("Failed to update user: " + e);
			this.handleError(e);
		});
	}
	/**
	 * Update user PIN from profile page
	 * 
	 * @param {Integer} id - the unique id of the user
	 * @param {String} pin - the new PIN
	 */
	update_user_pin(id, pin) {
		// First get the full user data
		User.read(id).then(user => {
			// Create update object with required fields
			const updateData = {
				name: user.name,
				email: user.email,
				comment: user.comment || '',
				pin: pin,
				role_id: user.role.id,
				is_active: user.is_active,
				authorizations: user.authorizations || []
			};
			
			// Send the update back to the server
			User.modify(id, updateData).then(_ => {
				// Show success message
				alert('PIN updated successfully!');
				
				// Update the display without refreshing the page
				const pinDisplay = document.getElementById('pin-display-container');
				if (pinDisplay) {
					pinDisplay.innerHTML = pin + ' <button type="button" onclick="document.getElementById(\'pin-display-container\').style.display=\'none\'; document.getElementById(\'pin-edit-container\').style.display=\'block\';" class="default">Change PIN</button>';
					pinDisplay.style.display = 'block';
				}
				
				const pinEditContainer = document.getElementById('pin-edit-container');
				if (pinEditContainer) {
					pinEditContainer.style.display = 'none';
				}
				
				// Also update the current user object
				if (this.user && this.user.id === id) {
					this.user.pin = pin;
				}
			}).catch(e => {
				alert('Error updating PIN: ' + e.message);
				console.error('PIN update error:', e);
				this.handleError(e);
			});
		}).catch(e => this.handleError(e));
	}
	/**
	 * Setup event handlers for the profile page PIN functionality
	 */
	setupProfilePinHandlers() {
		// This is now handled directly in the profile.mst template
	}
	/**
	 * Render an optionally sorted list of users
	 */
	list_users(params) {
		let queryString = "";
		if(params !== null) {
			queryString = Object.keys(params).map(key => key + '=' + params[key]).join('&');
		}

		let p0 = User.list(queryString);
		let p1 = Role.list();

		Promise.all([p0,p1]).then(values => {
			let users = values[0];
			let roles = values[1];

			if((params !== null) && 0 < Object.keys(params).length) {
				params.customized = true;
			}
			this.render("#main", "authenticated/users/list", {
				"users": users,
				"search": params,
				"roles": roles,
				"create_user_permission": this.user.has_permission(Permission.CREATE_USER)
			}, {}, () => {
				let element = document.getElementById("role_id");
				this.set_dropdown_selector(element, params.role_id);
				this.set_icon_colors(document);
			});
		}).catch(e => this.handleError(e));
	}

	search_users(search_form) {
		// look at search params to insure we have a search
		let search = {};
		let searchParams = this.get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length || ("boolean" == typeof(searchParams[k]))) {
				search[k] = searchParams[k];
			}
		}

		if(0 < Object.keys(search).length) {
			this.list_users(search);
		}
	}

	sort_users(sort_column) {
		let search_form = document.getElementById('user_search_form');
		// look at search params to insure we have a search
		let search = {};
		let searchParams = this.get_form_data(search_form);
		let keys = Object.getOwnPropertyNames(searchParams);
		for(let k of keys) {
			if(0 < searchParams[k].length || ("boolean" == typeof(searchParams[k]) && searchParams[k])) {
				search[k] = searchParams[k];
			}
		}

		search.sort = sort_column;
		this.list_users(search);
	}

	toggle_transactions() {
		let content = document.getElementsByClassName("collapsible-content");
		let button = document.getElementById("transaction-button");

		if(button.innerText == "Show Transactions") {
			button.innerText = "Hide Transactions";
		} else {
			button.innerText = "Show Transactions";
		}

		for(let i = 0; i < content.length; i++) {
			let element = content[i];
			if(element.style.display == '') {
				element.style.display = 'none';
			} else {
				element.style.display = '';
			}
		}
	}

	set_icon_colors(d) {
		let icons = d.getElementsByClassName("material-icons");

		for(let i = 0; i < icons.length; i++) {
			if(icons[i].innerText == "check_circle_outline") {
				icons[i].style.color = "green";
			}
			if(icons[i].innerText == "highlight_off") {
				icons[i].style.color = "red";
			}
		}
	}

	card_options_selector(value) {
		// Warning hardcoded values! However, we'd need
		// to make massive changes to the Portal Box
		// Application to do otherwise
		let equipment_type_selector_label = document.getElementById("equipment_type_id_label");
		let equipment_type_selector = document.getElementById("equipment_type_id");
		let user_selector_label = document.getElementById("user_id_label");
		let user_selector = document.getElementById("user_id");
		switch(value) {
			case "1":	// intentional fallthrough
			case "2":
				// hide and disable the equipment type and user selectors
				equipment_type_selector_label.style.display = "none";
				equipment_type_selector.style.display = "none";
				equipment_type_selector.disabled = true;
				equipment_type_selector.required = false;
				user_selector_label.style.display = "none";
				user_selector.style.display = "none";
				user_id_input.style.display = "none";
				user_selector.disabled = true;
				user_selector.required = false;
				break;
			case "3": // type training selected
				// hide and disable the user selector
				// show and enable the equipment type selector
				equipment_type_selector_label.style.display = "block";
				equipment_type_selector.style.display = "block";
				equipment_type_selector.disabled = false;
				equipment_type_selector.required = true;
				user_selector_label.style.display = "none";
				user_selector.style.display = "none";
				user_id_input.style.display = "none";
				user_selector.disabled = true;
				user_selector.required = false;
				break;
			case "4": // user card selected
				// hide and disable the equipment type selector
				// show and enable the user selector
				equipment_type_selector_label.style.display = "none";
				equipment_type_selector.style.display = "none";
				equipment_type_selector.disabled = true;
				equipment_type_selector.required = false;
				user_selector_label.style.display = "block";
				user_selector.style.display = "none";
				user_id_input.style.display = "block";
				user_selector.disabled = false;
				user_selector.required = true;
				break;
			default:
				equipment_type_selector_label.style.display = "none";
				equipment_type_selector.style.display = "none";
				equipment_type_selector.disabled = true;
				equipment_type_selector.required = false;
				user_selector_label.style.display = "none";
				user_selector.style.display = "none";
				user_id_input.style.display = "none";
				user_selector.disabled = true;
				user_selector.required = false;
		}
	}

	set_dropdown_selector(element, id) {
		for(let i = 0; i < element.length; i++) {
			if(element[i].value == id) {
				element[i].selected = true;
			}
		}
	}
}

export { Application };
