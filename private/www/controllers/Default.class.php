<?php

namespace www\controllers;

class DefaultController extends \Core\Controller
{
	public function anyIndexAction($req, $res)
	{
		echo 'Developpment of Melody 0.3 in progress ...';
		return $res->setViewAbs(array('admin'), 'blop', array());
	}
}