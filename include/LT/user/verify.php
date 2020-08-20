<?php

namespace Action\Admin\Settings\User;

class Verify extends \PA\Action\Authenticated {

	public function get($token = NULL) {
		if (is_null($token)) {
			$this->view->assign('error', 'Invalid Token');
			$this->view->assign('fatal', TRUE);
			return FALSE;
		}

		$user = \PA\User::findOne([
					'token'			 => $token,
					'verified_time'	 => NULL
		]);
		if (!$user) {
			$this->view->assign('error', 'Invalid Token');
			$this->view->assign('fatal', TRUE);
			return FALSE;
		}

		$merchant = \PA\Merchant::findOne($user->merchant_id);
		$this->view->assign('username', $user->username);
		$this->view->assign('merchant_name', $merchant->name);
	}

	public function post($token = NULL) {
		if (is_null($token)) {
			$this->view->assign('error', 'Invalid Token');
			$this->view->assign('fatal', TRUE);
			return FALSE;
		}

		$user = \PA\User::findOne([
					'token'			 => $token,
					'verified_time'	 => NULL
		]);
		if (!$user) {
			$this->view->assign('error', 'Invalid Token');
			$this->view->assign('fatal', TRUE);
			return FALSE;
		}

		$merchant = \PA\Merchant::findOne($user->merchant_id);
		$this->view->assign('username', $user->username);
		$this->view->assign('merchant_name', $merchant->name);

		$password		 = filter_input(INPUT_POST, 'password');
		$retypePassword	 = filter_input(INPUT_POST, 'retype-password');

		if ($password !== $retypePassword || strlen($password) < 8) {
			$this->view->assign('error', 'Password not match or Password length lesser than 8');
			return FALSE;
		}

		$user->verified_time = time();
		$user->salt			 = \LT\Security\Misc::random(8);
		$user->password		 = hash('sha512', $password . $user->salt);
		$user->save();

		$this->redirect('/user/sign-in');
	}

}
