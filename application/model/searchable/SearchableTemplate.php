<?php

/**
 * @package application/model/searchable
 * @author Integry Systems
 */

class SearchableTemplate extends SearchableModel
{
	public function getClassName()
	{
		return "SearchableTemplate";
	}
	public function loadClass()
	{

	}
	public function getSelectFilter($searchTerm)
	{
		return null;
	}

	public function isFrontend()
	{
		return false;
	}

	public function isBackend()
	{
		return true;
	}

	public function isARsearch()
	{
		return false;
	}

	public function fetchData()
	{
		$files = $this->getTransformedFiles();
		$baseDir = ClassLoader::getBaseDir();
		$query = $this->getOption('query');
		$found = array();

		foreach($files as $file)
		{
			if(strpos(file_get_contents($file['path']), $query) !== false)
			{
				$found[] = array(
					'id' => $file['id'],
					'name' => basename($file['path'])
					//,'path' => $file['path']
				);
			}
		}

		$count = count($found);
		$ret = array();
		$ret['records'] = array(); // $found;
		$ret['count'] = $count;



		$offset = $this->getOption('offset');
// pp($offset);

		$ret['from'] = $offset;
		if ($ret['from'] == '' || $ret['from'] < 0)
		{
			$ret['from'] = 0;
		}

		$ret['to'] = $this->getOption('limit') + $offset; // $ret['from'];


		for($i = $offset ; $i < $ret['to'] && $i < $ret['count']; $i++)
		{
			$ret['records'][] = $found[$i];
		}


		$diff = $ret['to'] - $ret['from'];
		$c = count($ret['records']);
		if($diff != $c)
		{
			$ret['to'] = $ret['from']+$c;
		}



		$ret['from']++;

		$ret['meta'] = array();

		return $ret;
	}

	private function getTransformedFiles($files=null, $path=array())
	{
		$result = array();
		if ($files === null)
		{
			$files = Template::getFiles();
		}

		foreach($files as $key => $value)
		{
			$newPath = $path;
			if(array_key_exists('subs', $value) && $value['subs'])
			{
				$newPath[] = $key;
				$result = array_merge($result, $this->getTransformedFiles($value['subs'], $newPath));
			}
			else
			{
				$newPath[] = basename($value['id']);

				$result[] = $value;
			}
		}

		return $result;
	}

}

?>