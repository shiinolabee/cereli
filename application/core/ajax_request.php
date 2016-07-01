<?php

	require_once(dirname(__file__).'/../controllers/manager.php');
	
	$manager = new Manager();
	
	$method = 'process_'.$_REQUEST['data'];
	if(method_exists($manager,$method))
		 call_user_func_array(array($manager,$method),array());
	
?>