<?php

class Installer
{
	public function checkRequirements(LiveCart $application)
	{
		$requirements = array(
		
				'checkPHPVersion',
				'checkMySQL',
				'checkGD',
				'checkSession',
			
			);
			
		$res = array();
		foreach ($requirements as $req)
		{
			$res[$req] = call_user_func(array(__CLASS__, $req));
		}
		
		return $res;
	}

	public function checkPHPVersion()
	{
		return 1 == version_compare(phpversion(), '5.2', '>=');
	}

	public function checkMySQL()
	{
		return function_exists('mysqli_get_server_version');
	}
	
	public function checkGD()
	{
		return function_exists('gd_info');
	}

	public function checkSession()
	{
		if (!session_id())
		{
			session_start();
		}
		
		$_SESSION['test'] = 'LiveCart';
		
		ob_start();
		session_write_close();
		$c = ob_get_contents();
		ob_clean();
		
		return !$c;
	}
	
	public function checkMySQLVersion()
	{
		$result = mysqli_get_server_version();
	    $mainVersion = round($result/10000, 0);
	    $minorVersion = round(($result-($mainVersion*10000))/100, 0);
	    $subVersion = $result-($minorVersion*100)-($mainVersion*10000);		

		return 1 == version_compare($mainVersion . '.' . $minorVersion . '.' . $subVersion, '4.1', '>=');
	}

	public function checkApache()
	{
		
	}

}

?>