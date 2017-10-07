#!/usr/bin/env bash
# Install ansible dependencies
apt-get install -qqy libpython-stdlib python-apt python-minimal

# make sure provision-projects.php.sh has correct permissions
chmod 0755 /vagrant/vagrant/provision-projects.php.sh