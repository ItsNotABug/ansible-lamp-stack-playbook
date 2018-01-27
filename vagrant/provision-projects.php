<?php

/*
 * provision-projects.php is a simple tool I wrote to clone .git repositories and do some basic essential project setup for your development environment. The objective is to allow you to quickly get started with the environment, and also to  ake reprovisioning your development environment as easy as possible.
 *
 * At a minimum this will clone the repository and create a symbolic link inside of vagrant to /home/$project_name.
 *
 * TODO: Support having a project-provision.ini file in different projects to allow for more customizable provisioning options. This would allow for things like: variable apache configuration locations, auto-creation of developer MySQL credentials, customized provisioning shell scripts, project-specific database provisioning, etc.
 */

if(!file_exists('projects-list.php')) {
	die('Missing projects-list.php file...'.PHP_EOL);
}
require_once('projects-list.php');

foreach($projects as $project) {
	echo "Starting {$project['name']}".PHP_EOL;
	$project_folder = '../projects/'.$project['name'];
	if(!is_dir($project_folder)) {
		echo "- Cloning repository...".PHP_EOL;
		shell_exec( 'cd /vagrant/projects/ && git clone ' . $project['repository'] . ' ' . $project['name'] .' && git checkout dev');
		echo "- Configuring symbolic links".PHP_EOL;
		if(symlink("/vagrant/projects/{$project['name']}", "/home/{$project['name']}")) {
			echo "- Created symlink for /home/{$project['name']}".PHP_EOL;
			$projects_home_folder = '/home/'.$project['name'];
		} else {
			echo "Failed to create /home/{$project['name']} sym link.".PHP_EOL;
		}

		echo "Checking for apache configurations...".PHP_EOL;
		$apache_found = 0;
		if(file_exists($project_folder."/configs/apache/{$project['name']}.conf")) {
			echo "-- Standard Apache configs found, creating symlink".PHP_EOL;
			if(symlink($project_folder."/configs/apache/{$project['name']}.conf", "/etc/apache2/sites-available/{$project['name']}.conf")) {
				echo " -- Standard Apache configuration symlink created, site is now available".PHP_EOL;
				shell_exec("a2ensite {$project['name']}.conf");
				$apache_found++;
			} else {
				echo "-- FAILED to create apache symlink. Apache configurations must be manually setup for this project.".PHP_EOL;
			}
		}

		if(file_exists($project_folder."/configs/apache/{$project['name']}.conf")) {
			echo "-- SSL Apache configs found, creating symlink".PHP_EOL;
			if(symlink($project_folder."/configs/apache/{$project['name']}-ssl.conf", "/etc/apache2/sites-available/{$project['name']}-ssl.conf")) {
				echo " -- SSL Apache configuration symlink created, site is now available".PHP_EOL;
				shell_exec("a2ensite {$project['name']}-ssl.conf");
				$apache_found++;
			} else {
				echo "-- FAILED to create apache symlink. Apache configurations must be manually setup for this project.".PHP_EOL;
			}
		}

		if($apache_found === 0){
			echo "-- Apache configuration not found in $project_folder/configs/apache/{$project['name']}.conf. Apache configurations must be setup manually for this project.".PHP_EOL;
		}

		echo "Attempting to configure MySQL Databases...".PHP_EOL;

		if(is_dir($project_folder.'/configs/db/')) {
			$dbfiles = scandir($project_folder.'/configs/db/');
			sort($dbfiles);
			foreach($dbfiles as $dbfile) {
				if($dbfile !== '.' && $dbfile !== '..') {
					echo " -- Processing {$dbfile}...".PHP_EOL;
					shell_exec("mysql -u{$mysql_user} -p{$mysql_pass} < {$project_folder}/{$dbfile}");
				}
			}
		}
		unset($dbfiles, $dbfile);

		echo "Attempting to create symbolic links for SSL file...".PHP_EOL;
		if(is_dir($project_folder.'/configs/ssl/')) {
			$ssl_files = scandir($project_folder.'/configs/ssl/');
			foreach($ssl_files as $ssl_file) {
				if($ssl_file !== '.' && $ssl_file !== '..') {
					if(symlink("{$projects_home_folder}/configs/ssl/$ssl_file", "/etc/apache2/ssl/{$ssl_file}")) {
						echo "- Created symlink for /etc/apache2/ssl/{$ssl_file}".PHP_EOL;
					} else {
						echo "Failed to create /etc/apache2/ssl/{$ssl_file} sym link.".PHP_EOL;
					}
				}
			}
		}

	} else {
		echo "- Project already configured".PHP_EOL;
	}

}

echo PHP_EOL.'Testing Apache Configurations'.PHP_EOL;
shell_exec('apachectl configtest');

echo PHP_EOL.'Restarting Apache...'.PHP_EOL;
shell_exec('service apache2 restart');

echo PHP_EOL.PHP_EOL.'Finished provisioning.'.PHP_EOL.PHP_EOL;