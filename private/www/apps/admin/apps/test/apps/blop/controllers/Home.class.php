<?php

namespace www\admin\test\blop\controllers;


class Home extends \Core\Controller
{
	public function anyIndexAction($req, $res)
	{
		echo __DIR__;
		return $res->setViewAbs('', 'blop', array());
	}
}