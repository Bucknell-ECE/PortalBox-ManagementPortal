-- Clear out the stored prcedures
DROP PROCEDURE IF EXISTS log_access_attempt;
DROP PROCEDURE IF EXISTS log_access_completion;
DROP FUNCTION IF EXISTS get_user_balance_for_card;


-- Clear out the tables we will build in reverse order to unwind FKeys
DROP TABLE IF EXISTS schema_versioning;
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS charges;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS event_types;
DROP TABLE IF EXISTS authorizations;
DROP TABLE IF EXISTS in_use;
DROP TABLE IF EXISTS equipment_type_x_cards;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS equipment_types;
DROP TABLE IF EXISTS charge_policies;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users_x_cards;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS cards;
DROP TABLE IF EXISTS card_types;
DROP TABLE IF EXISTS roles_x_permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS permissions;


-- List of Card Types... We use four(4): admin, proxy, training, and user
--   Could be an enum but we don't want to worry about enum issues in future
CREATE TABLE card_types (
	id INT UNSIGNED NOT NULL,
	name VARCHAR(8) NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO card_types(id, name) VALUES
	(1, "shutdown"),
	(2, "proxy"),
	(3, "training"),
	(4, "user");


-- List of cards
CREATE TABLE cards (
	id BIGINT(20) UNSIGNED NOT NULL,
	type_id INT UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY cards_type_id (type_id) REFERENCES card_types (id)
);


-- List of roles
CREATE TABLE roles (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT NOT NULL,
	is_system_role INT(1) UNSIGNED NOT NULL DEFAULT 0,
	description TEXT,
	PRIMARY KEY (id)
);

-- Create the default roles
INSERT INTO roles(name, is_system_role, description) VALUES
	('unauthenticated', 1, 'Role of users who have not authenticated'),
	('user', 1, 'Role of authenticated users who have not be granted additional permissions. This role is the default for users created in the web interface.'),
	('admin', 1, 'Role for users who administer the system.');


-- List of Permissions
-- Permissions play a special role in the code and are therefore constants in
-- in code and not AUTO_INCREMENT. See Entity/Permissions.php in Management
-- Portal. A backing table is provided to enforce a constraint in
-- roles_x_permissions
CREATE TABLE permissions (
	id INT UNSIGNED NOT NULL,
	name TEXT NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO permissions(id, name) VALUES
	(1, 'CREATE_API_KEY'),
	(2, 'READ_API_KEY'),
	(3, 'MODIFY_API_KEY'),
	(4, 'DELETE_API_KEY'),
	(5, 'LIST_API_KEYS'),
	(101, 'CREATE_EQUIPMENT_AUTHORIZATION'),
	(104, 'DELETE_EQUIPMENT_AUTHORIZATION'),
	(105, 'LIST_EQUIPMENT_AUTHORIZATIONS'),
	(106, 'LIST_OWN_EQUIPMENT_AUTHORIZATIONS'),
	(205, 'LIST_CARD_TYPES'),
	(301, 'CREATE_CARD'),
	(302, 'READ_CARD'),
	(303, 'MODIFY_CARD'),
	(305, 'LIST_CARDS'),
	(306, 'LIST_OWN_CARDS'),
	(401, 'CREATE_CHARGE_POLICY'),
	(402, 'READ_CHARGE_POLICY'),
	(403, 'MODIFY_CHARGE_POLICY'),
	(404, 'DELETE_CHARGE_POLICY'),
	(405, 'LIST_CHARGE_POLICIES'),
	(501, 'CREATE_CHARGE'),
	(502, 'READ_CHARGE'),
	(503, 'MODIFY_CHARGE'),
	(504, 'DELETE_CHARGE'),
	(505, 'LIST_CHARGES'),
	(506, 'LIST_OWN_CHANGES'),
	(601, 'CREATE_EQUIPMENT_TYPE'),
	(602, 'READ_EQUIPMENT_TYPE'),
	(603, 'MODIFY_EQUIPMENT_TYPE'),
	(604, 'DELETE_EQUIPMENT_TYPE'),
	(605, 'LIST_EQUIPMENT_TYPES'),
	(701, 'CREATE_EQUIPMENT'),
	(702, 'READ_EQUIPMENT'),
	(703, 'MODIFY_EQUIPMENT'),
	(704, 'DELETE_EQUIPMENT'),
	(705, 'LIST_EQUIPMENT'),
	(801, 'CREATE_LOCATION'),
	(802, 'READ_LOCATION'),
	(803, 'MODIFY_LOCATION'),
	(804, 'DELETE_LOCATION'),
	(805, 'LIST_LOCATIONS'),
	(902, 'READ_LOG'),
	(905, 'LIST_LOGS'),
	(1001, 'CREATE_PAYMENT'),
	(1002, 'READ_PAYMENT'),
	(1003, 'MODIFY_PAYMENT'),
	(1004, 'DELETE_PAYMENT'),
	(1005, 'LIST_PAYMENTS'),
	(1006, 'LIST_OWN_PAYMENTS'),
	(1101, 'CREATE_ROLE'),
	(1102, 'READ_ROLE'),
	(1103, 'MODIFY_ROLE'),
	(1104, 'DELETE_ROLE'),
	(1105, 'LIST_ROLES'),
	(1201, 'CREATE_USER'),
	(1202, 'READ_USER'),
	(1203, 'MODIFY_USER'),
	(1204, 'DELETE_USER'),
	(1205, 'LIST_USERS'),
	(1206, 'READ_OWN_USER');


-- Associate Permissions with roles.
CREATE TABLE roles_x_permissions (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	role_id INT UNSIGNED NOT NULL,
	permission_id INT UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY roles_x_permissions_role_id (role_id) REFERENCES roles (id) ON DELETE CASCADE,
	FOREIGN KEY roles_x_permissions_permission_id (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
);

-- Assign default permissions to roles
-- Assign permissions for an unauthenticated user
SET @unauthenticated_role_id = (SELECT id FROM roles WHERE name = 'unauthenticated');
INSERT INTO roles_x_permissions(role_id, permission_id) VALUES
	(@unauthenticated_role_id, 605),	-- list equipment types
	(@unauthenticated_role_id, 705);	-- list equipment

-- Assign permissions for a default authenticated user
SET @user_role_id = (SELECT id FROM roles WHERE name = 'user');
INSERT INTO roles_x_permissions(role_id, permission_id) VALUES
	(@user_role_id, 106),	-- list own authorizations
	(@user_role_id, 306),	-- list own cards
	(@user_role_id, 506),	-- read own charges
	(@user_role_id, 605),	-- list equipment types
	(@user_role_id, 705),	-- list equipment
	(@user_role_id, 1006),	-- read own payments
	(@user_role_id, 1206);	-- read own user record

-- Assign permissions for administrators ie. all permissions
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin');
INSERT INTO roles_x_permissions(role_id, permission_id) VALUES
	(@admin_role_id, 1),
	(@admin_role_id, 2),
	(@admin_role_id, 3),
	(@admin_role_id, 4),
	(@admin_role_id, 5),
	(@admin_role_id, 101),
	(@admin_role_id, 104),
	(@admin_role_id, 105),
	(@admin_role_id, 106),
	(@admin_role_id, 205),
	(@admin_role_id, 301),
	(@admin_role_id, 302),
	(@admin_role_id, 303),
	(@admin_role_id, 305),
	(@admin_role_id, 306),
	(@admin_role_id, 405),
	(@admin_role_id, 501),
	(@admin_role_id, 502),
	(@admin_role_id, 503),
	(@admin_role_id, 504),
	(@admin_role_id, 505),
	(@admin_role_id, 506),
	(@admin_role_id, 601),
	(@admin_role_id, 602),
	(@admin_role_id, 603),
	(@admin_role_id, 604),
	(@admin_role_id, 605),
	(@admin_role_id, 701),
	(@admin_role_id, 702),
	(@admin_role_id, 703),
	(@admin_role_id, 704),
	(@admin_role_id, 705),
	(@admin_role_id, 801),
	(@admin_role_id, 802),
	(@admin_role_id, 803),
	(@admin_role_id, 804),
	(@admin_role_id, 805),
	(@admin_role_id, 902),
	(@admin_role_id, 905),
	(@admin_role_id, 1001),
	(@admin_role_id, 1002),
	(@admin_role_id, 1003),
	(@admin_role_id, 1004),
	(@admin_role_id, 1005),
	(@admin_role_id, 1006),
	(@admin_role_id, 1101),
	(@admin_role_id, 1102),
	(@admin_role_id, 1103),
	(@admin_role_id, 1104),
	(@admin_role_id, 1105),
	(@admin_role_id, 1201),
	(@admin_role_id, 1202),
	(@admin_role_id, 1203),
	(@admin_role_id, 1204),
	(@admin_role_id, 1205),
	(@admin_role_id, 1206);


-- List of users
CREATE TABLE users (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT NOT NULL,
	email VARCHAR(512) NOT NULL,
	comment TEXT,
    pin VARCHAR(4) NOT NULL DEFAULT '0000',
	role_id INT UNSIGNED NOT NULL,
	is_active INT(1) UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	UNIQUE KEY users_email (email),
	FOREIGN KEY users_role_id (role_id) REFERENCES roles (id)
);


-- Associate a user to a card, form the User::cards relationship
CREATE TABLE users_x_cards (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	card_id BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY users_x_cards_user_id (user_id) REFERENCES users (id),
	FOREIGN KEY users_x_cards_card_id (card_id) REFERENCES cards (id) ON UPDATE CASCADE
);


-- List of locations where Portal Boxes are deployed
CREATE TABLE locations (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT,
	PRIMARY KEY(id)
);


-- List of Charge Frequencies
CREATE TABLE charge_policies (
	id INT UNSIGNED NOT NULL,
	name TEXT,
	PRIMARY KEY(id)
);

INSERT INTO charge_policies(id, name)
	VALUES (1, 'Manually Adjusted'), (2, 'No Charge'), (3, 'Per Use'), (4, 'Per Minute');


-- List of Equipment Types
CREATE TABLE equipment_types (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT,
	requires_training BOOLEAN NOT NULL DEFAULT true,
	charge_rate DECIMAL(5,2) DEFAULT NULL,
	charge_policy_id INT UNSIGNED DEFAULT 1,
	allow_proxy BOOLEAN NOT NULL DEFAULT false,
	PRIMARY KEY(id),
	FOREIGN KEY equipment_type_charge_policy_id (charge_policy_id) REFERENCES charge_policies (id)
);

INSERT INTO equipment_types(id, name) VALUES (0, 'Out of Service');


-- List of equipment connected to Portal Boxes
CREATE TABLE equipment (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT,
	type_id INT UNSIGNED NOT NULL,
	mac_address CHAR(12) NOT NULL,
	location_id INT UNSIGNED NOT NULL,
	timeout INT DEFAULT 0 NOT NULL,
	in_service INT(1) UNSIGNED NOT NULL,
	service_minutes INT UNSIGNED DEFAULT 0 NOT NULL,
	ip_address VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY(id),
	UNIQUE mac_address_index (mac_address),
	FOREIGN KEY equipment_locations_id (location_id) REFERENCES locations (id),
	FOREIGN KEY equipment_equipment_types_id (type_id) REFERENCES equipment_types (id)
);


-- List of Training cards...
--   Associate a piece of equipment to a card, forming the
--   Equipment::training_cards relationship
CREATE TABLE equipment_type_x_cards (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	equipment_type_id INT UNSIGNED NOT NULL,
	card_id BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY equipment_type_x_cards_equipment_type_id (equipment_type_id) REFERENCES equipment_types (id),
	FOREIGN KEY equipment_type_x_cards_card_id (card_id) REFERENCES cards (id) ON UPDATE CASCADE
);


-- List of equipment that is in use
--   Easy to reset `DELETE FROM in_use;`
CREATE TABLE in_use (
	equipment_id INT UNSIGNED NOT NULL,
	start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY in_use_equipment_id (equipment_id) REFERENCES equipment (id)
);


-- List of Authorizations forms the User::authorizations
CREATE TABLE authorizations (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	equipment_type_id INT UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY authorizations_equipment_type_id (equipment_type_id) REFERENCES equipment_types (id) ON DELETE CASCADE,
	FOREIGN KEY authorizations_user_id (user_id) REFERENCES users (id) ON DELETE CASCADE
);


-- List of Types of things we can log...
--   Could be an enum but we don't want to worry about enum issues in future
CREATE TABLE event_types (
	id INT UNSIGNED NOT NULL,
	name VARCHAR(32) NOT NULL,
	PRIMARY KEY(id)
);

INSERT INTO event_types(id, name) VALUES
	(1, "Unsuccessful Authentication"),
	(2, "Successful Authentication"),
	(3, "Deauthentication"),
	(4, "Startup Complete"),
	(5, "Planned Shutdown");


-- List of events aka the access log
CREATE TABLE log (
	id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	event_type_id INT UNSIGNED NOT NULL,
	card_id BIGINT(20) UNSIGNED,
	equipment_id INT UNSIGNED NOT NULL,
	time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	-- no foreign key for card_id because it may be an invalid card (failed auth)
	FOREIGN KEY log_equipment_id (equipment_id) REFERENCES equipment (id)
);


-- List of Payments
CREATE TABLE payments (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	amount DECIMAL(5,2) NOT NULL,
	time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	FOREIGN KEY payments_user_id (user_id) REFERENCES users (id)
);


-- List of Charges
CREATE TABLE charges (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	equipment_id INT UNSIGNED NOT NULL,
	time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	amount DECIMAL(5,2) NOT NULL,
	charge_policy_id INT UNSIGNED NOT NULL,
	charge_rate DECIMAL(5,2) DEFAULT NULL,
	charged_time INT UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY charges_user_id (user_id) REFERENCES users (id),
	FOREIGN KEY charges_equipment_id (equipment_id) REFERENCES equipment (id),
	FOREIGN KEY charges_charge_policy_id (charge_policy_id) REFERENCES charge_policies (id)
);


-- use a stored function to get the balance for the user presenting a card
DELIMITER $
CREATE FUNCTION get_user_balance_for_card(p_card_id INT UNSIGNED)
	RETURNS DECIMAL(10,2)
	READS SQL DATA
BEGIN
	DECLARE l_total_payments DECIMAL(10,2);
	DECLARE l_total_charges DECIMAL(10,2);

	SET l_total_payments = (SELECT sum(p.amount) FROM payments AS p
							INNER JOIN users_x_cards AS u ON u.user_id = p.user_id
							WHERE u.card_id = p_card_id);
	SET l_total_charges = (SELECT sum(c.amount) FROM charges AS c
							INNER JOIN users_x_cards AS u ON u.user_id = c.user_id
							WHERE u.card_id = p_card_id);
	RETURN IFNULL(l_total_payments, 0.0) - IFNULL(l_total_charges, 0.0);
END$
DELIMITER ;


-- use a stored procedure to log a user beginning a session with equipment
DELIMITER $
CREATE PROCEDURE log_access_attempt(p_success BOOL, p_card_id INT UNSIGNED, p_equipment_id INT UNSIGNED)
	MODIFIES SQL DATA
BEGIN
	IF p_success THEN
		INSERT INTO log(event_type_id, card_id, equipment_id, time) VALUES (2, p_card_id, p_equipment_id, CURRENT_TIMESTAMP);
		INSERT INTO in_use(equipment_id) VALUES (p_equipment_id);
	ELSE
		INSERT INTO log(event_type_id, card_id, equipment_id, time) VALUES (1, p_card_id, p_equipment_id, CURRENT_TIMESTAMP);
	END IF;
END$
DELIMITER ;


-- use a stored procedure to log a user releasing the equipment
DELIMITER $
CREATE PROCEDURE log_access_completion(p_card_id INT UNSIGNED, p_equipment_id INT UNSIGNED)
	MODIFIES SQL DATA
BEGIN
	DECLARE l_charge_policy_id INT UNSIGNED;
	DECLARE l_use_start_timestamp TIMESTAMP;
	DECLARE l_use_duration INT UNSIGNED;

	-- log in case all else fails
	INSERT INTO log(event_type_id, card_id, equipment_id, time)
		VALUES (3, p_card_id, p_equipment_id, CURRENT_TIMESTAMP);

	-- figure out charges if need be
	SET l_charge_policy_id = (SELECT et.charge_policy_id FROM equipment AS e
								INNER JOIN equipment_types AS et ON et.id = e.type_id
								WHERE e.id = p_equipment_id);
	SET l_use_start_timestamp = (SELECT start_time FROM in_use
								WHERE equipment_id = p_equipment_id LIMIT 1);
	SET l_use_duration = TIMESTAMPDIFF(MINUTE, l_use_start_timestamp, NOW());

	-- update equipment with new usage minutes
	UPDATE equipment SET service_minutes = service_minutes + l_use_duration WHERE id = p_equipment_id;

	CASE l_charge_policy_id
		WHEN 3 THEN -- per use
			INSERT INTO charges(user_id, equipment_id, amount, charge_policy_id, charge_rate, charged_time)
				SELECT uxc.user_id, p_equipment_id, et.charge_rate, l_charge_policy_id, et.charge_rate, l_use_duration
					FROM equipment AS e
					INNER JOIN equipment_types AS et ON et.id = e.type_id
					INNER JOIN users_x_cards AS uxc
					WHERE e.id = p_equipment_id AND uxc.card_id = p_card_id;
		WHEN 4 THEN -- per minute
			INSERT INTO charges(user_id, equipment_id, amount, charge_policy_id, charge_rate, charged_time)
				SELECT uxc.user_id, p_equipment_id, et.charge_rate * l_use_duration, l_charge_policy_id, et.charge_rate, l_use_duration
					FROM equipment AS e
					INNER JOIN equipment_types AS et ON et.id = e.type_id
					INNER JOIN users_x_cards AS uxc
					WHERE e.id = p_equipment_id AND uxc.card_id = p_card_id;
		ELSE BEGIN END;
	END CASE;

	DELETE FROM in_use WHERE equipment_id = p_equipment_id;
END$
DELIMITER ;


-- List of API Keys
CREATE TABLE api_keys (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	name TEXT NOT NULL,
	token CHAR(32) NOT NULL,
	PRIMARY KEY(id)
);


-- List to track what schema is installed; helpful when doing support
CREATE TABLE schema_versioning (
	id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	version TEXT NOT NULL,
	comment TEXT,
	PRIMARY KEY(id)
);

INSERT INTO schema_versioning(version, comment) VALUES ("2.9.0", "Database created");