<?php
	include('./private/core/Core.class.php');
	
	Core::Init(true);
	Core::Run($_GET['q']);
?>