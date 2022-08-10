#!/bin/bash

# VARIABLES

# Google clientID key
googleID=''

# Your google account name. Must include first and last name.
name=''

# Your gmail address
email=''

# Database name
db_name=makerportal

# Database login
db_login=makerportal

# Database password
db_password=password



root=/var/www

[ $(id -u) != "0" ] && { echo "${CFAILURE}Error: You must be root to run this script${CEND}"; exit 1; }

# Install packages
apt-get update
apt install nginx mariadb-server mariadb-client php php-fpm php-mysql git -y

mkdir -p $root
git clone https://github.com/Bucknell-ECE/PortalBox-ManagementPortal.git $root/makerportal

git clone https://github.com/Bucknell-ECE/PortalBox-database.git $root/portaldatabase

curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
sudo apt-get update
sudo apt-get install yarn -y

# Move example config for nginx
cp $root/makerportal/documentation/Example\ Server\ Configurations/nginx.conf /etc/nginx/sites-enabled/makerportal

mkdir -p /var/www/logs

# Replace placeholder values in example configuration

sed -i "s/%URL%/www.makerportal.com makerportal.com/" /etc/nginx/sites-enabled/makerportal
sed -i "s/%PATH_TO_DOCUMENT_ROOT%/\/var\/www\/makerportal\/public/" /etc/nginx/sites-enabled/makerportal
sed -i "s/%PATH_TO_LOGS_DIRECTORY%/\/var\/www\/logs/" /etc/nginx/sites-enabled/makerportal

# Create config.ini

cp $root/makerportal/config/example-config.ini $root/makerportal/config/config.ini

sed -i "s/YOUR_DB_HOSTNAME/localhost/" $root/makerportal/config/config.ini
sed -i "s/YOUR_DB_NAME/$db_name/" $root/makerportal/config/config.ini
sed -i "s/YOUR_DB_USERNAME/$db_login/" $root/makerportal/config/config.ini
sed -i "s/YOUR_DB_PASSWORD/$db_password/" $root/makerportal/config/config.ini
sed -i "s/YOUR_GOOGLE_API_KEY/$googleID/" $root/makerportal/config/config.ini

# Create and configure database

mariadb -e "CREATE DATABASE $db_name"

mariadb $db_name < $root/portaldatabase/schema/schema.sql
mariadb $db_name -e "CREATE USER '$db_login'@'localhost' IDENTIFIED BY '$db_password'"

mariadb $db_name -e "GRANT ALL ON $db_name.* TO '$db_login'@'localhost';"

# Insert admin into database

mariadb $db_name -e "INSERT INTO users (name, email, role_id, is_active) VALUES ('$name', '$email', 3, 1);"


cd $root/makerportal/public
yarn install

reboot now

# After script completes configure google OAuth2 access
