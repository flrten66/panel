#!/bin/sh
Infon()
{
 printf "\033[1;32m$@\033[0m"
}
Info()
{
 Infon "$@\n"
}
Error()
{
 printf "\033[1;31m$@\033[0m\n"
}
Error_n()
{
 Error "$@"
}
Error_s()
{
 Error "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - "
}
log_s()
{
 Info "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - "
}
log_n()
{
 Info "$@"
}
log_t()
{
 log_s
 Info "- - - $@"
 log_s
}
log_tt()
{
 Info "- - - $@"
 log_s
}


RED=$(tput setaf 1)
red=$(tput setaf 1)
green=$(tput setaf 2)
yellow=$(tput setaf 3)
white=$(tput setaf 7)
reset=$(tput sgr0)
toend=$(tput hpa $(tput cols))$(tput cub 6)
YELLOW=$(tput setaf 3)
BLUE=$(tput setaf 4)
MAGENTA=$(tput setaf 5)
LIME_YELLOW=$(tput setaf 190)
CYAN=$(tput setaf 6)
VER=`cat /etc/issue.net | awk '{print $1$3}'`
OS=$(lsb_release -s -i -c -r | xargs echo |sed 's; ;-;g' | grep Ubuntu)

install_panel()
{
	clear
	if [ $VER = "Debian9" ]; then
		read -p "${white}Пожалуйста, введите домен или IP:${reset}" DOMAIN
		log_n "${BLUE}Adding Repo"
		echo "deb http://deb.debian.org/debian stretch main" > /etc/apt/sources.list
		echo "deb-src http://deb.debian.org/debian stretch main" >> /etc/apt/sources.list
		echo "deb http://security.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list
		echo "deb-src http://security.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list
		echo "deb http://deb.debian.org/debian stretch-updates main" >> /etc/apt/sources.list
		echo "deb-src http://deb.debian.org/debian stretch-updates main" >> /etc/apt/sources.list
		log_n "${BLUE}Updating packages"
		apt-get update
		log_n "${BLUE}Instaling Packages"
		apt install -y pwgen apache2 php7.0 php7.0-gd php7.0-mysql php7.0-ssh2 mariadb-server unzip htop sudo curl
		MYPASS=$(pwgen -cns -1 16) > /dev/null 2>&1
		CRONTOKE=$(pwgen -cns -1 14) > /dev/null 2>&1
		mysql -e "GRANT ALL ON *.* TO 'admin'@'localhost' IDENTIFIED BY '$MYPASS' WITH GRANT OPTION" 
		mysql -e "FLUSH PRIVILEGES" 
		log_n "${BLUE}Instaling PhpMyAdmin"
		echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/admin-user string admin" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/admin-pass password $MYPASS" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/app-pass password $MYPASS" |debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/app-password-confirm password $MYPASS" | debconf-set-selections > /dev/null 2>&1
		echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections > /dev/null 2>&1
		apt-get install -y phpmyadmin
		log_n "${BLUE}Setting Apache2 and MariaDB"
		cd /etc/apache2/sites-available/
		touch panel.conf
		FILE='panel.conf'
			echo "<VirtualHost *:80>">>$FILE
			echo "ServerAdmin hosting-rus@mail.ru">>$FILE
			echo "ServerName $DOMAIN">>$FILE
			echo "DocumentRoot /var/www">>$FILE
			echo "<Directory /var/www/>">>$FILE
			echo "Options Indexes FollowSymLinks">>$FILE
			echo "AllowOverride All">>$FILE
			echo "Require all granted">>$FILE
			echo "</Directory>">>$FILE
			echo "ErrorLog ${APACHE_LOG_DIR}/error.log">>$FILE
			echo "CustomLog ${APACHE_LOG_DIR}/access.log combined">>$FILE
			echo "</VirtualHost>">>$FILE
		cd 
		a2ensite panel 
		a2dissite 000-default
		a2enmod rewrite 
		ln -snf /usr/share/zoneinfo/Europe/Moscow /etc/localtime && echo Europe/Moscow > /etc/timezone 
		sudo sed -i -r 's~^;date\.timezone =$~date.timezone = "Europe/Moscow"~' /etc/php/7.0/cli/php.ini 
		sudo sed -i -r 's~^;date\.timezone =$~date.timezone = "Europe/Moscow"~' /etc/php/7.0/apache2/php.ini 
		sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php/7.0/apache2/php.ini
		sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 90M/g" /etc/php/7.0/apache2/php.ini 
		sed -i "s/post_max_size = 8M/post_max_size = 360M/g" /etc/php/7.0/apache2/php.ini 
		sed -i 's/127.0.0.1/0.0.0.0/g' /etc/mysql/mariadb.conf.d/50-server.cnf 
		sed -i 's/#max_connections        = 100/max_connections        = 1000/g' /etc/mysql/mariadb.conf.d/50-server.cnf 
		service apache2 restart 
		service mysql restart 
		log_n "${BLUE}Setting Cronrab"
		(crontab -l ; echo "0 0 * * * curl http://127.0.0.1/main/cron/index?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/1 * * * * curl http://127.0.0.1/main/cron/gameServers?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/5 * * * * curl http://127.0.0.1/main/cron/updateSystemLoad?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/5 * * * * curl http://127.0.0.1/main/cron/gamelocationstatsupd?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "0 */10 * * * curl http://127.0.0.1/main/cron/serverReloader?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/30 * * * * curl http://127.0.0.1/main/cron/updateStats?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/30 * * * * curl http://127.0.0.1/main/cron/stopServers?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "*/30 * * * * curl http://127.0.0.1/main/cron/stopServersQuery?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		(crontab -l ; echo "0 * */7 * * curl http://127.0.0.1/main/cron/clearLogs?token=$CRONTOKE") 2>&1 | grep -v "no crontab" | sort | uniq | crontab -
		service cron restart
		log_n "${BLUE}Download Panel"
		wget http://mc.hostinpl.ru/5.5pro/KoOD30OKDQ0A/hostin55pro.zip 
		log_n "${BLUE}Unpacking Panel"
		unzip hostin55pro.zip -d /var/www/ 
		rm hostin55pro.zip 
		rm -Rfv /var/www/html 
		log_n "${BLUE}Setting Config"
		sed -i "s/parol/${MYPASS}/g" /var/www/application/config.php
		sed -i "s/domen.ru/${DOMAIN}/g" /var/www/application/config.php
		sed -i "s/xtwcklwhw222a/${CRONTOKE}/g" /var/www/application/config.php
		log_n "${BLUE}Creating and Upload Database"
		mkdir /var/lib/mysql/hostin 
		chown -R mysql:mysql /var/lib/mysql/hostin 
		mysql hostin < /var/www/hostinpl.sql
		rm -rf /var/www/hostinpl.sql 
		log_n "${BLUE}Issuing rights"
		chown -R www-data:www-data /var/www
		chmod -R 770 /var/www
		chmod 777 /var/www/tmp
		chmod 777 /var/www/tmp/avatar
		chmod 777 /var/www/tmp/mods
		chmod 777 /var/www/tmp/tickets_img
		log_n "================== Установка HOSTINPL 5.5 PRO успешно завершена =================="
		Error_n "${green}Адрес: ${white}http://$DOMAIN"
		Error_n "${green}Адрес phpmyadmin: ${white}http://$DOMAIN/phpmyadmin"
		Error_n "${green}Данные для входа в phpmyadmin (база панели):"
		Error_n "${green}Пользователь: ${white}admin"
		Error_n "${green}Пароль: ${white}$MYPASS"
		Error_n "${green}Мониторинг нагрузки сервера: ${white}htop"
		Error_n "${green}Пропишите ключ сайта и секретный ключ от рекапчи в конфигурации панели."
		log_n "=============================== vk.com/hosting_rus ==============================="
		Info
		log_tt "${white}Добро пожаловать в установочное меню ${BLUE}HOSTINPL 5.5 PRO"
		Info "- ${white}1 ${green}- ${white}Подключить файл подкачки"
		Info "- ${white}2 ${green}- ${white}Правила использования"
		Info "- ${white}3 ${green}- ${white}Выход в главное меню"
		Info "- ${white}0 ${green}- ${white}Выход из установщика"
		log_s
		Info
		read -p "Пожалуйста, введите пункт меню: " case
		case $case in
		  1) install_swap;;
		  2) rules;;
		  3) menu;;
		  0) exit;;
		esac
	else
		Info
		log_tt "${white}К сожалению, настройка панели возможна только на OS Debian 9"
		Info "- ${white}0 ${green}- ${white}Выход"
		log_s
		Info
		read -p "Пожалуйста, введите пункт меню: " case
		case $case in
		  0) exit;;
		esac
	fi
}
		
