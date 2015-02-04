<?php

namespace www\admin\test\controllers;


class Home extends \Core\Controller
{
	public function anyIndexAction($req, $res)
	{
		echo __DIR__;
		return $res->setViewAbs('', 'blop', array());
	}
}