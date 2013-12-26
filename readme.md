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
1. `chmod +x src/SwitchIt/daemons/switchIt_daemon.init`
1. `cd src/SwitchIt/daemons/`
1. `ln -s /var/www/switchIt/src/SwitchIt/daemons/switchIt_daemon.init /etc/init.d/switchIt_daemon.init`
1. install the daemon so it will be up and running after a reboot with `update-rc.d switchIt_daemon.init defaults`
1. start the daemon `/etc/init.d/switchIt_daemon.init start`
1. check, if the daemon is running `/etc/init.d/switchIt_daemon.init status`
1. install the cronjob-watchdog by typing `crontab -e`
1. add the line `  * *  *   *   *     php /var/www/switchIt/src/SwitchIt/daemons/cron.php`
1. edit the file `/var/www/switchIt/config/prod.php` and check if the var `$sendBin` points to the send-binary of the *raspberry-remote*-project
1. That's it! Surf to `http://{IP_OF_YOUR_RASPBERRY}/switchIt/web`

#### Troubeshooting ####
If the webpage shows an *404* error, make sure that the `.htaccess`-file of switchIt is used by the webserver.
Open up the file `/etc/apache2/sites-available/default` and check that under the section `<Directory /var/www/>` `AllowOverride` is set to `All`
Also check, that mod_rewrite is enabled! (If the file `/etc/apache2/mods-enabled/rewrite.load` exists)
If not do a:
`ln -s ../mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load`
and restart apache2 by
`/etc/init.d/apache2 restart`
