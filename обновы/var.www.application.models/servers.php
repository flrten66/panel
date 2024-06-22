<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko (https://vk.com/id00v)
Developed by Alexandr Zemlyanoy (https://vk.com/mrsasha082)
Developed by Good Day (https://vk.com/goodday20)
*/
class serversModel extends Model {
	public function createServer($data) {
		
		$sql = "INSERT INTO `servers` SET ";
		$sql .= "`user_id` = '" . (int)$data['user_id'] . "', ";
		$sql .= "`game_id` = '" . (int)$data['game_id'] . "', ";
		$sql .= "`location_id` = '" . (int)$data['location_id'] . "', ";
		$sql .= "`server_mysql` = '0', ";
		$sql .= "`server_slots` = '" . (int)$data['server_slots'] . "', ";
		$sql .= "`server_port` = '" . (int)$data['server_port'] . "', ";
		$sql .= "`server_password` = '" . $this->db->escape($data['server_password']) . "', ";
		$sql .= "`server_status` = '" . (int)$data['server_status'] . "', ";
		$sql .= "server_date_reg = NOW(), ";
		
		if($data['test_periud'] == false){
            $sql .= "server_date_end = NOW() + INTERVAL " . (int)$data['server_months'] . " MONTH";
		} 
		elseif($data['test_periud'] == true) {
			$sql .= "`server_date_end` = NOW() + INTERVAL 3 DAY";
			$this->db->query("UPDATE `users` SET `test_server` = '2' WHERE `user_id` = '{$this->user->getId()}'");
		}
		$this->db->query($sql);
		$return=$this->db->getLastId();		
		return $return;
	}
	
	public function promisedServer($serverid) {		
		$this->db->query("UPDATE `servers` SET server_date_end = server_date_end +INTERVAL 3 DAY WHERE server_id = '" . (int)$serverid . "'");
		$this->updateServer($serverid, array('server_status' => 1));
		return true;
	}
	
	public function deleteServer($serverid) {
		$this->db->query("DELETE FROM `servers` WHERE server_id = '" . (int)$serverid . "'");
	}
	
	public function blockServer($serverid) {
		$sql = "DELETE FROM `servers` WHERE server_id = '" . (int)$serverid . "'";
		$this->db->query($sql);
	}
	
	public function updateServer($serverid, $data = array()) {
		$sql = "UPDATE `servers`";
		if(!empty($data)) {
			$count = count($data);
			$sql .= " SET";
			foreach($data as $key => $value) {
				$sql .= " $key = '" . $this->db->escape($value) . "'";
				
				$count--;
				if($count > 0) $sql .= ",";
			}
		}
		$sql .= " WHERE `server_id` = '" . (int)$serverid . "'";
		$query = $this->db->query($sql);
	}
	
	public function getServers($data = array(), $joins = array(), $sort = array(), $options = array()) {
		$sql = "SELECT * FROM `servers`";
		foreach($joins as $join) {
			$sql .= " LEFT JOIN $join";
			switch($join) {
				case "users":
					$sql .= " ON servers.user_id=users.user_id";
					break;
				case "games":
					$sql .= " ON servers.game_id=games.game_id";
					break;
				case "locations":
					$sql .= " ON servers.location_id=locations.location_id";
					break;
			}
		}
		
		if(!empty($data)) {
			$count = count($data);
			$sql .= " WHERE";
			foreach($data as $key => $value) {
				$sql .= " $key = '" . $this->db->escape($value) . "'";
				
				$count--;
				if($count > 0) $sql .= " AND";
			}
		}
		
		if(!empty($sort)) {
			$count = count($sort);
			$sql .= " ORDER BY";
			foreach($sort as $key => $value) {
				$sql .= " $key " . $value;
				
				$count--;
				if($count > 0) $sql .= ",";
			}
		}
		
		if(!empty($options)) {
			if ($options['start'] < 0) {
				$options['start'] = 0;
			}
			if ($options['limit'] < 1) {
				$options['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$options['start'] . "," . (int)$options['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getServerById($serverid, $joins = array()) {
		$sql = "SELECT * FROM `servers`";
		foreach($joins as $join) {
			$sql .= " LEFT JOIN $join";
			switch($join) {
				case "users":
					$sql .= " ON servers.user_id=users.user_id";
					break;
				case "games":
					$sql .= " ON servers.game_id=games.game_id";
					break;
				case "locations":
					$sql .= " ON servers.location_id=locations.location_id";
					break;
			}
		}
		$sql .=  " WHERE `server_id` = '" . (int)$serverid . "' LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getTotalServerOwners($data = array()) {
		$sql = "SELECT COUNT(*) AS count FROM `servers_owners`";
		if(!empty($data)) {
			$count = count($data);
			$sql .= " WHERE";
			foreach($data as $key => $value) {
				$sql .= " $key = '" . $this->db->escape($value) . "'";
				
				$count--;
				if($count > 0) $sql .= " AND";
			}
		}
		$query = $this->db->query($sql);
		
		return $query->row['count'];
	}
	
	public function getTotalServers($data = array()) {
		$sql = "SELECT COUNT(*) AS count FROM `servers`";
		if(!empty($data)) {
			$count = count($data);
			$sql .= " WHERE";
			foreach($data as $key => $value) {
				$sql .= " $key = '" . $this->db->escape($value) . "'";
				
				$count--;
				if($count > 0) $sql .= " AND";
			}
		}
		$query = $this->db->query($sql);
		return $query->row['count'];
	}

	public function getServerNewPort($locationid, $min, $max) {
		for($i = $min; $i < $max; $i += 2) {
			$sql = "SELECT COUNT(*) AS total FROM `servers` WHERE location_id = '" . (int)$locationid . "' AND server_port = '" . (int)$i . "' LIMIT 1";
			$query = $this->db->query($sql);
			if($query->row['total'] == 0) {
				return $i;
			}
		}
		return null;
	}
	
	public function getGameServerPortList($locationid, $min, $max) {		
		for($i = $min; $i < $max; $i += 2) {
			$query = $this->db->query("SELECT COUNT(*) AS count FROM `servers` WHERE location_id = '" . (int)$locationid . "' AND server_port = '" . (int)$i . "'");
			if(!$query->row['count']) {
				$ports[] = $i;
			}
		}
		
		return $ports;
	}

	public function getServerSystemLoad($serverid) {
		$this->load->library('ssh2');
		
		$ssh2Lib = new ssh2Library();
			
		$server = $this->getServerById($serverid, array('users', 'locations', 'games'));
			
		$link = $ssh2Lib->connect($server["location_ip"], $server["location_user"], $server["location_password"]);
			
		$stats = @explode(' ', explode(PHP_EOL, $ssh2Lib->execute($link, 'docker stats --all --no-stream gs' . $server['server_id'] . ' | awk \'{print $3" "$7}\''))[1]);
		$cpu = @$stats[0];
		$ram = @$stats[1];

		$output['ssd'] = 0;
		$ssd = $ssh2Lib->execute($link, 'du -scm /home/gs' . $server['server_id'] . ' | tail -1 | sed \'s/[^0-9]//g\'');
	
		if($ssd) {
			$output['ssd'] = $ssd;
		}
			
		$output['ram'] = $ram;
		$output['cpu'] = $cpu;
		$output['ssd'] = $output['ssd'];
		$ssh2Lib->disconnect($link);
		return $output;
	}
	
	public function extendServer($serverid, $month, $fromCurrent) {
		$sql = "UPDATE `servers` SET server_date_end = ";
		if($fromCurrent)
			$sql .= "NOW()";
		else
			$sql .= "server_date_end";
		$sql .= "+INTERVAL " . (int)$month . " MONTH WHERE server_id = '" . (int)$serverid . "'";
		
		$this->db->query($sql);
	}
	
	public function slotsServer($serverid, $slots) {
		$sql = "UPDATE `servers` SET server_slots = '" . (int)$slots . "' WHERE server_id = '" . (int)$serverid . "'";		
		$this->db->query($sql);
	}

	public function getHDD($serverid) {
		$this->load->library('ssh2');
		$ssh2Lib = new ssh2Library();
		
		$server = $this->getServerById($serverid, array('users', 'locations', 'games'));
		$link = $ssh2Lib->connect($server['location_ip'], $server['location_user'], $server['location_password']);
		$output = (int)$ssh2Lib->execute($link, "du -scm /home/gs".$server['server_id']." | tail -1 | sed 's/[^0-9]//g'");
		
		$ssh2Lib->disconnect($link);
		return $output;
	}	

	public function gameConfigs($serverid) {
		$server = $this->getServerById($serverid, array('users', 'locations', 'games'));
		
		$this->load->library('ssh2');	
		$ssh2Lib = new ssh2Library();
		$link = $ssh2Lib->connect($server['location_ip'], $server['location_user'], $server['location_password']);
		
		$configs = array();
		/* Настройка конфигураций серверов перед их запуском. */
		/* Для San Andreas: Multiplayer 0.3.7, Criminal Russia: Multiplayer 0.3e, Criminal Russia: Multiplayer 0.3.7 и United Multiplayer */
		if($server['game_query'] = "samp") {
			$configs = array(
				array(
					'File' => '/server.cfg',
					'ExecPattern' => false,
					'Required' => 1,
					'Values' => array(
						array(
							'Pattern' => 'maxplayers <value>',
							'Value' => $server['server_slots'],
							'Required' => 1
						),
						array(
							'Pattern' => 'bind <value>',
							'Value' => $server['location_ip'],
							'Required' => 1
						),
						array(
							'Pattern' => 'port <value>',
							'Value' => $server['server_port'],
							'Required' => 1
						)
					)
				)
			);
		}
		/* Для Counter Strike 1.6 */
		if($server['game_code'] == "cs") {
			$configs = array(
				array(
					'File' => '/cstrike/server.cfg',
					'ExecPattern' => 'exec <value>',
					'Required' => 1,
					'Values' => array(
						array(
							'Pattern' => 'maxplayers <value>',
							'Value' => $server['server_slots'],
							'Required' => 1
						),
						array(
							'Pattern' => 'ip <value>',
							'Value' => $server['location_ip'],
							'Required' => 1
						),
						array(
							'Pattern' => 'port <value>',
							'Value' => $server['server_port'],
							'Required' => 1
						),
						array(
							'Pattern' => 'sys_ticrate <value>',
							'Value' => '500',
							'Required' => 1
						),
						array(
							'Pattern' => 'pingboost <value>',
							'Value' => '0',
							'Required' => 0
						)
					)
				)
			);
		}
		/* Для Counter Strike Source */
		if($server['game_code'] == "css") {
			$configs = array(
				array(
					'File' => '/cstrike/cfg/server.cfg',
					'ExecPattern' => 'exec <value>',
					'Required' => 1,
					'Values' => array(
						array(
							'Pattern' => 'maxplayers <value>',
							'Value' => $server['server_slots'],
							'Required' => 1
						)
					)
				)
			);
		}
		/* Для MineCraft: PE */
		if($server['game_code'] == "mcpe") {
			$configs = array(
				array(
					'File' => '/server.properties',
					'ExecPattern' => false,
					'Required' => 2,
					'Values' => array(
						array(
							'Pattern' => 'server-ip=<value>',
							'Value' => $server['location_ip'],
							'Required' => 1
						),
						array(
							'Pattern' => 'max-players=<value>',
							'Value' => $server['server_slots'],
							'Required' => 1
						),
						array(
							'Pattern' => 'server-port=<value>',
							'Value' => $server['server_port'],
							'Required' => 1
						),
						array(
							'Pattern' => 'memory-limit=<value>',
							'Value' => ''.$server['game_ram'].'M',
							'Required' => 1
						)
					)
				)
			);
		}
		/* Для MineCraft */
		if($server['game_code'] == "mine72") {
			$configs = array(
				array(
					'File' => '/server.properties',
					'ExecPattern' => false,
					'Required' => 2,
					'Values' => array(
						array(
							'Pattern' => 'max-players=<value>',
							'Value' => $server['server_slots'],
							'Required' => 1
						),
						array(
							'Pattern' => 'server-ip=<value>',
							'Value' => $server['location_ip'],
							'Required' => 1
						),
						array(
							'Pattern' => 'server-port=<value>',
							'Value' => $server['server_port'],
							'Required' => 1
						),
						array(
							'Pattern' => 'query.port=<value>',
							'Value' => $server['server_port'],
							'Required' => 1
						),
						array(
							'Pattern' => 'enable-query=<value>',
							'Value' => 'true',
							'Required' => 1
						)
					)
				)
			);
		}
		/* Для Multi Theft Auto: Multiplayer */
		if($server['game_code'] == "mta") {
			$http_port = $server['server_port'] + 1;
			$configs = array(
				array(
					'File' => '/mods/deathmatch/mtaserver.conf',
					'ExecPattern' => false,
					'Required' => 1,
					'Values' => array(
						array(
							'Pattern' => '<serverip><value>',
							'Value' => ''.$server['location_ip'].'</serverip>',
							'Required' => 1
						),
						array(
							'Pattern' => '<serverport><value>',
							'Value' => ''.$server['server_port'].'</serverport>',
							'Required' => 1
						),
						array(
							'Pattern' => '<maxplayers><value>',
							'Value' => ''.$server['server_slots'].'</maxplayers>',
							'Required' => 1
						),
						array(
							'Pattern' => '<httpport><value>',
							'Value' => ''.$http_port.'</httpport>',
							'Required' => 1
						),
						array(
							'Pattern' => '<bandwidth_reduction><value>',
							'Value' => 'medium</bandwidth_reduction>',
							'Required' => 0
						),
						array(
							'Pattern' => '<fpslimit><value>',
							'Value' => '36</fpslimit>',
							'Required' => 0
						)
					)
				)
			);
		}
		/* --- */
		$this->load->library('sftp');		
		$sftpLib = new sftpLibrary();		
		$sftpLink = $sftpLib->connect($server['location_ip'], $server['location_user'], $server['location_password']);

		foreach($configs as $cfg) {
			$file = $sftpLib->open($sftpLink, '/home/gs' . $serverid . '/' . $cfg['File']);
			if(empty($file) && $cfg['Required'] == 1) {
				$ssh2Lib->execute($link, 'cp -Rp /home/cp/gameservers/files/' . $server['game_code'] . '/' . $cfg['File'] . ' /home/gs' . $serverid . '/');
				$ssh2Lib->execute($link, 'chown gs' . $serverid . ':gameservers -Rf /home/gs' . $serverid . '/' . $cfg['File']);
			}
		}
					
		foreach($configs as $cfg) {
			$file = $sftpLib->open($sftpLink, '/home/gs' . $serverid . '/' . $cfg['File']);

			if(empty($file) && $cfg['Required'] == 1) {
				return array('status' => 'error', 'description' => 'Ошибка конфигурации!');
			}
						
			foreach($cfg['Values'] as $value) {
				$pattern = str_replace('<value>', '(.*)', $value['Pattern']);
				$replace = str_replace('<value>', $value['Value'], $value['Pattern']);

				if($value['Required'] == 1 && !preg_match('/' . $pattern . '/', $file)) {
					$file .= "\r\n" . $pattern;
				} else if($value['Required'] == -1 && preg_match('/' . $pattern . '/', $file)) {
					return False;
				}
				$file = preg_replace('/' . $pattern . '/', $replace, $file);
			}
			if($file != null) $sftpLib->write($sftpLink, '/home/gs' . $serverid . '/' . $cfg['File'], $file);
		}
		$ssh2Lib->disconnect($link);
	}
	
	public function action($serverid, $action = "", $data = array()) {
		$this->load->library('ssh2');	
		$ssh2Lib = new ssh2Library();
		$server = $this->getServerById($serverid, array('users', 'locations', 'games'));
		$link = $ssh2Lib->connect($server['location_ip'], $server['location_user'], $server['location_password']);
		$action = mb_strtolower($action);
			
		switch($action) {	
			case "start": {
				$cores_loc = $ssh2Lib->execute($link, 'lscpu | grep -E \'^CPU\(\' | awk \'{print $2}\'');
				if(round($cores_loc, 2) < $server['game_cores']) {
					return array('status' => 'error', 'description' => 'Для сервера указан слишком большой процент CPU, превышающий рамки игровой локации. Обратитесь к администрации хостинга для устранения проблемы.');
				}
				$this->gameConfigs($serverid);
				$user = explode(':', $ssh2Lib->execute($link, 'cat /etc/passwd | grep gs' . $serverid . ':'));

				$execCmd = $server['start_cmd'];
				$execCmd = str_replace('@ip@', $server['location_ip'], $execCmd);
				$execCmd = str_replace('@port@', $server['server_port'], $execCmd);
				$execCmd = str_replace('@port2@', $server['server_port'] + 1, $execCmd);
				$execCmd = str_replace('@port3@', $server['server_port'] + 1000, $execCmd);
				$execCmd = str_replace('@slots@', $server['server_slots'], $execCmd);

				$ssh2Lib->execute($link, 'docker create --tty --rm --name=gs' . $serverid . ' --network=host --cpus="' . $server['game_cores'] . '" --memory=' . $server['game_ram'] . 'M --memory-swap=-1 --volume="/home/gs' . $serverid . '/:/home/gs' . $serverid . '/" --workdir=/home/gs' . $serverid . ' debian:stretch bash > /dev/null 2>&1');
				$ssh2Lib->execute($link, 'docker start gs' . $serverid . ' > /dev/null 2>&1');
				$ssh2Lib->execute($link, 'docker exec gs' . $serverid . ' groupadd -g ' . $user[3] . ' gameservers > /dev/null 2>&1');
				$ssh2Lib->execute($link, 'docker exec gs' . $serverid . ' useradd -u ' . $user[2] . ' gs' . $serverid . ' -g gameservers -p ' . crypt(mt_rand(111111111, 999999999), 'tlas') . ' > /dev/null 2>&1');
				$ssh2Lib->execute($link, 'docker exec gs' . $serverid . ' chown -Rf gs' . $serverid . ':gameservers /home/gs' . $serverid . ' > /dev/null 2>&1');
				sleep(1);
				$ssh2Lib->execute($link, 'docker exec -i -u gs' . $serverid . ' gs' . $serverid . ' screen -L -dmS gs' . $serverid . ' ' . $execCmd . ' > /dev/null 2>&1');
				sleep(1);
				$result = array('status' => 'success');
				break;
			}
			
			case "stop": {
				$ssh2Lib->execute($link, 'docker stop gs'.$server['server_id'].'');
				$result = array('status' => 'success');
				break;
			}
			
			case "block": {
				$random = mt_rand(11111111, 99999999);
				$random2 = mt_rand(11111111, 99999999);
				$ssh2Lib->execute($link, 'usermod -p ' . crypt($random, 'tlas') . ' gs' . $serverid);
				$ssh2Lib->execute($link, 'mysql -e "grant usage on *.* to gs' . $serverid . '@\'%\' identified by \'' . $random2 . '\'"');
				$ssh2Lib->execute($link, 'mysql -e "grant all privileges on gs' . $serverid . '.* to \'gs' . $serverid . '\'@\'%\' identified by \'' . $random2 . '\'"');
				$result = array('status' => 'success');
				break;
			}
				
			case "install": {
				$ssh2Lib->execute($link, 'useradd -m -g gameservers -p ' . crypt($server['server_password'], 'tlas') . ' gs' . $serverid);
				$ssh2Lib->execute($link, 'cp -Rp /home/cp/gameservers/files/' . $server['game_code'] . '/* /home/gs' . $serverid . '/;');
				$ssh2Lib->execute($link, 'chown gs' . $serverid . ' -Rf /home/gs' . $serverid);
				$files = explode(' ', $server['check_files']);
				foreach($files as $item){
					$ssh2Lib->execute($link, 'chmod 700 /home/gs' . $serverid . '/' . $item);
					$ssh2Lib->execute($link, 'chown gs' . $serverid . ':gameservers /home/gs' . $serverid . '/' . $item);
				}
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr +i ' . $server['check_files']);
				$this->updateServer($serverid, array('server_status' => 1));
				$this->gameConfigs($serverid);
				$result = array('status' => 'success');
				break;
			}
			
			case "reinstall": {
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr -i ' . $server['check_files']);
				$ssh2Lib->execute($link, 'rm -Rf /home/gs' . $serverid . '/*');
				$ssh2Lib->execute($link, 'cp -Rp /home/cp/gameservers/files/' . $server['game_code'] . '/* /home/gs' . $serverid . '/;');
				$ssh2Lib->execute($link, 'chown gs' . $serverid . ' -Rf /home/gs' . $serverid);
				$files = explode(' ', $server['check_files']);
				foreach($files as $item){
					$ssh2Lib->execute($link, 'chmod 700 /home/gs' . $serverid . '/' . $item);
					$ssh2Lib->execute($link, 'chown gs' . $serverid . ':gameservers /home/gs' . $serverid . '/' . $item);
				}
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr +i ' . $server['check_files']);
				$this->gameConfigs($serverid);
				$this->updateServer($serverid, array('server_status' => 1));
				$result = array('status' => 'success');
				break;
			}
			
			
			case "updatepass": {
				$ssh2Lib->execute($link, 'usermod -p ' . crypt($server['server_password'], 'tlas') . ' gs' . $serverid);
				$result = array('status' => 'success');
				break;
			}
			
			case "updatepassm": {
				$ssh2Lib->execute($link, 'mysql -e "grant usage on *.* to gs' . $serverid . '@\'%\' identified by \'' . $server['db_pass'] . '\'"');
				$ssh2Lib->execute($link, 'mysql -e "grant all privileges on gs' . $serverid . '.* to \'gs' . $serverid . '\'@\'%\' identified by \'' . $server['db_pass'] . '\'"');
				$result = array('status' => 'success');
				break;
			}
			
			case "create_mysql": {
				$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP"; 
				$max=10; 
				$size=StrLen($chars)-1; 
				$dbpass=null; 
				while($max--) 
				$dbpass.=$chars[rand(0,$size)]; 
				$ssh2Lib->execute($link, 'mysql -e "create database gs' . $serverid . '"');
				$ssh2Lib->execute($link, 'mysql -e "grant usage on *.* to gs' . $serverid . '@\'%\' identified by \'' . $dbpass . '\'"');
				$ssh2Lib->execute($link, 'mysql -e "grant all privileges on gs' . $serverid . '.* to \'gs' . $serverid . '\'@\'%\' identified by \'' . $dbpass . '\'"');
				$this->updateServer($serverid, array('db_pass' => $dbpass));
				$result = array('status' => 'success');
				break;
			}
			
			case "delete_mysql": {
				$ssh2Lib->execute($link, 'mysql -e "DROP DATABASE gs' . $serverid . ';"');
				$ssh2Lib->execute($link, 'mysql -e "DROP USER \'gs' . $serverid . '\'@\'%\';"');
				$result = array('status' => 'success');
				break;
			}
		
			
			case "sendcommand": {
				$ssh2Lib->execute($link, 'docker exec -d -u gs' . $serverid . ' gs' . $serverid . ' screen -S gs' . $serverid . ' -X eval \'stuff "' . $data['command'] . '\015"\' > /dev/null 2>&1');
				$result = array('status' => 'success');
				break;
			}
			
			case "createbackup": {
				$ssh2Lib->execute($link, "tar --totals -cvf /home/cp/backups/gs" . $serverid . ".tar /home/gs" . $serverid);
				$this->updateServer($serverid, array('server_status' => 1));
				$result = array('status' => 'success');
				break;
			}
			
			case "installbackup": {
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr -i ' . $server['check_files']);
				$ssh2Lib->execute($link, "rm -Rf /home/gs" . $serverid . "/*");
				$ssh2Lib->execute($link, "tar -C \"/home/gs" . $serverid . "\" -xvf /home/cp/backups/gs" . $serverid . ".tar");
				$ssh2Lib->execute($link, "cp -rp /home/gs" . $serverid . "/home/gs" . $serverid . "/* /home/gs" . $serverid . "/");
				$ssh2Lib->execute($link, "rm -Rf /home/gs" . $serverid . "/home");
				$ssh2Lib->execute($link, 'chown gs' . $serverid . ' -Rf /home/gs' . $serverid);
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr +i ' . $server['check_files']);
				$this->updateServer($serverid, array('server_status' => 1));
				$result = array('status' => 'success');
				break;
			}
			
			case "delete": {
				$ssh2Lib->execute($link, "userdel -rf gs" . $serverid);
				$ssh2Lib->execute($link, 'cd /home/gs' . $serverid . '/ && chattr -i ' . $server['check_files']);
				$ssh2Lib->execute($link, "rm -Rf /home/gs" . $serverid);
				$ssh2Lib->execute($link, "rm -Rf /home/cp/backups/gs" . $serverid . ".tar");
				$result = array('status' => 'success');
				break;
			}

			case "server_status": {
				$status = $ssh2Lib->execute($link, 'docker ps --all | grep gs' . $server['server_id'] . ' | awk \'{print $1}\'');
					
				if($status) {
					$status = 2;// Контейнер запущен
				} else {
					$status = 1;// Контейнер не запущен
				}
					
				$result = $status;
				break;
			}
		}
		$ssh2Lib->disconnect($link);
		return $result;
	}	
}
?>
