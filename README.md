# Maker Portal

## About
This web application is the companion webite for a deployment of MakerSpace Portal Boxes. Consisting of two parts; a single page web application (SPA) built on the light weight moostaka+mustache framework with OAuth2 authentication using hellojs and a backend REST API built with PHP+PDO(mysql). By default, the website allows unauthenticated users to check the availability of equipment, authenticated users to check their account balances, and admins to administer the system. We have implemented a flexible role based access system allowing you to create new roles such as trainers or auditors.

### Note on Conventions
In some shell commands you may need to provide values left up to you. These values are denoted using the semi-standard shell variable syntax e.g. ${NAME_OF_DATA}

## License
This project is licensed under the Apache 2.0 License - see the LICENSE file for details

## Supported Server Environments
Makerportal requires PHP version 5.4+ and is known to work with:

 - Apache 2.4 + mod_php
 - Nginx 1.12 + PHP-FPM

if you use a different configuration please create a pull request to let us know. Some example server configurations can be found in the `documentation/Example Server Configurations` directory.

## Configuration
Configuration is primarily handled with two files. The first, `config/config.ini` specifies the database connection parameters used by the webservice a.k.a REST API and the Google OAuth Client ID used for OAUTH2 authentication. The second `public/styles/palette.css` is used to set the site's color palette. Example configuration files are provided in the respective directories. To use the Bucknell color palette simply copy `public/styles/example-palette.css` to `public/styles/palette.css`. While copying `config/example-config.ini` to `config/config.ini` is the fastest way to get started, you will need to then edit `config.ini` providing your database connection parameters and API key.

*Note*: currently only Google is supported as as OAUTH provider and you will need to provide a public redirect url (Google does not allow local only addresses like web.makerspace.local) for your web site when you generate an OAUTH Client ID. See also: https://developers.google.com/identity/protocols/OpenIDConnect

*Note*: Some webservers strip the Authorization header from requests before sending them to PHP. As we process the Authorization header in PHP (see api/login.php) you may need to allow the header through. If using WSGI with apache, you may be able to simply add this:

`WSGIPassAuthorization On`

to your server config, virtual host, or public/.htaccess. If using mod_php you will need to insure that your server config or virtual server config include a `<Directory ...>` element for the public directory which includes an `AllowOverride` rule with the value of `All` or a list including `AuthConfig`. Other configurations may also work see also `documentation/Example Server Configurations`

### Advanced Configuration
Occasionally, it may be necessary to provide a helper function for PHP. We support this through the use of php files placed in `lib/extensions`. Files should be named `ext_${function_name}.php` e.g. `ext_validate_email.php` and contain a single function with the name ${function_name} and conform to the signature documented below. Supported extensions include:

- validate_email - Provides for custom validation of email addresses. It take one string parameter, the email address to validate and returns the boolean constant FALSE if the email address could not be mapped to a valid email address otherwise it returns a string representing the email address to store in the database which may not be the same as the input email address.

## Installation
1) Clone this repository somewhere convenient. This will henceforth be referred to as ${PROJECT_DIRECTORY}.
2) Install the dependancies
	Using yarn (https://yarnpkg.com):

	```sh
	cd ${PROJECT_DIRECTORY}/public
	yarn install
	```
3) Copy `config/example-config.ini` to `config/config.ini` and edit the config.ini file filling in your database connection settings and Google OAuth Client ID.
4) Copy `public/styles/example-palette.css` to `public/styles/palette.css`; [Optional] Customize the site by editing palette.css
5) Either point your web server's DOCUMENT_ROOT to the public folder [OR] copy everything including hidden files e.g. .htaccess to the place your server considers to be DOCUMENT_ROOT

## Testing

### Unit Testing
A unit test suite is included for testing PHP code. To use it you will need `phpunit` and to generate the PSR-4 autoload map. This can be done using `composer`. To get `composer` follow the instructions for your os on https://getcomposer.org. With `composer` installed, simply change into the project directory and run `composer install`. You can then run tests using `phpunit` installed by `composer`.

```sh
cd ${PROJECT_DIRECTORY}
composer install
vendor/bin/phpunit test
```

### Integration Testing
The REST API exposed by this project project can be tested on your development machine using the webserver built in to the PHP CLI. Assuming you have followed steps 1 through 4 under Installation, you can in theory open a command shell and issue:

```sh
cd ${PROJECT_DIRECTORY}/public
php -S localhost:8000
```

You should then be able to use the requests available in the included [Postman](https://www.postman.com/) collection, see `documentation/api.postman_collection.json`, after setting reasonable collection variable values to test the API.

### Live Testing
Various OAuth2 providers restrict the "redirect" URL to be a public URL. With these OAuth2 providers you may be able to test locally by adding an alias for your local machine to a nonexistant domain or subdomain of your domain in your `/etc/hosts` file and enter that same nonexistant domain/subdomain as an authorized redirect URI for your OAuth Client ID credential. E.g.

```
sudo echo "127.0.0.1	dev.bucknell.edu" >> /ect/hosts
cd ${PROJECT_DIRECTORY}/public
php -S localhost:8000
```

provided your API token has a redirect URL of: dev.bucknell.edu:8000

## Security
You should take care to prevent the contents of the `config` directory from being publically accessible. Should you discover a security issue please send us an email: mlampart at bucknell dot edu and tom at tomegan dot tech. We will do our best to work with you to resolve the issue and credit you *or* create a pull request with a solution. Be aware we are volunteers and may not be able to respond immediately.

## Author

Since 2019 Tom Egan <tom@tomegan.tech>

## Roadmap
- work with OAUTH providers other than Google
- add a way to set a logo other than editing index.html
- automate Integration testing with phpunit or Postman
