CREATE TABLE users (
	id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
	username varchar(14) NOT NULL,
	email varchar(30) NOT NULL,
	password varchar(30) NOT NULL,
	ip_address varchar(30) NOT NULL,
	date varchar(30) NOT NULL,
	time varchar(30) NOT NULL
);

CREATE TABLE todo (
	id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
	username varchar(14) NOT NULL,
	title varchar(30) NOT NULL,
	description varchar(100) NOT NULL,
	due_date varchar(30) NOT NULL,
	created_date varchar(30) NOT NULL,
	label varchar(30) NOT NULL
);
