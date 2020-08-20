<?php

namespace Action\Admin\Settings\User;

class History extends \PA\Action\Authenticated {

	public function get($uid) {

		$user			 = \PA\User::findOne($uid);
		$auth_history	 = \PA\UserAuthenticateHistory::find(['where' => ['username' => $user->username], 'limit' => 50, 'order' => 'updated_time DESC']);
		$this->view->assign('history', $auth_history);
		$this->view->assign('user', $user);
	}

}
