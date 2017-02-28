#What does it do?
Dumps an entire schema's DB2 for i data objects (Table,Views,Procedures) into SQL source by using QSYS2.GENERATE_SQL and generates insert into statements for the data in the tables.

#Copy the source to your computer
```sh
git clone https://github.com/phpdave/DB2-for-IBMi.git
```

#Configure 
1. Modify ```DB2-for-IBMi/PHP/DB2Dump/includes/DB.php``` with your database name, username and password for your IBM i
2. Modify index.php to change the schema you want to dump
3. Run it

#To Run 
1. Copy ```DB2-for-IBMi/PHP/DB2Dump/``` folder to your machine's htdocs folder and navigate to the index.php page (i.e. for me I have PHP running on Nginx on http://spaces.litmis.com:61184/db2dump/index.php)
2. Or go to the command line and go to the PHP/DB2Dump folder and run:
```sh
php index.php
```

#Output
1. By default the program outputs the sql dump to the browser with line breaks ```<br>```
2. You can change the output to a file by calling 
```php
$dumper->SetOutputType(OutputTypes::_FILE);
$dumper->SetFileName('/path/to/where/you/want/thesql/dumped.sql');
```
