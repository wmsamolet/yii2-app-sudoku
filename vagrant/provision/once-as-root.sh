#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Import script args ==

timezone=$(echo "$1")

#== Provision script ==

info "Provision-script user: `whoami`"

export DEBIAN_FRONTEND=noninteractive

info "Configure timezone"
timedatectl set-timezone ${timezone} --no-ask-password

info "Prepare root password for MariaDB"
debconf-set-selections <<< "mariadb-server-10.3 mariadb-server/root_password password \"''\""
debconf-set-selections <<< "mariadb-server-10.3 mariadb-server/root_password_again password \"''\""
debconf-set-selections <<< "mariadb-server-10.3 mariadb-server/oneway_migration boolean true"
echo "Done!"

info "Update OS software"
apt update

info "Install additional software"
apt install -y software-properties-common
apt install -y git curl wget gnupg2 unzip ca-certificates lsb-release apt-transport-https mc

info "Install PHP"
wget https://packages.sury.org/php/apt.gpg
apt-key add apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php7.list
apt update
apt install -y php7.2 php7.2-cli php7.2-common php7.2-intl php7.2-curl php7.2-mysqlnd php7.2-gd php7.2-fpm php7.2-mbstring php7.2-xml

info "Remove apache packages and install nginx, mysql"
apt purge -y apache2 apache2-bin apache2-data apache2-utils
apt install -y nginx mariadb-server-10.3

info "Configure MySQL"
sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mariadb.conf.d/50-server.cnf
mysql -uroot <<< "CREATE USER 'root'@'%' IDENTIFIED BY ''"
mysql -uroot <<< "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'"
mysql -uroot <<< "DROP USER 'root'@'localhost'"
mysql -uroot <<< "FLUSH PRIVILEGES"
echo "Done!"

info "Configure PHP-FPM"
sed -i 's/user = www-data/user = vagrant/g' /etc/php/7.2/fpm/pool.d/www.conf
sed -i 's/group = www-data/group = vagrant/g' /etc/php/7.2/fpm/pool.d/www.conf
sed -i 's/owner = www-data/owner = vagrant/g' /etc/php/7.2/fpm/pool.d/www.conf
cat << EOF > /etc/php/7.2/mods-available/xdebug.ini
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_connect_back=1
xdebug.remote_port=9000
xdebug.remote_autostart=1
EOF
echo "Done!"

info "Configure NGINX"
sed -i 's/user www-data/user vagrant/g' /etc/nginx/nginx.conf
echo "Done!"

info "Enabling site configuration"
ln -s /app/vagrant/nginx/app.conf /etc/nginx/sites-enabled/app.conf
echo "Done!"

info "Initailize databases for MySQL"
mysql -uroot <<< "CREATE DATABASE yii2advanced"
mysql -uroot <<< "CREATE DATABASE yii2advanced_test"
echo "Done!"

info "Install Redis server"
apt install -y redis-server
sed -i 's/supervised no/supervised systemd/g' /etc/redis/redis.conf

info "Installing Ruby"
apt install -y ruby-full

info "Installing SQLite"
apt install -y libsqlite3-dev

info "Install composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

info "Install Node"
curl -sL https://deb.nodesource.com/setup_12.x | bash -
apt install -y nodejs
apt autoremove -y

info "Install Yarn"
npm install -g yarn
export PATH="$(yarn global bin):$PATH"

info "Install globally @vue/cli @vue/cli-service-global"
yarn global add @vue/cli @vue/cli-service-global

#info "Installing MailCatcher"
#gem update --system
#gem install mailcatcher

#info "Configuring MailCatcher"
#sed -i "s~;sendmail_path =~sendmail_path = /usr/bin/env catchmail -f noreply@sudoku.local~" /etc/php/7.2/fpm/php.ini
#sed -i "s~;sendmail_path =~sendmail_path = /usr/bin/env catchmail -f noreply@sudoku.local~" /etc/php/7.2/cli/php.ini
