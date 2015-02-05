<?php

namespace www\erreur\controllers;

class Home extends \Core\Controller
{
	public function __melody_access()
	{
		// delete /private/cache/access/{namespace/class}.access.php to apply modifications in prod mode

		$this->setDefaultAccessRole(0);
	}

	public function anyIndexAction($req, $res)
	{
		echo('Error');
		return $res->setViewAbs('', 'blop');
	}

}