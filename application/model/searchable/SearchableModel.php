<?php

/**
 * Text searching functionality for models (other than Products in frontend)
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
abstract class SearchableModel
{
	const FRONTEND_SEARCH_MODEL = 1;
	const BACKEND_SEARCH_MODEL = 2;

	protected $application;
	protected $options = array();
	
	public abstract function getClassName();
	public abstract function loadClass();
	public abstract function getSelectFilter($searchTerm);

	public static function getInstances($modelType=1)
	{
		$ret = array();
		foreach (self::getSearchableModels() as $file)
		{
			include_once $file;
			$class = basename($file, '.php');
			$inst = new $class();
			$inst->loadClass();
			if ((self::BACKEND_SEARCH_MODEL & $modelType && $inst->isBackend()) || (self::FRONTEND_SEARCH_MODEL & $modelType && $inst->isFrontend()))
			{
				$ret[$inst->getClassName()] = $inst;
			}
		}
		return $ret;
	}

	public static function getInstanceByModelClass($class, $modelType=1)
	{
		$instances = self::getInstances($modelType);

		if (isset($instances[$class]))
		{
			return $instances[$class];
		}
	}

	public static function getSearchableModels()
	{
		$ret = array();
		$cd = getcwd();
		chdir(dirname(__file__));
		foreach (glob('*.php') as $file)
		{
			$file = realpath($file);
			if ($file != __file__)
			{
				$ret[] = $file;
			}
		}

		chdir($cd);

		foreach (ActiveRecordModel::getApplication()->getPlugins('searchable') as $plugin)
		{
			$ret[] = $plugin['path'];
		}

		return $ret;
	}

	public static function getSearchableModelClasses($modelType=1)
	{
		$ret = array();
		foreach (self::getInstances($modelType) as $inst)
		{
			$ret[] = $inst->getClassName();
		}

		return $ret;
	}

	public function getWeighedSearchCondition($fields, $searchTerm)
	{
		if (!is_array($searchTerm))
		{
			$searchTerm = array($searchTerm);
		}

		$if = array();
		foreach ($fields as $field => $weight)
		{
			foreach ($searchTerm as $term)
			{
				$cond = new LikeCond(new ARFieldHandle($this->getClassName(), $field), '%' . $term . '%');
				$if[] = 'IF(' . $cond->toString() . ', ' . $weight . ', ';
			}
		}

		return implode('', $if) . 0 . str_repeat(')', count($if));
	}

	public function toArray()
	{
		$arr = array();
		$arr['class'] = $this->getClassName();
		$arr['template'] = 'custom:search/block/result_' . $arr['class'] .'.tpl';
		$arr['name'] = ActiveRecordModel::getApplication()->translate($arr['class']);
		return $arr;
	}

	public function isFrontend()
	{
		return true;
	}

	public function isBackend()
	{
		return true;
	}

	public function setOption($key, $value)
	{
		$this->options[$key] = $value;
	}
	
	public function getOption($key, $default=false)
	{
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
}

?>