install_location()
{
	clear
	if [ $VER = "Debian9" ]; then
		read -p "${white}Пожалуйста, введите  IP:${reset}" IP_SERV
		log_n "${BLUE}Adding Repo"
		echo "deb http://deb.debian.org/debian stretch main" > /etc/apt/sources.list
		echo "deb-src http://deb.debian.org/debian stretch main" >> /etc/apt/sources.list
		echo "deb http://security.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list
		echo "deb-src http://security.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list
		echo "deb http://deb.debian.org/debian stretch-updates main" >> /etc/apt/sources.list
		echo "deb-src http://deb.debian.org/debian stretch-updates main" >> /etc/apt/sources.list
		groupadd gameservers 
		log_n "${BLUE}Updating packages"
		apt-get update
		log_n "${BLUE}Instaling packages"
		apt-get install -y curl pwgen sudo unzip openssh-server apache2 php7.0 mariadb-server
		MYPASS=$(pwgen -cns -1 16) > /dev/null 2>&1
		mysql -e "GRANT ALL ON *.* TO 'admin'@'localhost' IDENTIFIED BY '$MYPASS' WITH GRANT OPTION"
		mysql -e "FLUSH PRIVILEGES"
		log_n "${BLUE}Instaling PhpMyAdmin"
		echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/admin-user string admin" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/admin-pass password $MYPASS" | debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/mysql/app-pass password $MYPASS" |debconf-set-selections > /dev/null 2>&1
		echo "phpmyadmin phpmyadmin/app-password-confirm password $MYPASS" | debconf-set-selections > /dev/null 2>&1
		echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections > /dev/null 2>&1
		apt-get install -y phpmyadmin
		log_n "${BLUE}Setting Apache2 and MariaDB"
		cd /etc/apache2/sites-available/
		touch phpmyadmin.conf
		FILE='phpmyadmin.conf'
			echo "<VirtualHost *:80>">>$FILE
			echo "ServerAdmin hosting-rus@mail.ru">>$FILE
			echo "ServerName $IP_SERV">>$FILE
			echo "DocumentRoot /usr/share/phpmyadmin">>$FILE
			echo "<Directory /usr/share/phpmyadmin/>">>$FILE
			echo "Options Indexes FollowSymLinks">>$FILE
			echo "AllowOverride All">>$FILE
			echo "Require all granted">>$FILE
			echo "</Directory>">>$FILE
			echo "ErrorLog ${APACHE_LOG_DIR}/error.log">>$FILE
			echo "CustomLog ${APACHE_LOG_DIR}/access.log combined">>$FILE
			echo "</VirtualHost>">>$FILE
		cd 
		a2ensite phpmyadmin
		a2dissite 000-default 
		a2enmod rewrite 
		ln -snf /usr/share/zoneinfo/Europe/Moscow /etc/localtime && echo Europe/Moscow > /etc/timezone 
		sudo sed -i -r 's~^;date\.timezone =$~date.timezone = "Europe/Moscow"~' /etc/php/7.0/cli/php.ini 
		sudo sed -i -r 's~^;date\.timezone =$~date.timezone = "Europe/Moscow"~' /etc/php/7.0/apache2/php.ini 
		sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php/7.0/apache2/php.ini
		sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 90M/g" /etc/php/7.0/apache2/php.ini 
		sed -i "s/post_max_size = 8M/post_max_size = 360M/g" /etc/php/7.0/apache2/php.ini
		sed -i 's/127.0.0.1/0.0.0.0/g' /etc/mysql/mariadb.conf.d/50-server.cnf 
		sed -i 's/#max_connections        = 100/max_connections        = 1000/g' /etc/mysql/mariadb.conf.d/50-server.cnf 
		service apache2 restart 
		service mysql restart 
		log_n "${BLUE}Create folder"
		mkdir /home/cp 
		mkdir /home/cp/backups
		mkdir /home/cp/gameservers 
		mkdir /home/cp/gameservers/files 
		log_n "${BLUE}Issuing rights"
		cd
		chown -R root /home/
		chmod -R 755 /home/ 
		chmod 700 /home/cp/backups 
		echo "Europe/Moscow" > /etc/timezone 
		log_n "${BLUE}Setting SSH"
		sudo sh -c "echo '' >> /etc/ssh/sshd_config" > /dev/null 2>&1
		sudo sh -c "echo 'DenyGroups gameservers' >> /etc/ssh/sshd_config" > /dev/null 2>&1
		service ssh restart 
		log_n "${BLUE}Instaling FTP Service"
		apt-get install -y proftpd
		sudo sh -c "echo 'DefaultRoot ~' >> /etc/proftpd/proftpd.conf" > /dev/null 2>&1
		sudo sh -c "echo 'RequireValidShell off' >> /etc/proftpd/proftpd.conf" > /dev/null 2>&1
		service proftpd restart 
		log_n "${BLUE}Instaling HTOP"
		apt-get install -y htop
		log_n "${BLUE}Instaling Docker"
		apt-get install -y apt-transport-https ca-certificates 
		curl -fsSL "https://download.docker.com/linux/debian/gpg" | apt-key add
		echo "deb [arch=amd64] https://download.docker.com/linux/debian stretch stable" > /etc/apt/sources.list.d/docker.list
		apt-get update 
		apt-get install -y docker-ce 
		log_n "${BLUE}Setting Docker"
		wget http://mc.hostinpl.ru/5.5pro/KoOD30OKDQ0A/1RlODIWJDD/Dockerfile
		docker build -t debian:stretch .
		rm Dockerfile
		log_n "================ Настройка игровой локации прошла успешно ================"
		Error_n "${green}Подключите локацию в панели управления"
		Error_n "${green}Базы данных серверов этой локации будут хранится на ней."
		Error_n "${green}Адрес phpmyadmin: ${white}http://$IP_SERV"
		Error_n "${green}Данные для входа в phpmyadmin:"
		Error_n "${green}Пользователь: ${white}admin"
		Error_n "${green}Пароль: ${white}$MYPASS"
		Error_n "${green}Мониторинг нагрузки сервера: ${white}htop"
		log_n "=========================== vk.com/hosting_rus ==========================="
		Info
		log_tt "${white}Добро пожаловать в установочное меню ${BLUE}HOSTINPL 5.5 PRO"
		Info "- ${white}1 ${green}- ${white}Подключить файл подкачки"
		Info "- ${white}2 ${green}- ${white}Правила использования"
		Info "- ${white}3 ${green}- ${white}Загрузить игры на локацию"
		Info "- ${white}4 ${green}- ${white}Выход в главное меню"
		Info "- ${white}0 ${green}- ${white}Выход из установщика"
		log_s
		Info
		read -p "Пожалуйста, введите пункт меню: " case
		case $case in
		  1) install_swap;;
		  2) rules;;
		  3) dop_games;;
		  4) menu;;
		  0) exit;;
		esac
	else
		Info
		log_tt "${white}К сожалению, настройка игровой локации возможна только на OS Debian 9"
		Info "- ${white}0 ${green}- ${white}Выход"
		log_s
		Info
		read -p "Пожалуйста, введите пункт меню: " case
		case $case in
		  0) exit;;
		esac
	fi
}

