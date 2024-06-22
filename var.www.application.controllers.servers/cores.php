<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko (https://vk.com/id00v)
*/
class coresController extends Controller {
	public function index($serverid = null) {
		$this->document->setActiveSection('servers');
		$this->document->setActiveItem('cores');
		
		if(!$this->user->isLogged()) {
			$this->session->data['error'] = "Вы не авторизированы!";
			$this->response->redirect($this->config->url . 'account/login');
		}
		
		if($this->user->getAccessLevel() < 0) {
			$this->session->data['error'] = "У вас нет доступа к данному разделу!";
			$this->response->redirect($this->config->url);
		}
		
		$this->load->model('servers');
		$this->load->model('minecores');
		
		$error = $this->validate($serverid);
		if($error) {
			$this->session->data['error'] = $error;
			$this->response->redirect($this->config->url . 'servers/index');
		}
		
		$server = $this->serversModel->getServerById($serverid, array('games', 'locations'));
		$cores = $this->minecoresModel->getCores(array('c_status' => 1), array('games'), array(), array());
		$craftbukkit = $this->minecoresModel->getCores(array('c_status' => 1, 'c_act' => 1), array('games'), array(), array());
		$spigot = $this->minecoresModel->getCores(array('c_status' => 1, 'c_act' => 2), array('games'), array(), array());
		$vanilla = $this->minecoresModel->getCores(array('c_status' => 1, 'c_act' => 3), array('games'), array(), array());
		
		$this->data['server'] = $server;
		$this->data['cores'] = $cores;
        $this->data['craftbukkit'] = $craftbukkit;
		$this->data['spigot'] = $spigot;
	    $this->data['vanilla'] = $vanilla;
		
		include_once 'application/controllers/common/main.php';
		
		$this->getChild(array('common/header', 'common/footer'));
		return $this->load->view('servers/cores', $this->data);
	}
	
	public function action($serverid = null, $action = null) {
		if(!$this->user->isLogged()) {
			$this->data['status'] = "error";
			$this->data['error'] = "Вы не авторизированы!";
			return json_encode($this->data);
		}
		
		if($this->user->getAccessLevel() < 0) {
	  		$this->data['status'] = "error";
			$this->data['error'] = "У вас нет доступа к данному разделу!";
			return json_encode($this->data);
		}
		
		$userid = $this->user->getId();
		$this->load->model('servers');
		$this->load->model('minecores');
		$this->load->model('serverLog');
		
		$this->load->library('ssh2');
		$ssh2Lib = new ssh2Library();

		$server = $this->serversModel->getServerById($serverid, array('users', 'locations', 'games'));
		
		$error = $this->validate($serverid);
		if($error) {
			$this->data['status'] = "error";
			$this->data['error'] = $error;
			return json_encode($this->data);
		}	
		
		if($server["server_status"] == 0) {
			$this->data["status"] = "error";
			$this->data["error"] = "Сервер заблокирован!";
			return json_encode($this->data);
		}
		
		$link = $ssh2Lib->connect($server['location_ip'], $server['location_user'], $server['location_password']);
		$minecores = $this->minecoresModel->getCores(array(), array('games'), array(), array());
		foreach($minecores as $item){
			if($action == $item['c_id']) {
				if($server['server_status'] == 1) {
					$stats = $this->serversModel->getHDD($server['server_id']);
					if((int)$stats < $server['game_ssd']) {
						if($item['c_act'] == 1) {
							$text = "CraftBukkit";
							$type = "craftbukkit";
						}
						if($item['c_act'] == 2) {
							$text = "Spigot";
							$type = "spigot";
						}
						if($item['c_act'] == 3) {
							$text = "Vanilla";
							$type = "vanilla";
						}
						$ver = $item['c_version'];
						$url = "http://games.hostinpl.ru/5.5_pro/files/mine";
						$output = $ssh2Lib->execute($link, 
						"
							cd /home/gs$serverid;
							chattr -i minecraft.jar;						
							rm minecraft.jar;
							wget ".$url."/".$type."/".$ver."/minecraft.jar;
							sudo chown -R gs". $server['server_id'] .":gameservers /home/gs". $server['server_id'] .";
							chattr +i minecraft.jar;	
						");
						$ssh2Lib->disconnect($link);
						$logData = array(
							'server_id'			=> $serverid,
							'reason'            => 'Успешная смена ядра.',
							'status'            => 2
						);
						$this->serverLogModel->createLog($logData);
						$this->data['status'] = "success";
						$this->data['success'] = "Вы успешно изменили версию ядра сервера на ".$text." ".$ver."!";
					} else {
						$this->data["status"] = "error";
						$this->data["error"] = "Вы привысили ограничение размера директории на ". round($stats - $server['game_ssd'], 2) ."МБ, смена ядра невозможена!";
					}
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = "Сервер должен быть выключен!";
			    }
				break;
            }
		}
		return json_encode($this->data);
	}

	private function validate($serverid) {
		$result = null;
		
		$userid = $this->user->getId();
		
		if(!$this->serversModel->getTotalServerOwners(array('server_id' => (int)$serverid, 'user_id' => (int)$userid, 'owner_status' => 1))) {
			if(!$this->serversModel->getTotalServers(array('server_id' => (int)$serverid, 'user_id' => (int)$userid))) {
				$result = "Запрашиваемый сервер не существует!";
			}
		}
		return $result;
	}
}
?>