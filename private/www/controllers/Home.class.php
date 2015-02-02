<?php

namespace www\controllers;


class Home extends \Core\Controller
{
	public function anyIndexAction($req, $res)
	{
		echo 'Developpment of Melody 0.3 in progress ...';
		return $res->setView('blop', array());
	}

	public function anyCaseSensitiveMethodAction($req, $res)
	{
		echo 'Test réussi';
		return $res->setView('blop', array())->cache();
	}

	public function anyDataAction($req, $res)
	{
		var_dump(\Core\Data::User()->test());
		return $res->setView('blop', array());
	}

	public function test($req, $res)
	{
		$req->setAttribute('blop', 'test');
		var_dump($req);
	}
}