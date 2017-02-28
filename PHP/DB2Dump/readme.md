#Copy the source to your computer
1. git clone https://github.com/phpdave/DB2-for-IBMi.git

#Configure 
1. Modify ```includes/DB.php``` with your database name, username and password for your IBM i
2. Modify index.php to change the schema you want to dump
3. Run it

#To Run 
1. Copy DB2Dump folder under the PHP folder to your machine's htdocs folder and navigate to the index.php page (i.e. for me I have PHP running on Nginx on http://spaces.litmis.com:61184/index.php)
2. Or go to the command line and do 
```
php index.php
```
to run it
