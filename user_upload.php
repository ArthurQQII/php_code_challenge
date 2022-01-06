<?php

/**
 * connect to database, if the database is not created, create that.
 */
function connect_db($servername, $username, $password)
{
    $conn = null;
    try {
        $conn = new mysqli($servername, $username, $password);
    } catch (Throwable $error) {
        die(sprintf("Access denied for user '%s'@'%s'", $username, $servername));
    }
    $sql = "CREATE DATABASE IF NOT EXISTS myDb";
    if ($conn->query($sql) === TRUE) {
        printf("Database created successfully\n");
        $conn->select_db('myDb');
        printf("Change database to myDb\n");
    } else {
        die("DATABASE creation failed: " . $conn->error);
    }
    return $conn;
}

/**
 * check the table exists or not
 */
function check_db_table_exist($conn, $tableName)
{
    try {
        if ($conn->query("select 1 from " . $tableName) == TRUE) {
            return TRUE;
        };
    } catch (Throwable $error) {
        return FALSE;
    }
    return FALSE;
}

/**
 * create table in the database
 */
function create_table($conn)
{
    $sql = "DROP TABLE IF EXISTS users;"; //rebuild the table
    $conn->query($sql);
    $sql = "CREATE TABLE IF NOT EXISTS users(
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                surname VARCHAR(30) NOT NULL,
                email VARCHAR(50) UNIQUE)";
    if ($conn->query($sql) === TRUE) {
        printf("Table users created/rebuild successfully\n");
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
                fprintf(STDOUT, "------------------------------------------\n");
                fprintf(STDOUT, "#Email is not valid   %s\n", $user['email']);
                fprintf(STDOUT, "------------------------------------------\n");
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
            echo "Error:  Fail to insert the data\n  ";
            echo sprintf(
                "name: %s  email: %s\n",
                $user['name'] . " " . $user['surname'],
                $user['email']
            );
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
        if ($strChar === chr(127)) {
            if (strlen($passInput) != 0) {
                echo chr(27) . "[1D";
                echo chr(27) . "[1P";
                $passInput = substr_replace($passInput, "", -1);
            }
        } else {
            $passInput .= $strChar;
            echo ("*");
        }
    }
    echo ("\n");
    return $passInput;
}

/**
 * usage function
 */
function _usage()
{
    echo
    "usage: php user_upload.php [--file <filename>] [--create_table]
        [--dry_run] [-u <username>] [-p] [-h <hostname>] [--hep]\n\n";
    echo "  file             name of the CSV to be parsed\n";
    echo "  create_table     the MySQL users table will be built\n";
    echo "  dry_run          this will be used with the --file directive in case we\n";
    echo "                   want to run the script but not insert into the DB. All\n";
    echo "                   other functions will be executed, but the database won't be altered\n";
    echo "  u                MySQL username\n";
    echo "  p                MySQL password\n";
    echo "  h                MySQL host\n";
    echo "  help             which will output the above list of directives with details.\n";
    die();
}

/**
 * main function
 */
function main()
{
    $shortopts  = "u:ph:";
    $longopts = array(
        "file:",
        "create_table",
        "dry_run",
        "help"
    );
    $options = getopt($shortopts, $longopts);
    if (
        array_key_exists("help", $options) ||
        !array_key_exists("u", $options) || !array_key_exists("h", $options)
    ) {
        _usage();
    }
    $options['password'] = array_key_exists("p", $options) ? get_password_input() : "";
    $conn = connect_db($options['h'], $options['u'], $options['password']);
    if (array_key_exists("create_table", $options)) {
        create_table($conn);
        $conn->close();
        return;
    } else {
        if (!check_db_table_exist($conn, "users")) {
            die("Table users not exists");
        }
    }
    if (!array_key_exists("file", $options)) {
        _usage();
    }
    $data = read_csv_file($options['file']);
    if (array_key_exists("dry_run", $options)) {
        $conn->close();
        return;
    }
    db_insert($conn, $data);
    $conn->close();
}

/**
 * run main function
 */
main();
