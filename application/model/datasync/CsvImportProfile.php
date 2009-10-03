<?php

ClassLoader::import('application.model.parser.CsvFile');

/**
 * CSV import profiles
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>
 */
class CsvImportProfile
{
	protected $name;
	protected $className;
	protected $isHead;
	protected $fields = array();
	protected $params = array();

	public function __construction($className)
	{
		$this->className = $className;
	}

	public static function load($fileName)
	{
		if (!file_exists($fileName))
		{
			return null;
		}

		$data = parse_ini_file($fileName, true);
		$className = __CLASS__;
		$instance = new $className($data['className']);
		$instance->setParams($data['params']);

		unset($data['params']);
		foreach ($data as $k => $entry)
		{
			$name = $entry['name'];
			unset($entry['name']);
			$instance->setField($k, $name, $entry);
		}

		return $instance;
	}

	public function setField($index, $name, $params = array())
	{
		$this->fields[$index] = array('name' => $name, 'params' => $params);
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getColumnIndex($name)
	{
		foreach ($this->fields as $index => $field)
		{
			if ($name == $field['name'])
			{
				return $index;
			}
		}

		return null;
	}

	public function isColumnSet($name)
	{
		return !is_null($this->getColumnIndex($name));
	}

	public function getSortedFields()
	{
		$fields = array();
		foreach ($this->fields as $key => $value)
		{
			if ($value)
			{
				list($type, $column) = explode('.', $value['name'], 2);
				$fields[$type][$column] = $key;
			}
		}

		return $fields;
	}

	public function save()
	{
		$data = $this->toArray();

		if (!file_exists(dirname($this->fileName)))
		{
			mkdir(dirname($this->fileName), 0777, true);
		}

		$this->write_ini_file($data, $this->fileName, true);
	}

	public function setName($name)
	{
		$this->setFileName(ClassLoader::getRealPath('storage.configuration.csvImportProfile.') . $name . '.ini');
	}

	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
	}

	public function getRootCategory()
	{
		return Category::getRoot();
	}

	public function getParam($key, $default = null)
	{
		if (isset($this->params[$key]))
		{
			return $this->params[$key];
		}

		return $default;
	}

	public function setParam($key, $value)
	{
		$this->params[$key] = $value;
	}

	public function setParams(array $params)
	{
		$this->params = $params;
	}

	public function getCsvFile($filePath)
	{
		return new CsvFile($filePath, $this->getParam('delimiter', ';'));
	}

	public function extractSection($section)
	{
		$profile = new CsvImportProfile($section);
		$sorted = $this->getSortedFields();
		if (isset($sorted[$section]))
		{
			foreach ($sorted[$section] as $column => $key)
			{
				$field = $this->fields[$key];
				$profile->setField($key, $field['name'], $field['params']);
			}
		}

		return $profile;
	}

	public function toArray()
	{
		$data = array('params' => $this->params);

		foreach ($this->fields as $key => $field)
		{
			$field = array_merge($field, $field['params']);
			unset($field['params']);
			$data[$key] = $field;
		}

		return $data;
	}

    private function write_ini_file($assoc_arr, $path, $has_sections=FALSE)
    {
        $content = "";

        if ($has_sections) {
            foreach ($assoc_arr as $key=>$elem) {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2) {
                    $content .= $key2." = \"".$elem2."\"\n";
                }
            }
        }
        else {
            foreach ($assoc_arr as $key=>$elem) {
                $content .= $key." = \"".$elem."\"\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    }
}

 ?>