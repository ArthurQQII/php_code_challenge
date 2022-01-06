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
 * check the format of the name, surname and email,
 * if the email is not valid, print the error message and no insert happen
 */
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
                $output[$count]['surname'] = ucfirst($user['surname']);
                $output[$count++]['email'] = strtolower($user['email']);
            }
        }
    }
    return $output;
}

/**
 * read the file and output the formatted user information
 */
function read_csv_file($fileName)
{
    $file = fopen($fileName, "r");
    $users = [];
    $userCount = 0;
    $userData = fgetcsv($file);
    while (($userData = fgetcsv($file)) !== false) {
        if (count($userData) == 3) {
            $users[$userCount++] = [
                "name" => trim($userData[0]),
                "surname" => trim($userData[1]),
                "email" => trim($userData[2])
            ];
        }
    }
    return format_validate_user($users);
}

/**
 * insert the data to database
 */
function db_insert($connnect, $data)
{
    if (count($data) == 0) {
        return;
    }
    foreach ($data as $user) {
        $sql = sprintf(
            'INSERT INTO users (name, surname, email) VALUES ("%s", "%s", "%s");',
            $user['name'],
            $user['surname'],
            $user['email']
        );
        try {
            $connnect->query($sql);
        } catch (Throwable $error) {
            echo "Error: " . $sql . "\n fail to insert the data\n";
        }
    }
    
}

/**
 * get password input from stdin, show * when typing
 */
function get_password_input()
{
    readline_callback_handler_install('', function () {
    });
    echo ("Password: ");
    $passInput = '';
    while (true) {
        $strChar = stream_get_contents(STDIN, 1);
        if ($strChar === chr(10)) {
            break;
        }
        $passInput .= $strChar;
        echo ("*");
    }
    echo ("\n");
    return $passInput;
}

/**
 * main function
 */
function main()
{
    // $servername = "localhost";
    // $username = "root";
    // $password = "password";
    $shortopts  = "u:ph:";
    $longopts = array(
        "file:",
        "create_table",
        "dry_run",
        "help"
    );
    $options = getopt($shortopts, $longopts);
    $options['password'] = array_key_exists("p", $options) ? get_password_input() : "";
    $conn = connect_db($options['h'], $options['u'], $options['password']);
    create_table($conn);
    $data = read_csv_file($options['file']);
    db_insert($conn, $data);
    $conn->close();
    //echo var_dump($options);
}

/**
 * run main function
 */
main();
