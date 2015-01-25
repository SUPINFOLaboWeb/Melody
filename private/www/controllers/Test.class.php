<?php

namespace www\controllers;

class TestController
{
	public function anyTestAction()
	{
		var_dump(\Core\Config::getAll());
	}
}