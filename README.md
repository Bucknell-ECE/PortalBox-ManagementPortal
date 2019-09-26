# Maker Portal

## About
This web application is the companion webite for a deployment of MakerSpace Portal Boxes. Consisting of two parts; a single page web application (SPA) built on the light weight moostaka+mustache framework with OAuth2 authentication using hellojs and a backend REST API built with PHP+PDO(mysql), the website allows unauthenticated users to check the availability of equipment, trainers to authorize users for equipment and admins to administer the system.

### Note on Conventions
In some shell commands you may need to provide values left up to you. These values are denoted using the semi-standard shell variable syntax e.g. ${NAME_OF_DATA}

## License
This project is licensed under the Apache 2.0 License - see the LICENSE file for details

## Configuration
Configuration is handled with two files. The first, `public/config/config.ini` specifies the database connection parameters used by the Backend API and the Google OAuth Client ID used for OAUTH2 authentication. The second `public/styles/palette.css` is used to set the site's color palette. We have provided an example configuration files in the respective directories. To use the Bucknell color palette simply copy `public/styles/example-palette.css` to `public/styles/palette.css`. While copying `public/config/example-config.ini` to `public/config/config.ini` is the fastest way to get started, you will need to then edit `config.ini` and enter your database connection parameters and API key.

*Note*: currently only Google is supported as as OAUTH provider and you will need to provide a public redirect url (no local only addresses like web.makerspace.local) for your web site when you generate an OAUTH Client ID. See also: https://developers.google.com/identity/protocols/OpenIDConnect

*Note*: Some webservers strip the Authorization header from requests before sending them to PHP. As we process the Authorization header in PHP (see api/loging.php) you may need to allow the header through. If using WSGI with apache, you may be able to simply add this:

`WSGIPassAuthorization On`

to your server config, virtual host, or public/.htaccess. If using mod_php you will need to insure that you server config or virtual server config include a `<Directory ...>` element for the public directory which includes an `AllowOverride` rule with the value of `All` or a list including `AuthConfig`. Other configurations may also work but are untested. 

## Installation
1) Clone this repository somewhere convenient. This will henceforth be referred to as ${PROJECT_DIRECTORY}.
2) Install the dependancies
	Using yarn (https://yarnpkg.com):

	```sh
	cd ${PROJECT_DIRECTORY}/public
	yarn install
	```
3) Copy `public/config/example-config.ini` to `public/config/config.ini` and edit the config.ini file filling in your database connection settings and Google OAuth Client ID.
4) Copy `public/styles/example-palette.css` to `public/styles/palette.css`; [Optional] Customize the site by editing palette.css
5) Either point your web server's DOCUMENT_ROOT to the public folder [OR] copy everything including hidden files e.g. .htaccess to the place your server considers to be DOCUMENT_ROOT

## Testing
This project can be tested on your development machine using the webserver built in to the PHP CLI. Assuming you have followed steps 1 through 4 under Installation, you can in theory open a command shell and issue:

```sh
cd ${PROJECT_DIRECTORY}/public
php -S localhost:8000
```

However, various OAuth2 providers restrict the "redirect" URL to be a public URL. With these OAuth2 providers you may be able to still test locally by adding an alias for your local machine to a nonexistant subdomain of your top level domain in your `/etc/hosts` file and enter that same nonexistant subdomain as an authorized redirect URI for your OAuth Client ID credential. E.g.

```
sudo echo "127.0.0.1	dev.bucknell.edu" >> /ect/hosts
cd ${PROJECT_DIRECTORY}/public
php -S localhost:8000
```

provided your API token has a redirect URL of: dev.bucknell.edu:8000

## Security
You should take care to prevent the contents of the public/config directory from being publically accessible. If you are hosting your site with apache2.x, the included .htaccess files should work provided your Apache config is set to process .htaccess files.

## Roadmap
- work with OAUTH providers other than Google
- add a way to set a logo other than editing index.html
- allow any user to login and see their profile including charges, payment history and account balance.
