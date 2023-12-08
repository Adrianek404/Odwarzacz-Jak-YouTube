<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
/*
$date = new DateTime();
echo $date->getTimestamp();
 */
//Vname,opis,author,views,data,previewIMG,name,likes,unlikes // TRZEBA ZROBIC: TYP, CZY dla wszystkich,
$sql = "CREATE TABLE IF NOT EXISTS video (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                  Vname VARCHAR(200) NOT NULL,
                                  name VARCHAR(100) NOT NULL,
                                  opis VARCHAR(5000) NOT NULL,
                                  author VARCHAR(64) NOT NULL,
                                  views INT(255) DEFAULT 0,
                                  data VARCHAR(25) NOT NULL,
                                  inf VARCHAR(250) DEFAULT '-',
                                  previewIMG TEXT(100000) DEFAULT '-',
                                  likes INT(200) DEFAULT 0,
                                  unlikes INT(200) DEFAULT 0,
                                  vkey VARCHAR(256) DEFAULT '-');";
// INSERT INTO video (Vname, name, opis, author, data, vkey) VALUES ("film.mp4", "Yummy hehe", "to jest opis", "Adrianek404", "1647863186", "001abc")
$link->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS users (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                  name VARCHAR(50) NOT NULL,
                                  surname VARCHAR(50) NOT NULL,
                                  email VARCHAR(50) NOT NULL,
                                  password VARCHAR(255) NOT NULL,
                                  birth VARCHAR(10) NOT NULL,
                                  sex VARCHAR(2) NOT NULL,
                                  vkey VARCHAR(45)NOT NUll,
                                  verifed BOOLEAN DEFAULT 0,
                                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP);";
$link->query($sql);
// INSERT INTO channels (name, email, Vname, CustomVname) VALUES ('Adrianek404', 'adr.rzeszutek@gmail.com', 'abc000', 'abc000')
$sql = "CREATE TABLE IF NOT EXISTS channels(id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                  name VARCHAR(50) NOT NULL UNIQUE,
                                  email VARCHAR(50) NOT NULL,
                                  Vname VARCHAR(255) NOT NULL,
                                  CustomVname VARCHAR(250) NOT NULL UNIQUE,
                                  videos TEXT(10000) DEFAULT '-',
                                  info TEXT(1000) DEFAULT 'Nie podano informacji',
                                  joined DATETIME DEFAULT CURRENT_TIMESTAMP,
                                  channelIMG TEXT(10000) default '-',
                                  bannerIMG TEXT(10000) default '-',
                                  followers INT(200) DEFAULT 0,
                                  partner BOOLEAN DEFAULT 0)";
$link->query($sql);
?>