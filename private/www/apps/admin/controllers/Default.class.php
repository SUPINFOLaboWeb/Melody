<?php

namespace www\admin\controllers;

class DefaultController
{
	public function anyIndexAction()
	{
		var_dump(\Core\Config::getAll());
	}
}