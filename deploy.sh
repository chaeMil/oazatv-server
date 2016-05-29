#!/bin/bash

echo 'checkout from master'
git checkout master

echo 'git pull'
git pull

#cd to web-project to install composer packages
cd web-project
composer install

#clear cache
echo 'to clear cache provide sudo password'
sudo rm -rf ./temp/cache/*
sudo rm -rf ./www/webtemp/*
echo 'done clearing cache'