<?php

namespace Action\Admin\Settings\User;

class Register extends \PA\Action\Authenticated {

	public function post() {
		$this->view->noOutput();

		$username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL));
		$merchant_id = filter_input(INPUT_POST, 'merchant');
		$flag = null;

		if (filter_var($username, FILTER_VALIDATE_EMAIL) === FALSE) {
			$this->fail([sprintf('Invalid Email Address (%s)', $username)]);
		}
		//Adam 14-02 Start
		if ($merchant_id === "Admin") {
			$flag = "admin";
			$user = \PA\User::findOne([
						'merchant_id' => null,
						'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL)
			]);
			$merchant_id = null;
		} else {
			$flag = "merchant";
			$user = \PA\User::findOne([
						'merchant_id' => $merchant_id,
						'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL)
			]);
		}
//Adam 14-02 End
		if ($user) {
			$this->fail([
				'Email address already associated with your merchant account.'
			]);
		} else {
			$user = new \PA\User();
			$user->merchant_id = $merchant_id;
			$user->username = trim($username);
			$user->save();
		}

		$merchant = \PA\Merchant::findOne($merchant_id);
		if ($flag == "admin") {
			$url = sprintf("%s%s/verify", \LT\Config::value('pa.admin_panel_url'), $user->token);
		} else {
			$url = sprintf("%s%s/%s/verify", \LT\Config::value('pa.merchant_panel_url'), $merchant->token, $user->token);
		}

		$email = new \PA\Notification\Email();
		$email->to = $user->username;
		$email->send('User Account Verification', <<<EOL
Dear {$user->username},<br/><br/>
	
  User Account have been created, please click the URL below to verify your account, and setup your password.<br/><br/>
				
  <a clicktracking=off href="{$url}">{$url}</a><br/><br/>
				
  Thank you.
EOL
		);

		$this->ok([
			'Verification email have been sent. Please check your Inbox.'
		]);
	}

}
