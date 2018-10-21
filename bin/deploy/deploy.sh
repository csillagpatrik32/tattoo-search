sudo rm /var/www/tattoosearch &&
sudo ln -s /var/www/tattoosearch_current /var/www/tattoosearch &&
cd /var/www/tattoosearch &&
sudo chown -R www-data:www-data /var/www/tattoosearch_current &&
sudo chown -h www-data:www-data /var/www/tattoosearch &&
sudo service apache2 restart