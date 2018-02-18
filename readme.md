# The Developers Devops Toolkit

This project is a devops setup for independent developers. The objective is to provide an intuitive & easy to use setup for developers to build, maintain, and deploy LAMP-based websites & applications.

This is intended to be a professional production line which will maintain all necessary environments for a developer or team of developers. 

## Contributors
I began working on this project as a fork of "Jasodeep Chatterjee"'s ansible-lamp-stack-playbook repository on GitHub (https://github.com/jasodeep/ansible-lamp-stack-playbook). Although I do have plans to rework the code-base the foundation of this is still owed to him. 

Also a big thanks goes out to my current employer Palo Alto Software. Although this project was constructed on personal time they provided me the initial opportunity to begin learning about DevOps which ultimately inspired me to create this project.

## License / Restrictions
There is currently no license on this code. By all means take it and do whatever you want with it. You can sell it, use it personally, use it commercially, or whatever your heart so desires. If you feel the need to give credit you're absolutely welcome to do so - although it is by no means required.

## Feedback, Suggestions, and Improvements
I am currently using this project for my own purposes and I fully intend to maintain and expand this project. Any feedback offered will be valued highly. If you offer a suggestion and it gets implemented or you make code contributions I'll be happy to list you as a contributor.

## Requirements
### Assumed Knowledge
I've made the following assumptions with this project and documentation:
- You are a LAMP developer or a Devops Specialist
- You have at least basic knowledge of PHP, MYSQL, Apache, and Linux
- You have some level of familiarity with the other technologies used (such as VirtualBox, Ansible, Vagrant, Redis, etc)

### Hardware Requirements
The development environment was designed to run on a Mac computer - this is untested on all other environments. If you use this on a Windows or Linux computer please feel free to submit pull requests or supply feedback on performance. Other project contributors are welcome and feedback is appreciated. The only environment currently supported is OSX though.

You will need an Apple computer with decent hardware. I recommend a 2.3ghz i7 processor with at least 16gb of ram. You can probably run this on significantly less resources you you will need to adjust the VirtualBox settings in order to do so. This is a professional development environment so I've made the assumption you have professional equipment that can do the job.

## What's Included
The Developers Devops Toolkit current includes a selection of tools used to provision development & production environments. In addition to the provisioning scripts to create & maintain various environments a "project provisioner" is also included to help you get things up & running quickly. It's not required that you use the project provisioner but I do strongly recommend it.

### Technology Used
In order to use the Developers Devops Toolit you will need to download & install the following items on your computer:
- Vagrant (2.0.0) - for provisioning & maintaining the local development environment
- Ansible (2.4.0.0) - for provisioning and maintaining all servers (local dev, production, and any others)
- Virtualbox (5.1.28) - for creating the virtual machine used as the local development environment

### Current Supported Environments
- Development Machine
- Production Server

### What's in the actual environments
Here's an overview of the different things that come together to form the LAMP production and development environments.

#### Core Server Software
- Ubuntu 16.04
- Apache 2.4
- MySQL 5.7
- PHP 7.0
- PHP-FPM
- Redis 3.0.6

#### PHP Modules
- Everything included by default with PHP 7.0
- php-fpm
- php-gd
- php-curl
- php-mysql
- php-dom
- php-xml
- php-zip
- php-mbstring
- php-odbc
- php-mcrypt

### How to use the development environment
After installing all required softare listed in the requirements section of this file - clone this repository, go into the directory, and type "vagrant up". All provisioning instructions will be ran automatically. 

If you are unfamiliar with how to use vagrant further, here's a good overview: https://www.vagrantup.com/intro/getting-started/

The default MySQL credentials are:
- Username: root
- Password: root

Once your dev machine is provisioned you can either manually configure your projects by placing them in the "projects" folder inside of this repository - or if your projects are configured properly you can pull them in using the project provisioner.

### How to provision a production server
The following instructions should be done on your computer, **not** inside of your development environment.

1) Setup a VPN node somewhere running Ubuntu 16.04 (I prefer DigitalOcean personally).
2) Make sure you have an SSH key configured on your computer with access to the node you just setup (verify it by doing **ssh root@that_servers_ip**)
3) Add the server into the Ansible Hosts file and tell Ansible the path to the SSH key used to login to it. 
    - **sudo nano /etc/ansible/hosts**
    - you should have a record that looks something like this under [All]:
    
    138.12.32.1 ansible_ssh_private_key_file=/Users/alexw/.ssh/id_rsa ansible_ssh_user=root
    
4) In your terminal - go to the folder for this repository and CD into the "ansible" directory.
5) Run this command: **ansible-playbook lamp-playbook.yml**
6) After the instructions have finished running, open your server in a web browser (go to http://your_servers_ip/) - this should return a phpinfo() page, if it does then your new production server provisioned correctly.

When updates are made to the production server you will need to rerun the ansible-playbook command to carry the updates over.

Now that your production server is provisioned you will need to either manually install your websites on the server or use the project provisioner to set them up for you.

### Project Provisioner

The Project Provisioner is stored in the /vagrant folder in your dev environment and the /var/project_provisioner/ folder on other servers and is used as a simple way of pulling various projects into this list. The Project Provisioner assumes that you're projects are built with specific files in specific places. 

#### How It Works

All projects are currently managed as GIT repositories. No other types of projects are currently supported by the Project Provisioner. On a basic list the Project Provisioner takes an array of project repositories stored in /vagrant/projects-list.php, clones them, places symlinks to them in the /home/ directory inside of the environment you're running it on, and then checks various rules for to determine if other things such as Apache, MySQL, and SSL can be automatically configured for that project. The objective of the Project Provisioner is simply to make getting up and running easier for personal projects in the event you need to provision a new environment or reprovision an existing one.

#### How To Use / Instructions (locally)

First you'll need to copy the /vagrant/project-list-sample.php file to projects-list.php. Inside of there you'll want to configure the $mysql_user and $mysql_pass. Once you've configured your list of projects you can run the project provisioner via PHP CLI. To do this:

1) Open vagrant (**vagrant ssh**)
2) **cd /vagrant/**
3) **sudo php provision-projects.php**

#### How to Use / Instructions (Production)
1) SSH into your production server
2) **cd /var/project_provisioner/**
3) Make sure the projects-list.php is configured properly
4) **sudo php provision-projects.php**

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
