<?php

class LiveCartImporter
{
    private $driver;
    
    public function __construct(LiveCartImport $driver)
    {
        $this->driver = $driver;
    }
    
    /**
     *  Determine what kind of data can be imported
     */
    public function getItemTypes()
    {
        $allTypes = array(        
                'language',
                'currency',
                'category',
                'product',                
            );
            
        $supportedTypes = array();
        
        foreach ($allTypes as $type)
        {
            if (call_user_func_array(array($this->db, 'is' . $type), array()))
            {
                $supportedTypes[$type] = true;
            }
        }
    }
    
    /**
     *  Get current data type
     */
    public function getCurrentType()
    {
        // read from txt file
    }

    /**
     *  Get total number of importable records of the current data type
     */
    public function getCurrentRecordCount()
    {
        
    }

    /**
     *  Get the number of imported records of the current data type
     */
    public function getCurrentProgress()
    {
        
    }    

    /**
     *  Get ID of the last imported record
     */
    public function getCurrentId()
    {
        
    }
    
    /**
     *  Processes data import - one type of data at a time
     *  
     *  Data size is limited to 50 records per call
     */
    public function process()
    {
        $type = $this->getCurrentType();
        
        for ($k = 0; $k <= self::MAX_RECORDS; $k++)
        {
            $id = $this->getCurrentId();
            if (null == $id)
            {
                break;
            }
            
            $record = call_user_func_array(array($this->driver, 'getNext' . $type), array($id));
            if (null == $record)
            {
                return false;
            }
            
            $record->save();
        }
    }
}

?>