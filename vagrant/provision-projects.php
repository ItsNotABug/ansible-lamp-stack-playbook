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

$projects_home_folder = '';

foreach($projects as $project) {
	echo "Starting {$project['name']}".PHP_EOL;
	$project_folder = '../projects/'.$project['name'];
	if(!is_dir($project_folder)) {
		$projects['git_branch'] = isset($projects['git_branch']) ? $projects['git_branch'] : 'master';
		echo "- Cloning repository...".PHP_EOL;
		shell_exec( 'cd /vagrant/projects/ && git clone ' . $project['repository'] . ' ' . $project['name'] );
		shell_exec("cd /vagrant/projects/{$project['name']} && git fetch origin && git checkout origin/{$project['git_branch']} -b {$project['git_branch']} ");
		echo "- Configuring symbolic links".PHP_EOL;
		if(symlink("/vagrant/projects/{$project['name']}", "/home/{$project['name']}")) {
			echo "- Created symlink for /home/{$project['name']}".PHP_EOL;
			$projects_home_folder = '/home/'.$project['name'];
		} else {
			echo "Failed to create /home/{$project['name']} sym link.".PHP_EOL;
		}
		if(!empty($projects_home_folder)) {
			echo "Checking for apache configurations..." . PHP_EOL;
			$apache_found = 0;
			if ( file_exists( $projects_home_folder . "/configs/apache/{$project['name']}.conf" ) ) {
				echo "-- Standard Apache configs found, creating symlink" . PHP_EOL;
				if ( symlink( $projects_home_folder . "/configs/apache/{$project['name']}.conf", "/etc/apache2/sites-available/{$project['name']}.conf" ) ) {
					echo " -- Standard Apache configuration symlink created, site is now available" . PHP_EOL;
					shell_exec( "a2ensite {$project['name']}.conf" );
					$apache_found ++;
				} else {
					echo "-- FAILED to create apache symlink. Apache configurations must be manually setup for this project." . PHP_EOL;
				}
			}

			if ( file_exists( $projects_home_folder . "/configs/apache/{$project['name']}-ssl.conf" ) ) {
				echo "-- SSL Apache configs found, creating symlink" . PHP_EOL;
				if ( symlink( $projects_home_folder . "/configs/apache/{$project['name']}-ssl.conf", "/etc/apache2/sites-available/{$project['name']}-ssl.conf" ) ) {
					echo " -- SSL Apache configuration symlink created, site is now available" . PHP_EOL;
					shell_exec( "a2ensite {$project['name']}-ssl.conf" );
					$apache_found ++;
				} else {
					echo "-- FAILED to create apache symlink. Apache configurations must be manually setup for this project." . PHP_EOL;
				}
			}

			if ( $apache_found === 0 ) {
				echo "-- Apache configuration not found in $projects_home_folder/configs/apache/{$project['name']}.conf. Apache configurations must be setup manually for this project." . PHP_EOL;
			}

			echo "Attempting to configure MySQL Databases..." . PHP_EOL;

			if ( is_dir( $projects_home_folder . '/configs/db/' ) ) {
				$dbfiles = scandir( $projects_home_folder . '/configs/db/' );
				sort( $dbfiles );
				foreach ( $dbfiles as $dbfile ) {
					if ( $dbfile !== '.' && $dbfile !== '..' ) {
						echo " -- Processing {$dbfile}..." . PHP_EOL;
						shell_exec( "mysql -u{$mysql_root_user} -p{$mysql_root_password} < {$projects_home_folder}/configs/db/{$dbfile}" );
					}
				}
			}
			unset( $dbfiles, $dbfile );

			echo "Attempting to create symbolic links for SSL directory..." . PHP_EOL;
			if ( is_dir( $project_folder . '/configs/ssl/' ) ) {
				$ssl_files = scandir( $project_folder . '/configs/ssl/' );
					if ( symlink( "{$projects_home_folder}/configs/ssl/", "/etc/apache2/ssl/{$project['name']}" ) ) {
						echo "- Created symlink for /etc/apache2/ssl/{$project['name']}" . PHP_EOL;
					} else {
						echo "Failed to create /etc/apache2/ssl/{$project['name']} sym link." . PHP_EOL;
					}

			}
		} else {
			echo "- Project did not clone correctly.".PHP_EOL;
		}
	} else {
		echo "- Project already configured".PHP_EOL;
	}


	$projects_home_folder = '';
}

echo PHP_EOL.'Testing Apache Configurations'.PHP_EOL;
shell_exec('apachectl configtest');

echo PHP_EOL.'Restarting Apache...'.PHP_EOL;
shell_exec('service apache2 restart');

echo PHP_EOL.PHP_EOL.'Finished provisioning.'.PHP_EOL.PHP_EOL;