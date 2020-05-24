import { Application } from 'Application.js';

var app = null;

/**** Setup Authentication using hello.js ****/
hello.on("auth.login", auth => {
	// check if auth suceessful???
	if(auth && auth.authResponse && auth.authResponse.id_token) {
		fetch("/api/login.php", {"credentials": "same-origin", headers: {"Authorization": "Bearer " + id_token}}).then(response => {
			if(response.ok) {
				return response.json();
			} else {
				response.text().then(text => {
					throw response.statusText + ": " + text;
				});
			}
		}).then(user => {
			hello(auth.network).api("me").then(profile => {
				//app.render("#page-menu", "user/menu", params);
				user.profile = profile;
				app.set_user(user);
				app.route("/logout", _ => {
					hello("google").logout();
				});
			});
		}).catch(e => {
			app.handleError(e);
		});
	} else {
		app.render("#main", "login", {"error": "You did not successfully authenticate with our OAuth2 partner"});
	}
});

hello.on("auth.logout", () => {
	app.set_user(null);
});

document.addEventListener("DOMContentLoaded", () => {
	app = new Application();

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
				redirect_uri: location.protocol + "//" + location.host,
				scope:"email"
			}
		);

		let currentTime = (new Date()).getTime() / 1000; // time in ms, session.expires is in seconds
		let session = hello("google").getAuthResponse();
		if(!session || session.expires <= currentTime) {
			app.set_user(null);
		}
	}).catch(e => {
		app.handleError(e);
	});
});
