<?php

/*
 * This is just a sample file. You should modify it to your own needs.
 *
 * In order to use SSH in the repository URL's you'll need to configure your SSH keys inside of your Vagrant box. Alternatively if you use HTTPS as in the examples below you'll simply be prompted for the password.
 */

$mysql_root_password = 'root';
$mysql_root_user = 'root';

$projects = [
	[
		'name' => 'alexthewebguy.net',
		'repository' => 'https://alexthewebguy@bitbucket.org/alexthewebguy/atrw-marketing-website.git'
	],
	[
		'name' => 'project-toolkit.dev',
		'repository' => 'https://github.com/ItsNotABug/project-toolkit.git'
	]
];