<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko (https://vk.com/id00v)
*/
class ownerController extends Controller {
	public function index($serverid = null) {
		$this->document->setActiveSection('servers');
		$this->document->setActiveItem('owner');
		
		if(!$this->user->isLogged()) {
			$this->session->data['error'] = "Вы не авторизированы!";
			$this->response->redirect($this->config->url . 'account/login');
		}
		
		if($this->user->getAccessLevel() < 0) {
			$this->session->data['error'] = "У вас нет доступа к данному разделу!";
			$this->response->redirect($this->config->url);
		}
		
		$this->load->model('servers');
		$this->load->model('serversOwners');
		
		$error = $this->validate($serverid);
		if($error) {
			$this->session->data['error'] = $error;
			$this->response->redirect($this->config->url . 'servers/index');
		}

		$this->data['server'] = $server = $this->serversModel->getServerById($serverid, array('games', 'locations'));
		$this->data['serversOwners'] = $serversOwners = $this->serversOwnersModel->getOwners(array('server_id' => $serverid), array('users'));
		
		include_once 'application/controllers/common/main.php';

		$this->getChild(array('common/header', 'common/footer'));
		return $this->load->view('servers/owner', $this->data);
	}
	
	public function ajax($serverid = null) {
		if(!$this->user->isLogged()) {
			$this->data['status'] = "error";
			$this->data['error'] = "Вы не авторизированы!";
			return json_encode($this->data);
		}
		
		if($this->user->getAccessLevel() < 1) {
			$this->data['status'] = "error";
			$this->data['error'] = "У вас нет доступа к данному разделу!";
			return json_encode($this->data);
		}

		$this->load->library('mail');
		$this->load->model('users');
		$this->load->model('servers');
		$this->load->model('serversOwners');

		$server = $this->serversModel->getServerById($serverid);
		
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
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($server['server_status'] == 1){
				if(!$this->validatePOST($serverid)) {
					$ownerid = @$this->request->post['ownerid'];
					
					$ownerData = array(
						'server_id'				=> $serverid,
						'user_id'				=> $ownerid,
						'owner_status'  		=> 1
					);
					$serverOwnerId = $this->serversOwnersModel->createOwner($ownerData);
					
					$this->data['status'] = "success";
					$this->data['success'] = "Вы успешно добавили совладельца!";			
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $this->validatePOST($serverid);
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Сервер должен быть выключен!";
			}
		}

		return json_encode($this->data);
	}
	
	public function action($action = null, $serverid = null, $ownerid = null) {
		if(!$this->user->isLogged()) {
			$this->data['status'] = "error";
			$this->data['error'] = "Вы не авторизированы!";
			return json_encode($this->data);
		}
		
		if($this->user->getAccessLevel() < 1) {
			$this->data['status'] = "error";
			$this->data['error'] = "У вас нет доступа к данному разделу!";
			return json_encode($this->data);
		}
		
		$this->load->model('servers');
		$this->load->model('serversOwners');
		
		$server = $this->serversModel->getServerById($serverid);
		
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
		
		if($server['server_status'] == 1){
			switch($action) {
				case 'delete': {
					if($this->serversModel->getTotalServerOwners(array('server_id' => $serverid, 'owner_id' => $ownerid))) {
						$this->serversOwnersModel->deleteOwner($ownerid);
					
						$this->data['status'] = "success";
						$this->data['success'] = "Вы успешно удалили совладельца!";
					} else {
						$this->data['status'] = "error";
						$this->data['error'] = "Ошибка!";
					}
					break;
				}
				default: {
					$this->data['status'] = "error";
					$this->data['error'] = "Вы выбрали несуществующее действие!";
					break;
				}
			}
		} else {
			$this->data['status'] = "error";
			$this->data['error'] = "Сервер должен быть выключен!";
		}

		return json_encode($this->data);
	}
	
	private function validate($serverid) {
		$result = null;
		$userid = $this->user->getId();

		if (!$this->serversModel->getTotalServers( array( 'server_id' => (int)$serverid, 'user_id' => (int)$userid ) )) {
			$result = 'Вы не являетесь владельцем сервера. У вас нет доступа к разделу!';
		}

		return $result;
	}

	private function validatePOST($serverid) {
		$result = null;
		$this->load->library('validate');
		
		$validateLib = new validateLibrary();
		$ownerid = @$this->request->post['ownerid'];
		$userid = $this->user->getId();
				
		if(!$validateLib->money($ownerid)) {
			$result = "Вы указали недоступный ID пользователя!";
		}elseif(!$this->usersModel->getTotalUsers(array('user_id' => $ownerid))) {
			$result = "Запрашиваемый пользователь не существует!";
		}elseif($this->serversModel->getTotalServerOwners(array('server_id' => $serverid, 'user_id' => $ownerid))) {
			$result = "Запрашиваемый пользователь уже добавлен!";
		}elseif($userid == $ownerid) {
			$result = "Запрашиваемый пользователь является владельцем сервера!";
		}
		return $result;
	}
}