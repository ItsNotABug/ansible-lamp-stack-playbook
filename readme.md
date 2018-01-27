### Project Provisioner

The Project Provisioner is stored in /the /vagrant folder and is used as a simple way of pulling various projects into this list. The Project Provisioner assumes that you're projects are built with specific files in specific places. 

#### How It Works

All projects are currently managed as GIT repositories. No other types of projects are currently supported by the Project Provisioner. On a basic list the Project Provisioner takes an array of project repositories stored in /vagrant/projects-list.php, clones them, places symlinks to them in the /home/ directory inside of the environment you're running it on, and then checks various rules for to determine if other things such as Apache, MySQL, and SSL can be automatically configured for that project. The objective of the Project Provisioner is simply to make getting up and running easier for personal projects in the event you need to provision a new environment or reprovision an existing one.

#### How To Use / Instructions

First you'll need to copy the /vagrant/project-list-sample.php file to projects-list.php. Inside of there you'll want to configure the $mysql_user and $mysql_pass. Once you've configured your list of projects you can run the project provisioner via PHP CLI. To do this:

1) Open vagrant (**vagrant ssh**)
2) **cd /vagrant/**
3) **sudo php provision-projects.php**

#### Standards and  Conventions

The Project Provisioner is designed to help automate as much as possible to get your new projects running in a given environment. In order to have this automation you will need to implement certain standards and conventions on your projects to make them compatible. This is easiest to do with new projects but shouldn't be a ton of work for existing projects as well.

##### Apache Configurations

If you have apache configuration files stored in proj_folder/configs/apache/proj_name.conf then those apache config files will will automatically be symlinked and enabled in apache.

EX: if my project name is AwesomePortfolio.com, I would have: AwesomePortfolio.com/configs/apache/AwesomePortfolio.com.conf

The Project Provisioner will check for two SSL files automatically per project:
proj_name.conf and proj_name-ssl.conf

EX: AwesomePortfolio.com.conf and AwesomePortfolio.com-ssl.confa

##### MySQL Databases

If your project uses MySQL you can store the setup files inside of the proj_name/cofigs/db/ folder with any naming convention that when passed through PHP's sort() function will ensure the files you want run first appear at the beginning of the list.

EX: 1a-create-database-and-user.sql, 2a-provision-structure.sql

EX: AwesomePortfolio.com/configs/db/1a-create-database-and-user.sql

###### SQL File Standards and Conventions

* All SQL files should implement a USE statement where applicable to ensure they go to the correct database
* Your first SQL file (named approperiately as described above) should implement a CREATE DATABASE statement. You should also create any MySQL users you're going to use inside of it. Be cautious with this as these DB files need to be part of your project's repository and therefore your unencrypted MySQL credentials will be stored there.
* Make sure you use DROP statements where approperiate as well.

##### SSL Configurations

SSL files are automatically checked for inside of the proj_name/configs/ssl/ directory. The Project Provisioner will automatically attempt to *symbolically link* any SSL **files or directories** found to /etc/apache2/ssl/

This will only look at files and directories directly inside of the /proj_name/configs/ssl folder and **will not** recurse through those directories.

EX: AwesomePortfolio.com/configs/ssl/AwesomePortfolio.com.crt will result in the following sym link

*/etc/apache2/ssl/AwesomePortfolio.com.crt --> /home/AwesomePortfolio.com/configs/ssl/AwesomePortfolio.com.crt*

If you have a directory (AwesomePortfolio.com/configs/ssl/AwesomePortfolio/) with several files inside of it (star.awesomeportfolio.com.crt, star.awesomeportfolio.com.key, and maybe gd_bundle.crt) then it will result in the following symbolic link:

*/etc/apache2/ssl/AwesomePortfolio/ --> /home/AwesomePortfolio/configs/ssl/AwesomePortfolio/*
