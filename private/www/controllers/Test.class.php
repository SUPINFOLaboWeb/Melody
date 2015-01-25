<?php

namespace www\controllers;

class TestController extends \Core\Controller
{
	public function anyTestAction()
	{
		var_dump(\Core\Config::getAll());
	}
}