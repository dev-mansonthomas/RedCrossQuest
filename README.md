# RedCrossQuest
RedCrossQuest

Tool to manage fund raison on the street for French Red Cross



Installation

vi /etc/apache2/sites-available/004-rcq.domain.com.conf
######################################################
#NameVirtualHost *:80
<VirtualHost *:80>
	ServerAdmin  webmaster@localhost
	DocumentRoot /home/special/www-mansonthomas/rcq/public_html/
        ServerName   rcq.mansonthomas.com
 <Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>

	<Directory /home/special/www-mansonthomas/rcq/public_html/>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
	  Require all granted
	</Directory>

  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel warn

  ErrorLog  /var/log/apache2/error-rcq.mansonthomas.log
  CustomLog /var/log/apache2/access-rcq.mansonthomas.log combined


	ServerSignature On

</VirtualHost>
######################################################

cd /etc/apache2/sites-enabled
ln -s ../sites-available/004-rcq.domain.com.conf

#enable mod rewrite
a2enmod rewrite
service apache2 restart



Copy sources:
[19:57] tmanson@busyboxy:~/Dropbox/crf2/RedCrossQuest/server$ scp -r * thomas@domain.com:/home/special/www-domain/rcq/
[20:15] tmanson@busyboxy:~/Dropbox/crf2/RedCrossQuest/public_html$ scp -r * thomas@domain.com:/home/special/www-domain/rcq/public_html/


chown -R thomas:thomas /home/special/www-domain/rcq/
chown -R www-data:www-data /home/special/www-domain/rcq/logs/app.log


vi /home/special/www-mansonthomas/rcq/src/settings.php

change DB details and log level.



