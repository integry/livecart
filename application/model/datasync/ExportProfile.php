<?php

/**
 * Data export profiles
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>
 */
class ExportProfile
{
	protected $name;
	protected $className;
	protected $fields = array();

	public function __construction($className)
	{
		$this->className = $className;
	}

	public function setField($key, $field)
	{
		$this->fields[] = array('key' => $key, 'field' => $field);
	}

	public function setFields($fields)
	{
		foreach ($fields as $key => $field)
		{
			$this->setField($key, $field);
		}
	}

	public function getFields()
	{
		return $this->fields;
	}

}

?>