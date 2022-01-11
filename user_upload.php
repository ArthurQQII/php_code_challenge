<?php

/**
 * error hanld and exit
 */
function error_exit($errMsg, $exitCode)
{
    fprintf(STDERR, "ERROR: " . $errMsg);
    exit($exitCode);
}

/**
 * connect to database, if the database is not created, create that.
 */
function connect_db($servername, $username, $password)
{
    $conn = false;
    try {
        $conn = mysqli_connect($servername, $username, $password);
        if ($conn === false) {
            error_exit("Database server connect fail\n", 1);
        }
        echo "Database server connect successfully\n";
    } catch (Exception $e) {
        error_exit("Database server connect fail\n", 1);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS myDb";
    if ($conn->query($sql) === TRUE) {
        printf("Database connect successfully\n");
        $conn->select_db('myDb');
        printf("Change database to myDb\n");
    } else {
        error_exit("DATABASE creation failed: \n", 2);
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
        error_exit("Error creating table", 3);
    }
}

/**
 * check the format of the name, surname and email,
 * if the email is not valid, print the error message and no insert happen
 */
function format_validate_user($users)
{
    $output = [];
    foreach ($users as $user) {
        if (strlen($user['name']) == 0 || strlen($user['surname']) == 0) {
            die("name and surname should not be null\n");
        } else {
            if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                fprintf(STDOUT, "------------------------------------------\n");
                fprintf(STDOUT, "#Email is not valid   %s\n", $user['email']);
                fprintf(STDOUT, "------------------------------------------\n");
            } else {
                $tmpUser = array(
                    "name" => ucfirst($user['name']),
                    "surname" => ucfirst($user['surname']),
                    "email" => strtolower($user['email']),
                );
                array_push($output, $tmpUser);
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
    $dataField = fgetcsv($file);
    if (count($dataField) != 3) {
        error_exit("Csv file should only have 3 fields: name, surname, email\n", 4);
    }
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
            $result = $connnect->query($sql);
            if ($result === FALSE) {
                echo "Error:  Fail to insert the duplicated data\n  ";
                echo sprintf(
                    "name: %s  email: %s\n",
                    $user['name'] . " " . $user['surname'],
                    $user['email']
                );
            }
        } catch (Exception | Throwable $t) {
            echo "Error:  Fail to insert the duplicated data\n  ";
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
 * check the file type and exist or not
 */
function file_check($file)
{
    if (!file_exists($file)) {
        return false;
    }
    if (count(explode(".", $file)) != 2 || explode(".", $file)[1] != "csv") {
        return false;
    }
    return true;
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
        exit(0);
    } else {
        if (!check_db_table_exist($conn, "users")) {
            error_exit("Table users does not exist\n", 5);
        }
    }
    if (!array_key_exists("file", $options)) {
        _usage();
        error_exit("No csv file selected", 6);
    }
    if (!file_check($options['file'])) {
        error_exit("Selected file does not exist or is not csv file\n", 7);
    }
    $data = read_csv_file($options['file']);
    if (array_key_exists("dry_run", $options)) {
        $conn->close();
        exit(0);
    }
    db_insert($conn, $data);
    $conn->close();
    exit(0);
}

set_error_handler(function () {
});
/**
 * run main function
 */
main();
