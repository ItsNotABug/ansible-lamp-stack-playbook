#!/bin/sh

ps auxw | grep mysqld | grep -v grep > /dev/null

if [ $? != 0 ]
then
        /etc/init.d/mysql start > /dev/null
fi