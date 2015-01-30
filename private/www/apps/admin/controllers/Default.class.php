<?php

namespace www\admin\controllers;

class DefaultController extends \Core\Controller
{
	public function __melody_access()
	{
		// delete /private/cache/access/{namespace/class}.access.php to apply modifications in prod mode

		$this->setDefaultAccessRole(1);
		$this->setAccessRoleException('anyIndexAction', 0);
	}

	public function anyIndexAction($req, $res)
	{
		echo('Bienvenue sur l\'espace d\'administration');
		return $resp;
	}
}