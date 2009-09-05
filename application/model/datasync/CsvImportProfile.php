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

		$data = parse_ini_file($fileName);
		$className = __CLASS__;
		$instance = new $className($data['className']);

		if (isset($data['isHead']))
		{
			$instance->setIsHead($data['isHead']);
		}

		if (isset($data['params']))
		{
			$instance->setParams($data['params']);
		}

		$k = 0;
		do
		{
			++$k;
			if (isset($data[$k]))
			{
				$name = $data[$k]['name'];
				unset($data[$k]['name']);
				$this->setField($k, $name, $data[$k]);
			}
		}
		while (isset($data[$k]));

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
		$data = array('className' => $this->className,
					  'isHead' => $this->isHead,
					  'params' => $this->params);

		$data = array_merge($data, $this->fields);

		if (!file_exists(dirname($filename)))
		{
			mkdir(dirname($filename));
		}

		$this->write_ini_file($data, $this->fileName);
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

	public function getCsvFile($filePath)
	{
		return new CsvFile($filePath, $this->getParam('delimiter', ';'));
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