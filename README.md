# php_code_challenge

## Description 

> This php script will analyse the csv file and store the user information to the mysql database.
	
### csv file 

| name   | surname  | email          |
| ------ | -----    |----------------|
| Daley  | thompson | daley@yahoo.co.nz|
| Phil   | CARRY    |phil@open.edu.au |


## How to use

> usage: php user_upload.php [--file <filename>] [--create_table]  
> 			[--dry_run] [-u <username>] [-p] [-h <hostname>] [--hep]  
	   
- --file [csv file name] – this is the name of the CSV to be parsed
-  --create_table – this will cause the MySQL users table to be built (and no further
-  action will be taken)
- --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
-  -u – MySQL username
- -p – MySQL password
- -h – MySQL host
-  --help – which will output the above list of directives with details.

	
	
### create table
```
arthurqi@air ~/D/j/s/php_code_challenge (main)> php user_upload.php -u root  -h localhost --file users.csv  --create_table -p
Password:*******
Database created successfully
Change database to myDb
Table users created/rebuild successfully
```
	
### insert data
```
arthurqi@air  ~/D/j/s/php_code_challenge (main)> php user_upload.php -u root  -h localhost --file users.csv -p
Password: *******
Database created successfully
Change database to myDb
------------------------------------------
#Email is not valid   edward@jikes@com.au
------------------------------------------
Error:  Fail to insert the data
  name: HAMISH Jones  email: ham@seek.com
```

### dry run
	
```
arthurqi@air ~/D/j/s/php_code_challenge (main)> php user_upload.php -u root  -h localhost --file users.csv --dry_run -p
Password: ********
Database created successfully
Change database to myDb
------------------------------------------
#Email is not valid   edward@jikes@com.au
------------------------------------------
```
	
### rebuild table
```
arthurqi@air ~/D/j/s/php_code_challenge (main)> php user_upload.php -u root  -h localhost --file users.csv  --create_table -p
Password:*******
Database created successfully
Change database to myDb
Table users created/rebuild successfully
```
	
