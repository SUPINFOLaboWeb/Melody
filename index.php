<?php
	include('./private/core/Core.class.php');
	
	Core\Core::Init(true);
	Core\Core::Run($_GET['q']);
?>