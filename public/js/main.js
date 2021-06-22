import { Application } from './Application.js';
import { User } from './User.js';

window.app = null;

/**
 * While Moostaka is pretty great; simple, open source, has made this SPA
 * possible; there is a shortcoming: it's design really only supports
 * navigation by HTML link elements. Unfortunately, linking table rows is not
 * part of the HTML standard so we need a bit of a work around. We will bind
 * the click event of table rows to this helper function making them work with
 * moostaka as if they are links.
 * 
 * go - make the browser "navigate" to the given url even when when the url is
 *     virtual, ie only valid in the SPA
 * @param (string)destination_url - the location to which the browser should
 *     "navigate"
 * @param (string)current_page_title - what the history entry for page where
 *     the click occured should be called
 */
window.go = function(destination_url, current_page_title) {
    app.navigate(destination_url);
    history.pushState({}, current_page_title, destination_url);
};

/**** Setup Authentication using hello.js ****/
hello.on("auth.login", auth => {
	// check if auth suceessful???
	if(auth && auth.authResponse && auth.authResponse.id_token) {
		User.authenticate(auth.authResponse.id_token).then(user => {
			hello(auth.network).api("me").then(profile => {
				user.profile_image_url = profile.picture;
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
