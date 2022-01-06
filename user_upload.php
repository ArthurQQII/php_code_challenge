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

function format_validate_user($users)
{
    $output = [];
    $count = 0;
    foreach ($users as $user) {
        if (strlen($user['name']) == 0 || strlen($user['surname']) == 0) {
            die("name and surname should not be null\n");
        } else {
            if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                fprintf(STDOUT, "email is not valid   %s\n", $user['email']);
            } else {
                $output[$count]['name'] = ucfirst($user['name']);
                $output[$count]['surname'] = ucfirst($user['name']);
                $output[$count++]['email'] = strtolower($user['email']);
            }
        }
    }

    foreach ($output as  $q) {
        echo $q['name'] . "  " . $q['surname'] . "  " . $q['email'] . "\n";
    }
}

function read_csv_file($fileName)
{
    $file = fopen($fileName, "r");
    $users = [];
    $userCount = 0;
    $userData = fgetcsv($file);
    while (($userData = fgetcsv($file)) !== false) {
        if (count($userData) == 3) {
            $users[$userCount++] = [
                "name" => $userData[0],
                "surname" => $userData[1],
                "email" => $userData[2],
            ];
            //echo $users[$userCount - 1]['name'] . "  " . $users[$userCount - 1]['surname'] . "  " . $users[$userCount - 1]['email'] . "\n";
        }
    }
    format_validate_user($users);
}
/**
 * main function
 */
function main()
{
    // $servername = "localhost";
    // $username = "root";
    // $password = "password";
    // $conn = connect_db($servername, $username, $password);
    // create_table($conn);
    // $conn->close();
    read_csv_file('users.csv');
}

/**
 * run main function
 */
main();
