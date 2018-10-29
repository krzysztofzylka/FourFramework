CREATE TABLE uzytkownicy (
    ID int NOT NULL AUTO_INCREMENT,
    login varchar(24) NOT NULL,
    haslo varchar(48) NOT NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE uprawnienia (
	ID int NOT NULL AUTO_INCREMENT,
	uzytkownik int NOT NULL,
	uprawnienie varchar(255) NOT NULL,
	pozwolenie int NOT NULL DEFAULT 0,
	PRIMARY KEY (ID)
);