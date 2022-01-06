<?php

/**
 * connect to database, if the database is not created, create that.
 */
function connect_db($servername, $username, $password)
{
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "CREATE DATABASE IF NOT EXISTS myDb";
    if ($conn->query($sql) === TRUE) {
        printf("Database created successfully\n");
        $conn->select_db('myDb');
        printf("change database to myDb\n");
    } else {
        die("DATABASE creation failed: " . $conn->error);
    }
    return $conn;
}

/**
 * create table in the database
 */
function create_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS users(
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                surname VARCHAR(30) NOT NULL,
                email VARCHAR(50) UNIQUE)";
    if ($conn->query($sql) === TRUE) {
        printf("Table users created successfully\n");
    } else {
        die("Error creating table: %s" . $conn->error);
    }
}

/**
 * main function
 */
function main()
{
    $servername = "localhost";
    $username = "root";
    $password = "password";
    $conn = connect_db($servername, $username, $password);
    create_table($conn);
    $conn->close();
}

/**
 * run main function
 */
main();
