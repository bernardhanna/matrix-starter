#!/bin/bash
# A script that makes a package for WordPress Theme Directory
txtbold=$(tput bold)
boldyellow=${txtbold}$(tput setaf 3)
boldgreen=${txtbold}$(tput setaf 2)
boldwhite=${txtbold}$(tput setaf 7)
yellow=$(tput setaf 3)
green=$(tput setaf 2)
white=$(tput setaf 7)
txtreset=$(tput sgr0)

mkdir -p /var/www
mkdir -p /var/www/matrixdev
mkdir -p /var/www/matrixdev/content
mkdir -p /var/www/matrixdev/content/themes
rm /var/www/matrixdev/content/themes/matrix-starter.zip
sh /var/www/matrixdev/content/themes/matrix-starter/bin/air-move-out.sh
cd /var/www/matrixdev/content/themes/
zip -r matrix-starter.zip matrix-starter
sh $HOME/matrix-tmp/bin/matrix-move-in.sh
