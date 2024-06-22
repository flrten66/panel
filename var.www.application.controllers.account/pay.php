<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko (https://vk.com/id00v)
*/
class payController extends Controller {
	public function index() {
		$this->document->setActiveSection('account');
		$this->document->setActiveItem('pay');
		
		if(!$this->user->isLogged()) {
			$this->session->data['error'] = "Вы не авторизированы!";
			$this->response->redirect($this->config->url . 'account/login');
		}
		if($this->user->getAccessLevel() < 0) {
			$this->session->data['error'] = "У вас нет доступа к данному разделу!";
			$this->response->redirect($this->config->url);
		}
		$this->load->model('users');
		$unitpay = $this->config->unitpay;
		$enotpay = $this->config->enotpay;
		$anypay = $this->config->anypay;
		$litekassa = $this->config->litekassa;
		$robokassa = $this->config->robokassa;
		$freekassa = $this->config->freekassa;
		$interkassa = $this->config->interkassa;
        $yandexkassa = $this->config->yandexkassa;
		$qiwi = $this->config->qiwi;
		$this->data['unitpay'] = $unitpay;
		$this->data['enotpay'] = $enotpay;
		$this->data['anypay'] = $anypay;
		$this->data['litekassa'] = $litekassa;
		$this->data['robokassa'] = $robokassa;
		$this->data['freekassa'] = $freekassa;
		$this->data['interkassa'] = $interkassa;
        $this->data['yandexkassa'] = $yandexkassa;
		$this->data['qiwi'] = $qiwi;
		$this->getChild(array('common/header', 'common/footer'));
		return $this->load->view('account/pay', $this->data);
	}
	
	public function anypay() {
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
		
		$this->load->model('invoices');

		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->anypay == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$server = $this->config->anypay_server;
					$login = $this->config->anypay_login;
					$password = $this->config->anypay_password;
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Anypay"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					$currency = 'RUB'; // валюта Вашего магазина
					$signature = md5("$currency:$ammount:$password:$login:$invid");

					$url = "$server";
					/* Параметры: */
					$url .= "?merchant_id=$login";
					$url .= "&amount=$ammount";
					$url .= "&sign=$signature";
					$url .= "&desc=Оплата счета ".$invid;
					$url .= "&pay_id=$invid";
					
					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function enotpay() {
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
		
		$this->load->model('invoices');

		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->enotpay == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$server = $this->config->enot_server;
					$login = $this->config->enot_login;
					$password1 = $this->config->enot_password1;
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Enot"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$signature = md5("$login:$ammount:$password1:$invid");
					
					$url = "$server";
					/* Параметры: */
					$url .= "?m=$login";
					$url .= "&oa=$ammount";
					$url .= "&s=$signature";
					$url .= "&o=$invid";
					$url .= "&c=Оплата счета ".$invid;
					
					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function interpay() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->interkassa == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'			=> "Interkassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					$url = "https://sci.interkassa.com/";
					/* Параметры: */
					$url .= "?ik_co_id=57e931c53d1eafce4e8b456d";
					$url .= "&ik_pm_no=$invid";
					$url .= "&ik_am=$ammount";
					$url .= "&ik_cur=RUB";				
					$url .= "&ik_desc=Оплата счета ".$invid;
					
					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function robopay() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->robokassa == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$server = $this->config->rk_server;
					$login = $this->config->rk_login;
					$password1 = $this->config->rk_password1;
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Robokassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$signature = md5("$login:$ammount:$invid:$password1");
					
					$url = "$server/Index.aspx";
					/* Параметры: */
					$url .= "?MrchLogin=$login";
					$url .= "&OutSum=$ammount";
					$url .= "&InvId=$invid";
					$url .= "&SignatureValue=$signature";
					$url .= "&Desc=Оплата счета ".$invid;

					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}

		return json_encode($this->data);
	}
	
	public function freepay() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->freekassa == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$server = $this->config->fk_server;
					$login = $this->config->fk_login;
					$password1 = $this->config->fk_password1;
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Free-Kassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$signature = md5("$login:$ammount:$password1:$invid");
					
					$url = "$server";
					/* Параметры: */
					$url .= "?m=$login";
					$url .= "&oa=$ammount";
					$url .= "&o=$invid";
					$url .= "&s=$signature";
					$url .= "&us_desc=Оплата счета ".$invid;

					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function unitpay() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->unitpay == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'			=> "Unitpay"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$server = $this->config->unitpay_url;
					$url = "$server";
					/* Параметры: */
					$url .= "?sum=$ammount";
					$url .= "&account=$invid";
					$url .= "&desc=Оплата счета ".$invid;
					
					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function yandexkassa() {
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
		
		$this->load->model('invoices');
		$userid = $this->user->getId();
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->yandexkassa == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Yandex Kassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$url = "https://money.yandex.ru/quickpay/confirm.xml";
					$url .= "?receiver=".$this->config->yk_login."";
					$url .= "&quickpay-form=shop";
					$url .= "&paymentType=PC";
					$url .= "&paymentType=AC";
					$url .= "&paymentType=MC";
					$url .= "&label=$invid";
					$url .= "&successURL=".$this->config->url."account/success";
					$url .= "&targets=Оплата счета ".$invid;
					$url .= "&sum=$ammount";
					
					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}

		return json_encode($this->data);
	}
	
	public function litepay() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->litekassa == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$server = $this->config->lk_server;
					$login = $this->config->lk_login;
					$password = $this->config->lk_password;
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Lite-Kassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$signature = md5("$login:$ammount:$password:$invid");
					
					$url = "$server";
					/* Параметры: */
					$url .= "?shop=$login";
					$url .= "&amount=$ammount";
					$url .= "&order=$invid";
					$url .= "&sign=$signature";
					$url .= "&desc=Оплата счета ".$invid;

					$this->data['status'] = "success";
					$this->data['url'] = $url;
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}
		return json_encode($this->data);
	}
	
	public function qiwi() {
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
		
		$this->load->model('invoices');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->config->qiwi == 1) {
				$errorPOST = $this->validatePOST();
				if(!$errorPOST) {
					$ammount = @$this->request->post['ammount'];
					
					$userid = $this->user->getId();
					
					$invoiceData = array(
						'user_id'			=> $userid,
						'invoice_ammount'	=> $ammount,
						'invoice_status'	=> 0,
						'system'	        => "Qiwi Kassa"
					);
					$invid = $this->invoicesModel->createInvoice($invoiceData);
					
					$params['publicKey'] = $this->config->qiwipublickey;
					$params['amount'] = number_format(round(floatval($ammount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
					$params['billId'] = $invid;
					$params['account'] = $userid;
					$params['comment'] = "Оплата счета ".$invid;
					$params['successUrl'] = $this->config->url."account/success";
					if($this->config->qiwi_theme == 1) {
						$customFields = ['themeCode' => $this->config->qiwi_themecode];
						$params['customFields'] = $customFields;
					}

					$this->data['status'] = "success";
					$this->data['url'] = "https://oplata.qiwi.com/create?".http_build_query($params, '', '&', PHP_QUERY_RFC3986);
				} else {
					$this->data['status'] = "error";
					$this->data['error'] = $errorPOST;
				}
			} else {
				$this->data['status'] = "error";
				$this->data['error'] = "Данная платежная система отключена!";
			}
		}

		return json_encode($this->data);
	}
	
	private function validatePOST() {
	
		$this->load->library('validate');
		
		$validateLib = new validateLibrary();
		
		$result = null;
		
		$ammount = @$this->request->post['ammount'];
		if(!$validateLib->money($ammount)) {
			$result = "Укажите сумму пополнения в допустимом формате!";
		}
		elseif(1 > $ammount || $ammount > 5000) {
			$result = "Укажите сумму от 10 до 5000 рублей!";
		}
		return $result;
	}
}
?>