install_swap()
{
	clear
	read -p "${white}Введите размер файла подкачки (в GB):${reset}" GB
	fallocate -l ${GB}G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile 
    swapon /swapfile
    echo "/swapfile    none    swap    sw    0    0" >> /etc/fstab
	log_n "================ Файл подкачки размером в ${GB}GB успешно подключен! ==============="
}

rules()
{
	clear
	log_n ""
	log_n "===================== Правила эксплуатации панели управления HOSTINPL 5.5 PRO ======================"
	Info "- ${RED}1 ${green}- ${white}Запрещено передавать панель управления третьим лицам (друзьям/знакомым и т.д.)"
    Info "- ${RED}2 ${green}- ${white}Запрещено продавать панель."
    Info "- ${RED}3 ${green}- ${white}Запрещено сливать панель."
	Info "- ${RED}4 ${green}- ${white}Вы в праве изменить все разделы под себя."
	log_n "======================================== vk.com/hosting_rus ========================================"
	log_n ""
	log_n ""
	log_n "============================= Правила эксплуатации установщика панели =============================="
	Info "- ${RED}1 ${green}- ${white}Запрещено передавать установщик третьим лицам (друзьям/знакомым и т.д.)"
	Info "- ${RED}2 ${green}- ${white}Запрещено продавать установщик."
	Info "- ${RED}3 ${green}- ${white}Запрещено сливать установшик."
	log_n "======================================== vk.com/hosting_rus ========================================"
	log_n ""
	Info "- ${RED}В случаи нарушений правил, Вы будите добавлены в черный список HOSTINPL."
}

