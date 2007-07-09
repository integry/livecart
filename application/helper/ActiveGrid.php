<?php

class ActiveGrid
{    
    const SORT_HANDLE =   0;
    const FILTER_HANDLE = 1;
    
    private $filter;
    private $application;
    
    public static function getFieldType(ARField $field)
    {
		$fieldType = $field->getDataType();
		
		if ($field instanceof ARForeignKeyField || $field instanceof ARPrimaryKeyField)
		{
		  	return null;
		}		            
		
        if ($fieldType instanceof ARBool)
		{
		  	$type = 'bool';
		}	  
		elseif ($fieldType instanceof ARNumeric)
		{
			$type = 'numeric';	  	
		}			
		elseif ($fieldType instanceof ARPeriod)
		{
			$type = 'date';
		}			
		else
		{
		  	$type = 'text';
		}
		
		return $type;        
    }
    
    public function __construct(LiveCart $application, ARSelectFilter $filter, $modelClass = false)
    {        
		$this->application = $application;
        $request = $this->application->getRequest();
        
        // set recordset boundaries (limits)
        $filter->setLimit($request->get('page_size', 20), $request->get('offset', 0));

		// set order
		if ($request->isValueSet('sort_col'))
		{
            $handle = $this->getFieldHandle($request->get('sort_col'), self::SORT_HANDLE);
                        
            if ($handle)
            {
                $filter->setOrder($handle, $request->get('sort_dir'));
            }				
		}
        
		// apply filters
		$filters = $request->get('filters', array());
        foreach ($filters as $field => $value)
		{    		
            if (!strlen($value))
            {
                continue;    
            }
            
            $handle = $this->getFieldHandle($field, self::FILTER_HANDLE);
             
            if (!is_array($handle))
            {
                $fieldInst = $this->getFieldInstance($field);
                
                if ($fieldInst && $fieldInst->getDataType() instanceof ARNumeric)
                {
                    $value = preg_replace('/[ ]{2,}/', ' ', $value);
                    $constraints = explode(' ', $value);
                    foreach ($constraints as $c)
                    {
                        if (in_array(substr($c, 0, 2), array('<>', '<=', '>=')))
                        {
                            $operator = substr($c, 0, 2);
                            $value = substr($c, 2);   
                        }
                        else if (in_array(substr($c, 0, 1), array('>', '<', '=')))
                        {
                            $operator = substr($c, 0, 1);
                            $value = substr($c, 1);   
                        }
                        else
                        {
                            $operator = '=';
                            $value = $c;
                        }
                        
                        if (!is_numeric($value))
                        {
                            continue;
                        }
                        
                        $filter->mergeCondition(new OperatorCond($handle, $value, $operator));
                    }
                }
                else
                {
                    $filter->mergeCondition(new LikeCond($handle, '%' . $value . '%'));
                }
            }
            
            // language field filter
            else
            {
                $cond = null;
                foreach ($handle as $h)
                {
                    $c = new LikeCond($h, '%' . $value . '%');
                    if (!$cond)
                    {
                        $cond = $c;
                    }
                    else
                    {
                        $cond->addOR($c);
                    }
                }   

                $filter->mergeCondition($cond);
            }
        }       	  
		
		// apply IDs to filter
		if ($modelClass)
		{
			$selectedIDs = json_decode($request->get('selectedIDs'));
			if ($selectedIDs)
			{
				if ((bool)$request->get('isInverse'))
				{
					$idcond = new NotINCond(new ARFieldHandle($modelClass, 'ID'), $selectedIDs);				
				}	
				else
				{
					$idcond = new INCond(new ARFieldHandle($modelClass, 'ID'), $selectedIDs);						
				}

		        $filter->mergeCondition($idcond);
			}
			else
			{
				if (!(bool)$request->get('isInverse'))
                {
                    $idcond = new EqualsCond(new ARExpressionHandle(1), 2);
    		        $filter->mergeCondition($idcond);
                } 
            }
		}		
		      
    }
    
    private function getFieldInstance($fieldName)
    {
		list($schemaName, $fieldName) = explode('.', $fieldName);

        if ($schemaName)
		{
            $schema = ActiveRecordModel::getSchemaInstance($schemaName);
			
			return $schema->getField($fieldName);
		}        
    }
    
    private function getFieldHandle($fieldName, $handleType)
    {
        list($schemaName, $fieldName) = explode('.', $fieldName);
		
		$handle = null;
		
        if ($schemaName)
		{
            $schema = ActiveRecordModel::getSchemaInstance($schemaName);
			
			if ($field = $schema->getField($fieldName))
			{
                $handle = new ARFieldHandle($schemaName, $fieldName);
                    
                // language fields
                if ($field->getDataType() instanceof ARArray)
                {
    			  	if (self::SORT_HANDLE == $handleType)
    			  	{
                        $handle = MultiLingualObject::getLangOrderHandle($handle);                            
                    }
                    
                    // filtering by language fields needs two conditions (filter by both current and default language)
                    else
                    {
                        $handleres = array();
                        $defLang = $this->application->getDefaultLanguageCode();
                        $locale = $this->application->getLocaleCode();
                        $handleres[] = MultiLingualObject::getLangSearchHandle($handle, $locale);    
                        if ($locale != $defLang)
                        {                            
                            $handleres[] = MultiLingualObject::getLangSearchHandle($handle, $defLang);    
                        }
                        
                        $handle = $handleres;
                    }
                }
            }
        }
        else
        {
            $handle = new ARExpressionHandle($fieldName);
        }
        
        return $handle;        
    }
}

?>