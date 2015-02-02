<?php

namespace www\controllers;

class Test extends \Core\Controller
{
	public function anyTestAction()
	{
		var_dump(\Core\Config::getAll());
	}
}