<?php

/**
 *  Abstract database conversion handler
 *
 *  Imports other shopping cart databases into LiveCart
 *
 *  The following data is imported whenever available:
 *
 *  * products
 *  * categories
 *  * languages
 *  * currencies
 *  * users
 *  * user addresses
 *  * orders
 *  * delivery zones
 *  * configuration
 *
 */
abstract class LiveCartImportDriver
{
	const MAP_SIZE = 100;

	protected $db;
    protected $path;
    protected $importer;

	private $recordMap = array();
	private $recordMapOffsets = array();

    public function __construct($dsn, $path = null)
    {
        $this->db = Creole::getConnection($dsn);
        $this->path = $path;
    }

    public abstract function getTableMap();

    public function setImporter(LiveCartImporter $importer)
    {
        $this->importer = $importer;
    }

	public function isAttribute()
	{
		return false;
	}

	public function isCategory()
	{
		return false;
	}

	public function isCustomerOrder()
	{
		return false;
	}

	public function isCurrency()
	{
		return false;
	}

    public function isLanguage()
    {
        return false;
    }

	public function isProduct()
	{
		return false;
	}

    public function isUser()
    {
        return false;
    }

    public function getNextLanguage()
    {
        return null;
    }

	public function getNextCurrency()
	{
		return null;
	}

	public function getNextManufacturer()
	{
		return null;
	}

	public function getNextUser()
	{
		return null;
	}

    public function getTotalRecordCount($type)
    {
        $tableMap = $this->getTableMap();
        if (isset($tableMap[$type]))
        {
            return array_shift(array_shift($this->getDataBySQL('SELECT COUNT(*) FROM `' . $tableMap[$type] . '`')));
        }
    }

    /**
     *  Checks if the supplied database is valid
     */
    public function isDatabaseValid()
    {
        $tables = array();

        // get database tables
        $info = $this->db->getDatabaseInfo();
        foreach ($info->getTables() as $table)
        {
            $tables[] = $table->getName();
        }

        foreach ($this->getTableMap() as $table)
        {
            if (array_search($table, $tables) === false)
            {
                return false;
            }
        }

        return true;
    }

    public function isPathValid()
    {
        return true;
    }

    public function getRealId($type, $id)
    {
        return $this->importer->getRealId($type, $id);
    }

	protected function loadRecord($sql)
	{
        if (empty($this->recordMap[$sql]))
		{
			$this->recordMapOffset[$sql] = isset($this->recordMapOffset[$sql]) ? $this->recordMapOffset[$sql] + self::MAP_SIZE : 0;
			$this->recordMap[$sql] = $this->getDataBySQL($sql . ' LIMIT ' . $this->recordMapOffset[$sql] . ',' . self::MAP_SIZE);
		}

		if (!empty($this->recordMap[$sql]))
		{
			return array_shift($this->recordMap[$sql]);
		}
		else
		{
			return null;
		}
	}

    protected function getDataBySQL($sql)
    {
		$resultSet = $this->db->executeQuery($sql);
        $dataArray = array();
        while ($resultSet->next())
		{
			$dataArray[] = $resultSet->getRow();
		}

		return $dataArray;
    }

    protected function addLanguage(Language $lang)
    {
        $this->languages[] = $lang;
    }
    
    public function saveCustomerOrder(CustomerOrder $order)
    {
        $order->isFinalized->set(true);
        $order->save();
        
        if ($order->shippingAddress->get())
        {
            $order->shippingAddress->get()->save();
        }

        if ($order->billingAddress->get())
        {
            $order->billingAddress->get()->save();
        }

        $order->totalAmount->set($order->calculateTotal($order->currency->get()));

        return $order->save();
    }    
}

?>