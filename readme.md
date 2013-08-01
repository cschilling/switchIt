## switchIt ##

#### About ####
switchIt is a webGui that uses the *raspberry-remote*-project to switch radio-controlled switches

#### Requirements ####
* *raspberry-remote*-project
* Apache2 with PHP

#### Installation ####
1. go to the document root of your webserver (e.g. `cd /var/www`)
1. `git clone git@git.sits-serv.de:cschilling/switchIt.git switchIt`
1. `cd switchIt`
1. `curl -sS https://getcomposer.org/installer | php`
1. `php composer.phar install`
1. `chown -R www-data:www-data /var/www/switchIt`
1. `chmod +x src/SwitchIt/daemon/switchIt_daemon.init`
