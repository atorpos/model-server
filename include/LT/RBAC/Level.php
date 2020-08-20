<?php

namespace LT\RBAC;

class Level extends \LT\Enum\Base {

	const INHERIT	 = '-';
	const DENY	 = 'd';
	const READ	 = 'r';
	const EDIT	 = 'w';

}
