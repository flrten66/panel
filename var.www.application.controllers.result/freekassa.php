<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko and Alexander Zemlyanoy  (https://vk.com/id00v / https://vk.com/mrsasha082)
*/
class freekassaController extends Controller {
	public function index() {
		$this->load->model('users');
		$this->load->model('invoices');
		$this->load->model('waste');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			$errorPOST = $this->validatePOST();
			if(!$errorPOST) {
				$ammount = $this->request->post['AMOUNT'];
				$invid = $this->request->post['MERCHANT_ORDER_ID'];
				$signature = $this->request->post['SIGN'];
				
				$invoice = $this->invoicesModel->getInvoiceById($invid);
				$userid = $invoice['user_id'];
				
				if($ammount > 50){
					$this->usersModel->updateUser($userid, $userData = array('user_promised_pay' => 0));
				}

				$wasteData = array(
					'user_id'		=> $userid,
					'waste_ammount'	=> $ammount,
					'waste_status'	=> 0,
					'waste_usluga'	=> "Пополнение баланса пользователя",
				); 						
				$this->wasteModel->createWaste($wasteData);
				
				$this->usersModel->upUserBalance($userid, $ammount);
				
				$bonus_percent = $this->config->bonus_percent;
				$getbonus = ($ammount * (1 + $bonus_percent / 100)) - $ammount;
				$this->usersModel->upUserBonuses($userid, $getbonus);
				
				$this->invoicesModel->updateInvoice($invid, array('invoice_status' => 1));
				return "OK$invid\n";
			} else {
				return "Error: $errorPOST";
			}
		} else {
			return "Error: Invalid request!";
		}
	}
	
	private function validatePOST() {
		$result = null;
		
		$ammount = $this->request->post['AMOUNT'];
		$invid = $this->request->post['MERCHANT_ORDER_ID'];
		$signature = $this->request->post['SIGN'];
		
		$login = $this->config->fk_login;
		$password2 = $this->config->fk_password2;
		
		if(!$this->invoicesModel->getTotalInvoices(array('invoice_id' => (int)$invid))) {
			$result = "Invalid invoice!";
		}
        elseif(strtoupper($signature) != strtoupper(md5("$login:$ammount:$password2:$invid"))) {
			$result = "Invalid signature!";
		}
		return $result;
	}
}
?>
