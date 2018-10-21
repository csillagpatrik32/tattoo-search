# Remove symlink
sudo rm -Rf /var/www/tattoosearch_old &&
sudo cp -R /var/www/tattoosearch_current /var/www/tattoosearch_old/ &&
sudo rm /var/www/tattoosearch &&
sudo rm -R /var/www/tattoosearch_current &&
# Create symlink to older version &&
sudo ln -s /var/www/tattoosearch_old /var/www/tattoosearch