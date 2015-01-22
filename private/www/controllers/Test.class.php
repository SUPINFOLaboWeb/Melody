<?php

namespace www\controllers;

class Test
{
	public function anyTestAction()
	{
		var_dump(\Core\Config::getCore('access_role_session_key'));
	}
}