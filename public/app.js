
var moostaka = null;
/**
 * While an end point exists for card_type, the types are integral
 * to the functioning of the Portal Box application, and would need
 * be changed in a coordinated manner not only here but also the
 * DB and the Portal Box application. Therefore it is reasonable to
 * hard code them at this time.
 */
var card_types = [
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
// ditto
var charge_policies = [
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
// ditto
var management_portal_access_levels = [
    {
        "id": 1,
        "name":"No Access"
    },
    {
        "id": 2,
        "name":"Trainer"
    },
    {
        "id": 3,
        "name":"Admin"
    },
];

/**
 * While Moostaka is pretty great; simple, open source, has made
 * this SPA possible; there is a shortcoming: it's design really
 * only supports navigation by HTML link elements. Unfortunately,
 * linking table rows is not part of the HTML standard so we need
 * a bit of a work around. We will bind the click event of table
 * rows to this helper function making them work with moostaka
 * as if they are links.
 * 
 * go - make the browser "navigate" to the given url even when
 *     when the url is virtual, ie only valid in the SPA
 * @param (string)destination_url - the location the browser should
 *     "navigate" to
 * @param (string)current_page_title - what the history entry for
 *     page where the click occured should be called
 */
function go(destination_url, current_page_title) {
    moostaka.navigate(destination_url);
    history.pushState({}, current_page_title, destination_url);
}

/**
 * Helper which iterates the fields in a form creating an object
 * which has key value pairs corresponding to the name and value
 * of the fields with a name attribute. If name is of the form
 * "foo.bar" then the value will be nested as ret["foo"]["bar"]
 */
function get_form_data(form) {
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
function add_api_key(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/api-keys.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new API key";
    }).then(_data => {
        moostaka.navigate("/api-keys");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

function delete_api_key(id) {
    if(window.confirm("Are you sure you want to delete the API key")) { 
        fetch("/api/api-keys.php?id=" + id, {
            credentials: "include",
            method: "DELETE"
        }).then(response => {
            if(response.ok) {
                moostaka.navigate("/api-keys");
            }

            throw "API was unable to save new API key";
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    }
}

/**
 * Callback that handles updating cards on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_api_key(key, event) {
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
        }

        throw "API was unable to save API key";
    }).then(_data => {
        moostaka.navigate("/api-keys");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding a card to the backend. Bound
 * to the form.submit() in moostaka.render() for the view
 */
function add_card(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/cards.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new card";
    }).then(_data => {
        moostaka.navigate("/cards");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating cards on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_card(card, event) {
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
        }

        throw "API was unable to save card";
    }).then(_data => {
        moostaka.navigate("/cards");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating charges on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_charge(charge, event) {
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
        }

        throw "API was unable to save charge";
    }).then(_data => {
        moostaka.navigate("/charges");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding equipment to the backend. Bound
 * to the form.submit() in moostaka.render() for the view
 */
function add_equipment(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/equipment.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new equipment";
    }).then(_data => {
        moostaka.navigate("/equipment");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating equipment on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_equipment(equipment, event) {
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
        }

        throw "API was unable to save equipment";
    }).then(_data => {
        moostaka.navigate("/equipment");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding an equipment type to the backend.
 * Bound to the form.submit() in moostaka.render() for the view
 */
function add_equipment_type(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/equipment-types.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new equipment type";
    }).then(_data => {
        moostaka.navigate("/equipment-types");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating an equipment type on backend.
 * Bound to the form.submit() in moostaka.render() for the view.
 */
function update_equipment_type(type, event) {
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
        }

        throw "API was unable to save equipment type";
    }).then(_data => {
        moostaka.navigate("/equipment-types");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding a location to the backend. Bound
 * to the form.submit() in moostaka.render() for the view
 */
function add_location(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/locations.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new location";
    }).then(_data => {
        moostaka.navigate("/locations");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating a location on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_location(location, event) {
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
        }

        throw "API was unable to save location";
    }).then(_data => {
        moostaka.navigate("/locations");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Retrieves log as currently filtered in csv format and allows user
 * to save as a CSV file
 */
function saveLog(search) {
    let url = '/api/logs.php?' + search;

    fetch(url, {
        credentials: "include",
        headers: {
            "Accept": "text/csv"
        }
    }).then(response => {
        if(response.ok) {
            return response.text();
        }

        throw "API was unable to create report from log";
    }).then(data => {
        let blob = new Blob([data], {type: "text/csv;charset=utf-8"});
        saveAs(blob, "log.csv");    // provided by Eli Grey's FileSaver.js
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding a payment to the backend. Bound
 * to the form.submit() in moostaka.render() for the view
 */
function add_payment(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/payments.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new payment";
    }).then(data => {
        moostaka.navigate("/users/" + data.user_id);
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles adding a user to the backend. Bound
 * to the form.submit() in moostaka.render() for the view
 */
function add_user(event) {
    event.preventDefault();
    let data = get_form_data(event.target);

    fetch("/api/users.php", {
        body: JSON.stringify(data),
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        method: "PUT"
    }).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to save new user";
    }).then(_data => {
        moostaka.navigate("/users");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Render an optionally sorted list of users
 */
function list_users(params) {
    let url = "/api/users.php";
    let sort = "";
    let search = "";

    if('sort' in params) {
        if('name' == params.sort) {
            url += '?sort=name';
            sort = 'name';
        }
        if('email' == params.sort) {
            url += '?sort=email';
            sort = 'email';
        }
    }

    if('name' in params) {
        url += '?search=';
        url += encodeURIComponent(params.name);
        search = params.name;
    }

    fetch(url, {"credentials": "same-origin"}).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "API was unable to list users";
    }).then(users => {
        moostaka.render("#main", "admin/users/list", {"users": users, "sort": sort, "search":search});
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Callback that handles updating a user on backend. Bound
 * to the form.submit() in moostaka.render() for the view.
 */
function update_user(user, event) {
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
        }

        throw "API was unable to save user";
    }).then(_data => {
        moostaka.navigate("/users");
        // notify user of success
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
}

/**
 * Helper to set up the routes for our authenticated admin user...
 * Much of the application logic is in anonmyous functions used
 * here. Possible refactor target in the future
 */
function init_routes_for_authenticated_admin() {
    moostaka.route("/", params => {
        moostaka.render("#main", "admin/top-menu", params);
    });
    moostaka.route("/logout", params => {
        hello("google").logout();
    });
    moostaka.route("/api-keys", params => {
        fetch("/api/api-keys.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list api keys";
        }).then(keys => {
            moostaka.render("#main", "admin/api-keys/list", {"keys": keys});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/api-keys/add", params => {
        moostaka.render("#main", "admin/api-keys/add", {}, {}, () => {
            let form = document.getElementById("add-api-key-form");
            form.addEventListener("submit", (e) => { add_api_key(e); });
        });
    });
    moostaka.route("/api-keys/:id", params => {
        fetch("/api/api-keys.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find api key: " + params.id;
        }).then(key => {
            moostaka.render("#main", "admin/api-keys/view", {"key": key}, {}, () => {
                let form = document.getElementById("edit-api-key-form");
                form.addEventListener("submit", (e) => { update_api_key(key, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/cards", params => {
        fetch("/api/cards.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list cards";
        }).then(cards => {
            moostaka.render("#main", "admin/cards/list", {"cards": cards});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/cards/add", params => {
        let p0 = new Promise((resolve, reject) => { resolve(card_types); });
        let p1 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });
        let p2 = fetch("/api/users.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list users";
        });

        Promise.all([p0, p1, p2]).then(values => {
            moostaka.render("#main", "admin/cards/add", {"types": values[0], "equipment_types": values[1], "users": values[2]}, {}, () => {
                let form = document.getElementById("add-card-form");
                form.addEventListener("submit", (e) => { add_card(e); });
                let equipment_type_selector_label = document.getElementById("equipment_type_id_label");
                let equipment_type_selector = document.getElementById("equipment_type_id");
                let user_selector_label = document.getElementById("user_id_label");
                let user_selector = document.getElementById("user_id");
                equipment_type_selector_label.style.display = "none";
                equipment_type_selector.style.display = "none";
                equipment_type_selector.disabled = true;
                equipment_type_selector.required = false;
                user_selector_label.style.display = "none";
                user_selector.style.display = "none";
                user_selector.disabled = true;
                user_selector.required = false;
                let type_id_selector = document.getElementById("type_id");
                type_id_selector.addEventListener('change', (event) => {
                    // Warning hardcoded values! However, we'd need
                    // to make massive changes to the Portal Box
                    // Application to do otherwise
                    let equipment_type_selector_label = document.getElementById("equipment_type_id_label");
                    let equipment_type_selector = document.getElementById("equipment_type_id");
                    let user_selector_label = document.getElementById("user_id_label");
                    let user_selector = document.getElementById("user_id");
                    switch(event.target.value) {
                        case "1":	// intentional fallthrough
                        case "2":
                            // hide and disable the equipment type and user selectors
                            equipment_type_selector_label.style.display = "none";
                            equipment_type_selector.style.display = "none";
                            equipment_type_selector.disabled = true;
                            equipment_type_selector.required = false;
                            user_selector_label.style.display = "none";
                            user_selector.style.display = "none";
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
                            user_selector.style.display = "block";
                            user_selector.disabled = false;
                            user_selector.required = true;
                            break;
                    }
                });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/cards/:id", params => {
        let p0 = new Promise((resolve, reject) => { resolve(card_types); });
        let p1 = fetch("/api/cards.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find card: " + params.id;
        });
        let p2 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });
        let p3 = fetch("/api/users.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list users";
        });

        Promise.all([p0, p1, p2, p3]).then(values => {
            moostaka.render("#main", "admin/cards/view", {"types": values[0], "card": values[1], "equipment_types": values[2], "users": values[3]}, {}, () => {
                let form = document.getElementById("edit-card-form");
                form.addEventListener("submit", (e) => { update_card(values[1], e); });
                let equipment_type_selector = document.getElementById("equipment_type_id");
                if(values[1].equipment_type_id) {
                    equipment_type_selector.value = values[1].equipment_type_id;
                } else {
                    document.getElementById("equipment_type_id_label").style.display = "none";
                    document.getElementById("equipment_type_label").style.display = "none";
                    document.getElementById("equipment_type").style.display = "none";
                    equipment_type_selector.style.display = "none";
                    equipment_type_selector.disabled = true;
                    equipment_type_selector.required = false;
                }
                let user_selector = document.getElementById("user_id");
                if(values[1].user_id) {
                    user_selector.value = values[1].user_id;
                } else {
                    document.getElementById("user_id_label").style.display = "none";
                    document.getElementById("user_label").style.display = "none";
                    document.getElementById("user").style.display = "none";
                    user_selector.style.display = "none";
                    user_selector.disabled = true;
                    user_selector.required = false;
                }
                let type_id_selector = document.getElementById("type_id");
                type_id_selector.value = values[1].type_id;
                type_id_selector.addEventListener('change', (event) => {
                    // Warning hardcoded values! However, we'd need
                    // to make massive changes to the Portal Box
                    // Application to do otherwise
                    let equipment_type_selector_label = document.getElementById("equipment_type_id_label");
                    let equipment_type_selector = document.getElementById("equipment_type_id");
                    let user_selector_label = document.getElementById("user_id_label");
                    let user_selector = document.getElementById("user_id");
                    switch(event.target.value) {
                        case "1":	// intentional fallthrough
                        case "2":
                            // hide and disable the equipment type and user selectors
                            equipment_type_selector_label.style.display = "none";
                            equipment_type_selector.style.display = "none";
                            equipment_type_selector.disabled = true;
                            equipment_type_selector.required = false;
                            user_selector_label.style.display = "none";
                            user_selector.style.display = "none";
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
                            user_selector.style.display = "block";
                            user_selector.disabled = false;
                            user_selector.required = true;
                            break;
                    }
                });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/charges", params => {
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
            // we will inject a minimal search so we don't pull the entire log by default
            let oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
            searchParams.append("after", oneWeekAgo.toISOString());
        }

        let p0 = fetch("/api/charges.php?" + searchParams.toString(), {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list charges";
        });
        let p1 = fetch("/api/equipment.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        });
        let p2 = fetch("/api/users.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list users";
        });
        
        Promise.all([p0, p1, p2]).then(values => {
            moostaka.render("#main", "admin/charges/list", {"charges":values[0], "equipment":values[1], "search":search, "users":values[2]});
        }).catch(error => {
            console.log(error);
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/charges/:id", params => {
        fetch("/api/charges.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find charge: " + params.id;
        }).then(charge => {
            console.log(charge);
            moostaka.render("#main", "admin/charges/view", {"charge": charge}, {}, () => {
                let form = document.getElementById("edit-charge-form");
                form.addEventListener("submit", (e) => { update_charge(charge, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/equipment", params => {
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

        fetch("/api/equipment.php?" + searchParams.toString(), {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        }).then(equipment => {
            moostaka.render("#main", "admin/equipment/list", {"equipment": equipment, "search":search});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/equipment/add", params => {
        let p1 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });
        let p2 = fetch("/api/locations.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list locations";
        });

        Promise.all([p1, p2]).then(values => {
            moostaka.render("#main", "admin/equipment/add", {"types": values[0], "locations": values[1]}, {}, () => {
                let form = document.getElementById("add-equipment-form");
                form.addEventListener("submit", (e) => { add_equipment(e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/equipment/:id", params => {
        let p0 = fetch("/api/equipment.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find equipment: " + params.id;
        });
        let p1 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });
        let p2 = fetch("/api/locations.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list locations";
        });

        Promise.all([p0, p1, p2]).then(values => {
            moostaka.render("#main", "admin/equipment/view", {"equipment": values[0], "types": values[1], "locations": values[2]}, {}, () => {
                document.getElementById("type_id").value = values[0].type_id;
                document.getElementById("location_id").value = values[0].location_id;
                let form = document.getElementById("edit-equipment-form");
                form.addEventListener("submit", (e) => { update_equipment(values[0], e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/equipment-types", params => {
        fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        }).then(types => {
            console.log(types);
            moostaka.render("#main", "admin/equipment-types/list", {"types": types});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/equipment-types/add", params => {
        moostaka.render("#main", "admin/equipment-types/add", {"charge_policies":charge_policies}, {}, () => {
            let form = document.getElementById("add-equipment-type-form");
            form.addEventListener("submit", (e) => { add_equipment_type(e); });
        });
    });
    moostaka.route("/equipment-types/:id", params => {
        fetch("/api/equipment-types.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find equipment type: " + params.id;
        }).then(type => {
            moostaka.render("#main", "admin/equipment-types/view", {"type":type, "charge_policies":charge_policies}, {}, () => {
                document.getElementById("charge_policy_id").value = type.charge_policy_id;
                let form = document.getElementById("edit-equipment-type-form");
                form.addEventListener("submit", (e) => { update_equipment_type(type, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/locations", params => {
        fetch("/api/locations.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list locations";
        }).then(locations => {
            moostaka.render("#main", "admin/locations/list", {"locations": locations});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/locations/add", params => {
        moostaka.render("#main", "admin/locations/add", {}, {}, () => {
            let form = document.getElementById("add-location-form");
            form.addEventListener("submit", (e) => { add_location(e); });
        });
    });
    moostaka.route("/locations/:id", params => {
        let p0 = fetch("/api/locations.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find location: " + params.id;
        });
        let p1 = fetch("/api/equipment.php?location_id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        });
        
        Promise.all([p0,p1]).then(values => {
            moostaka.render("#main", "admin/locations/view", {"location": values[0], "equipment": values[1]}, {}, () => {
                let form = document.getElementById("edit-location-form");
                form.addEventListener("submit", (e) => { update_location(location, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/logs", params => {
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
            // we will inject a minimal search so we don't pull the entire log by default
            let oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
            searchParams.append("after", oneWeekAgo.toISOString());
        }
        let queryString = searchParams.toString();

        let p0 = fetch("/api/logs.php?" + queryString, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to retrieve specified log segment";
        });
        let p1 = fetch("/api/equipment.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        });
        let p2 = fetch("/api/locations.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
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
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
/*
    moostaka.route("/payments", params => {
        fetch("/api/payments.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list payments";
        }).then(payments => {
            console.log(payments);
            moostaka.render("#main", "admin/payments/list", {"payments": payments});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/payments/add", params => {
        fetch("/api/users.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list users";
        }).then(users => {
            moostaka.render("#main", "admin/payments/add", {"users": users}, {}, () => {
                let form = document.getElementById("add-payment-form");
                form.addEventListener("submit", (e) => { add_payment(e); });
                let now = new Date();
                document.getElementById("time").value = now.toISOString().slice(0,10);
            });
        });
    });
*/
    moostaka.route("/users", list_users);
    moostaka.route("/users/add", params => {
        fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        }).then(equipment_types => {
            moostaka.render("#main", "admin/users/add", {"equipment_types": equipment_types, "management_portal_access_levels": management_portal_access_levels}, {}, () => {
                let form = document.getElementById("add-user-form");
                form.addEventListener("submit", (e) => { add_user(e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/users/sort/:sort", list_users);
    moostaka.route("/users/search/:name", list_users);
    moostaka.route("/users/:id", params => {
        let p0 = fetch("/api/users.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find user: " + params.id;
        });
        let p1 = fetch("/api/charges.php?user_id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list user's charges";
        });
        let p2 = fetch("/api/payments.php?user_id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list user's payments";
        });
        let p3 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });

        Promise.all([p0, p1, p2, p3]).then(values => {
            let user = values[0];
            let ledger = values[1].concat(values[2]).map(e => {
                e.ts = new Date(e.time);
                return e;
            }).sort((a,b) => {
                return a.ts - b.ts;
            });

            let total_charges = values[1].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0);
            let total_payments = values[2].map(e => Number.parseFloat(e.amount)).reduce((a, c) => a + c, 0.0);
            let balance = total_payments - total_charges;

            moostaka.render("#main", "admin/users/view", {
                "balance": balance,
                "equipment_types": values[3],
                "ledger": ledger,
                "management_portal_access_levels": management_portal_access_levels,
                "user": user
            }, {}, () => {
                document.getElementById("management_portal_access_level_id").value = user.management_portal_access_level_id;
                for(let i = 0, l = user.authorizations.length; i < l; i++) {
                    let a = user.authorizations[i];
                    document.getElementById("authorizations." + a.equipment_type_id).checked = true;
                }
                let form = document.getElementById("edit-user-form");
                form.addEventListener("submit", (e) => { update_user(user, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/users/:id/add_payment", params => {
        fetch("/api/users.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find user: " + params.id;
        }).then(user => {
            moostaka.render("#main", "admin/users/add_payment", {"user": user}, {}, () => {
                let form = document.getElementById("add-payment-form");
                form.addEventListener("submit", (e) => { add_payment(e); });
                let now = new Date();
                document.getElementById("time").value = now.toISOString().slice(0,10);
            });
        });
    });
}

/**
 * Helper to set up the routes for our authenticated trainer
 * Much of the application logic is in anonmyous functions used
 * here. Possible refactor target in the future
 */
function init_routes_for_authenticated_trainer() {
    moostaka.route("/", params => {
        moostaka.render("#main", "trainer/top-menu", params);
    });
    moostaka.route("/logout", params => {
        hello("google").logout();
    });
    moostaka.route("/equipment", params => {
        fetch("/api/equipment.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        }).then(equipment => {
            moostaka.render("#main", "trainer/equipment/list", {"equipment": equipment});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/users", params => {
        fetch("/api/users.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list users";
        }).then(users => {
            moostaka.render("#main", "trainer/users/list", {"users": users});
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
    moostaka.route("/users/:id", params => {
        let p0 = fetch("/api/users.php?id=" + params.id, {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to find user: " + params.id;
        });
        let p1 = fetch("/api/equipment-types.php", {"credentials": "same-origin"}).then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment types";
        });

        Promise.all([p0, p1]).then(values => {
            let user = values[0];
            moostaka.render("#main", "trainer/users/view", {"user": user, "equipment_types": values[1]}, {}, () => {
                for(let i = 0, l = user.authorizations.length; i < l; i++) {
                    let a = user.authorizations[i];
                    document.getElementById("authorizations." + a.equipment_type_id).checked = true;
                }
                let form = document.getElementById("edit-user-form");
                form.addEventListener("submit", (e) => { update_user(user, e); });
            });
        }).catch(error => {
            moostaka.render("#main", "error", {"error": error});
        });
    });
}

/**
 * Helper to set up routes for unauthenticated user
 */
function init_routes_for_unauthenticated_user() {
    moostaka.route("/", params => {
        fetch("/api/equipment.php").then(response => {
            if(response.ok) {
                return response.json();
            }

            throw "API was unable to list equipment";
        }).then(equipment => {
            moostaka.render("#main", "unauthenticated/availability", {"equipment": equipment});
        }).catch(error => {
            console.log(error);
            moostaka.render("#main", "error", {"error": error});
        });
    });
}

/**** Setup Authentication using hello.js ****/
hello.on("auth.login", auth => {
    // check if google auth suceessful???
    if(auth && auth.authResponse && auth.authResponse.id_token) {
        fetch("/api/login.php", {"credentials": "same-origin", headers: {"Authorization": "Bearer " + auth.authResponse.id_token}}).then(response => {
            if(response.ok) {
                return response.json();
            } else {
                throw response.statusText + ": " + response.text();
            }
        }).then(user => {
            switch(user.management_portal_access_level_id) {
                case "3": // admin
                    moostaka.flush();
                    init_routes_for_authenticated_admin();
                    moostaka.navigate(location.pathname); // need to explicitly update content
                    hello(auth.network).api("me").then(params => {
                        moostaka.render("#page-menu", "admin/menu", params);
                    });
                    break;
                case "2": // trainer
                    moostaka.flush();
                    init_routes_for_authenticated_trainer();
                    moostaka.navigate(location.pathname); // need to explicitly update content
                    hello(auth.network).api("me").then(params => {
                        moostaka.render("#page-menu", "trainer/menu", params);
                    });
                    break;
                default:
                    console.log("Unknown authorization level");
                    moostaka.render("#main", "error", {"error": "You are not permitted to use this system"});
            }
            
        }).catch(error => {
            moostaka.render("#main", "error", {"error": "You are not permitted to use this system"});
        });
    } else {
        moostaka.render("#main", "login", {"error": "You did not successfully authenticate with our OAuth2 partner"});
    }
});
hello.on("auth.logout", () => {
    // drop priveleges and transition to unauthenticated session
    // delete api session cookie
    document.getElementById("page-menu").innerHTML = "";
    moostaka.flush();
    init_routes_for_unauthenticated_user();
    moostaka.render("#page-menu", "unauthenticated/menu", {});
    window.location = location.protocol + "//" + location.host;
});
document.addEventListener("DOMContentLoaded", () => {
    moostaka = new Moostaka();

    fetch("/api/config.php", {"credentials": "same-origin"}).then(response => {
        if(response.ok) {
            return response.json();
        }

        throw "Unable to read interface configuration data from API.";
    }).then(config => {
        hello.init(
            {
                google: config.google_oauth_client_id
            },
            {
                response_type:"token id_token",
                redirect_uri: location.protocol + "//" + location.host, scope:"email"
            }
        );

        let currentTime = (new Date()).getTime() / 1000;
        let session = hello("google").getAuthResponse();
        if(session && session.access_token && session.expires > currentTime) {
            // resume authenticated session
            // hello.js will auto renew our OAuth session so we
            // pickup in the on(auth.login) handler
        } else {
            // start unauthenticated session
            init_routes_for_unauthenticated_user();
            moostaka.render("#page-menu", "unauthenticated/menu", {});
            moostaka.navigate(location.pathname);
        }
    }).catch(error => {
        moostaka.render("#main", "error", {"error": error});
    });
});