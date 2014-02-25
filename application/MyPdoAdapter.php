<?php

class MyPdoAdapter extends \Phalcon\Db\Adapter\Pdo\Mysql
{
	public function query($sqlStatement, $placeholders = NULL, $dataTypes = NULL)
	{
		if (strpos($sqlStatement, 'SUBQUERY('))
		{
			$sqlStatement = preg_replace("/SUBQUERY\('([^']+)'\)/", '(\1)', $sqlStatement);
		}
		
		return parent::query($sqlStatement, $placeholders, $dataTypes);
	}
}
