<?php
	include('./private/core/Core.class.php');
	
	Core::Init(true);
	var_dump(Core::$root);
	Core::Run();
?>