dop_games()
{
 clear
 log_s
 log_tt "${white}Добро пожаловать в меню загрузки игр для ${BLUE}HOSTINPL 5.5 PRO"
 Info "- ${white}1 ${green}- ${white}San Andreas: Multiplayer 0.3.7"
 Info "- ${white}2 ${green}- ${white}Criminal Russia: Multiplayer 0.3e"
 Info "- ${white}3 ${green}- ${white}Criminal Russia: Multiplayer 0.3.7"
 Info "- ${white}4 ${green}- ${white}United Multiplayer"
 Info "- ${white}5 ${green}- ${white}Multi Theft Auto: Multiplayer"
 Info "- ${white}6 ${green}- ${white}MineCraft: PE"
 Info "- ${white}7 ${green}- ${white}MineCraft"
 Info "- ${white}8 ${green}- ${white}Counter Strike 1.6"
 Info "- ${white}9 ${green}- ${white}Counter Strike Source"
 Info "- ${white}0 ${green}- ${white}Выход в главное меню"
 log_s
 Info
 read -p "Пожалуйста, введите пункт меню: " case
 case $case in
  1) 
	clear
	mkdir /home/cp/gameservers/files/samp
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/samp.zip
	unzip samp.zip -d /home/cp/gameservers/files/samp
	rm samp.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;     
  2) 
	clear
	mkdir /home/cp/gameservers/files/crmp
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/crmp.zip
	unzip crmp.zip -d /home/cp/gameservers/files/crmp
	rm crmp.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  3) 
	clear
	mkdir /home/cp/gameservers/files/crmp037
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/crmp037.zip
	unzip crmp037.zip -d /home/cp/gameservers/files/crmp037
	rm crmp037.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac  
  ;;
  4) 
	clear
	mkdir /home/cp/gameservers/files/unit
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/unit.zip
	unzip unit.zip -d /home/cp/gameservers/files/unit
	rm unit.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  5) 
	clear
	mkdir /home/cp/gameservers/files/mta
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/mta.zip
	unzip mta.zip -d /home/cp/gameservers/files/mta
	rm mta.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  6) 
	clear
	mkdir /home/cp/gameservers/files/mcpe 
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/mcpe.zip
	unzip mcpe.zip -d /home/cp/gameservers/files/mcpe
	rm mcpe.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  7) 
	clear
	mkdir /home/cp/gameservers/files/mine72
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/mine72.zip
	unzip mine72.zip -d /home/cp/gameservers/files/mine72
	rm mine72.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac  
  ;;
  8) 
	clear
	mkdir /home/cp/gameservers/files/cs 
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/cs.zip
	unzip cs.zip -d /home/cp/gameservers/files/cs
	rm cs.zip
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  9) 
	clear
	mkdir /home/cp/gameservers/files/css
	wget http://games.hostinpl.ru/5.5_pro/3UK2wOlGeXHAJ5JasH/css.zip
	unzip css.zip -d /home/cp/gameservers/files/css 
	rm css.zip 
	log_n "Игра успешно загружена на ваш сервер, включите ее для заказа в панели управления."
	Info "- ${white}1 ${green}- ${white}Вернуться в меню выбора игр"
	Info "- ${white}0 ${green}- ${white}Вернуться в главное меню"
	log_s
	Info
	read -p "Пожалуйста, введите пункт меню: " case
	case $case in
		1) dop_games;;     
		0) menu;;
	esac 
  ;;
  0) menu;;
 esac
}

menu()
{
 clear
 log_s
 log_tt "${white}Добро пожаловать в установочное меню ${BLUE}HOSTINPL 5.5 PRO"
 Info "- ${white}1 ${green}- ${white}Настроить веб-часть"
 Info "- ${white}2 ${green}- ${white}Настроить игровую локацию"
 Info "- ${white}3 ${green}- ${white}Загрузить игры на настроенную игровую локацию"
 Info "- ${white}4 ${green}- ${white}Подключить файл подкачки"
 Info "- ${white}5 ${green}- ${white}Правила использования"
 Info "- ${white}0 ${green}- ${white}Выход"
 log_s
 Info
 read -p "Пожалуйста, введите пункт меню: " case
 case $case in
  1) install_panel;;     
  2) install_location;;
  3) dop_games;;
  4) install_swap;;
  5) rules;;
  0) exit;;
 esac
}
menu