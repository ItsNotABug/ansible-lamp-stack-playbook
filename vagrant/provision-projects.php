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
		} else {
			echo "Failed to create /home/{$project['name']} sym link.".PHP_EOL;
		}

		echo "Checking for apache configurations...".PHP_EOL;
		if(file_exists($project_folder."/configs/apache/{$project['name']}.conf")) {
			echo "-- Apache configs found, creating symlink".PHP_EOL;
			if(symlink($project_folder."/configs/apache/{$project['name']}.conf", "/etc/apache2/sites-available/{$project['name']}.conf")) {
				echo " -- Apache configuration symlink created, site is now available".PHP_EOL;
				shell_exec("a2ensite {$project['name']}.conf");
			} else {
				echo "-- FAILED to create apache symlink. Apache configurations must be manually setup for this project.".PHP_EOL;
			}
		} else {
			echo "-- Apache configuration not found in $project_folder/configs/apache/{$project['name']}.conf. Apache configurations must be setup manually for this project.".PHP_EOL;
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