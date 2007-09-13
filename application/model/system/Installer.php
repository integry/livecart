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
				'checkWritePermissions',
			);
			
		$res = array();
		foreach ($requirements as $req)
		{
			$res[$req] = call_user_func(array(__CLASS__, $req));
		}
		
		return $res;
	}

    public function checkWritePermissions()
    {
        $writable = array(
        
                'cache',
                'storage',
                'public.cache',
                'public.upload',     
                
            );
            
        $failed = array();
        foreach ($writable as $dir)
        {
            $path = ClassLoader::getRealPath($dir);
            $testFile = $path . '/test.txt';
            
			// try a couple of permissions
			foreach (array(0, '0755', '0777') as $mode)
            {
				if ($mode)
				{
					@chmod($path, $mode);
				}
				
				$res = @file_put_contents($testFile, 'test');
            	
				if ($res)
				{
					break;
				}
			}

            if (!file_exists($testFile))
            {
                $failed[] = $path;
            }
            else
            {
				unlink($testFile);
			}
        }
        
        return 0 == count($failed) ? true : $failed;
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

    public function loadDatabaseDump($dump)
    {
        // newlines
        $dump = str_replace("\r", '', $dump);
        
        // clear comments
        $dump = preg_replace('/#.*#/', '', $dump);
        
        // get queries
        $queries = preg_split('/;\n/', $dump);        
        
        foreach ($queries as $query)
        {
            $query = trim($query);
            if (!empty($query))
            {
                ActiveRecord::executeUpdate($query);
            }
        }
    }
}

?>