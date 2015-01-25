<?php

namespace www\admin\controllers;

class DefaultController extends \Core\Controller
{
	public function __melody_access()
	{
		$this->setDefaultAccessRole(1);
		$this->setAccessRoleException('anyIndexAction', 0);
	}

	public function anyIndexAction()
	{
		var_dump(\Core\Config::getAll());
	}
}