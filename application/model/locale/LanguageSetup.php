<?php

/**
 * Class for creating english definitions from definitions files and storing in interfaceTranslation.
 * @package application.model.locale
 * @author Denis Slaveckij
 */
class LanguageSetup {
  
  	private $dir;
  
  	public function __construct($dir = "") {
	    
	    $this->dir = $dir;	
	}
	
	/**
	 * Update definitions from files.
	 */
	public function updateFromFiles() {	
		
		$defs = $this->getDefinitions();		
		
		//kol kas padariau taip nes ten problemos su ActiveRecord 
		$db = ActiveRecord::getDbConnection();
		
		$schema = ActiveRecord::getSchemaInstance("InterfaceTranslation");
		$res = $db->executeQuery("SELECT * FROM ".$schema->getName());				  

		while ($res->next()) {
						
			if ((string)$res->getString("ID") == "en") {
			  
			  	//print_r($defs);
			  	//echo "\n\n\n----------\n\n\n";
			  	$db->executeUpdate("UPDATE ".$schema->getName()." SET InterfaceData = '".addslashes(serialize($defs))."' WHERE ID = 'en' ");
			} else {
			  
				$current = unserialize((string)$res->getString("interfaceData"));  
			
				//print_r($current);
				foreach ($defs as $key => $value) {
				    
				    if (isSet($current[$key])) {
				      				      						
				      	$defs[$key][Locale::value] = $current[$key][Locale::value];					  
					} else {
					  
					  	$defs[$key][Locale::value] = "";					  
					}					
				}	
				
				$db->executeUpdate("UPDATE ".$schema->getName()." SET InterfaceData = '".addslashes(serialize($defs))."' WHERE ID = '".(string)$res->getString("ID")."' ");
				
			}
			
			//return (int)$res->getInt("products_count")
		}	
		  	
		/*$dataSet = ActiveRecord::getRecordSet("InterfaceTranslation", new ARSelectFilter(), true, true);				
	
		//$dataEn = ActiveRecord::getInstanceByID("InterfaceTranslation", array("ID" => "en"), true);				
		print_r($dataSet);exit();
		
		$defs = $this->getDefinitions();		
		foreach ($dataSet as $data) {
		  	  	
		  	if ($data->getID() == "en") {
							
				$data->interfaceData->Set(serialize($defs));			
				$data->Save();    
			} else {
			  					
				if (is_array($data->interfaceData->get())) {
				  
				  	$current = unserialize($data->interfaceData->get());
				}						
							  
			  	foreach ($defs as $key => $value) {
				    
				    if (isSet($current[$key])) {
				      
				      	$defs[$key][Locale::value] = $current[$key];					  
					} else {
					  
					  	$defs[$key][Locale::value] = "";					  
					}					
				}					
				
				$data->interfaceData->set(addslashes(serialize($defs)));
				$data->Save();
			}
		}	*/	
	}

	/**
	 * Gets all translations.
	 * @return array
	 * @todo strtolower
	 */	
	private function getDefinitions() {
	  
	  	$iter = new DirectoryIterator($this->dir);
	  	
	  	$defs = array();
	  	foreach ($iter as $value) {
		    
		    if ($value->isFile() && (substr($name = $value->GetFileName(), -4)) == ".lng") { 			 	
			 	
			 	$short = substr($name, 0, -4);			 	
				$defs += $this->GetFileDefs($this->dir.$name, $short);			 				 									
			}
		}
		return $defs;
	}
	
	/**
	 * Gets all translations from file (almost copied from k-rates)
	 * @param string $file File name
	 * @return array 
	 */
	private function getFileDefs($file, $short = '') {

        if (!file_exists($file))
            return false;

        $defs = array();

        $f = fopen($file,'r');
        while (!feof($f)) {
          
            $s = chop(fgets($f,4096));

            if (strlen($s) == 0)
                continue;

            if ($s{0} == '#')
                continue;

            list($key,$value) = explode('=',$s,2);
            $defs[$key][Locale::value] = $value;
            $defs[$key][Locale::file] = $short;
        }

        fclose($f);
        return $defs;
    }
  
  	  
  
  
  
  
  
  
  
  
  
  
}




?>