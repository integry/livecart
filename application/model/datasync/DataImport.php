<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');

abstract class DataImport
{
	protected $application;
	protected $fields;
	protected $options;
	protected $uid;
	protected $className;
	protected $callback;
	private $lastImportName;
	private $flushMessage;

	public final function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->uid = uniqid('csv_');
	}

	abstract public function getFields();
	abstract protected function getInstance($record, CsvImportProfile $profile);

	public function importInstance($record, CsvImportProfile $profile, $instance = null)
	{
		try
		{
			$instance = is_null($instance) ? $this->getInstance($record, $profile) : $instance;
		}
		catch (ARNotFoundException $e)
		{
			$instance = null;
		}

		if (is_null($instance))
		{
			return;
		}

		$this->className = get_class($instance);
		$defLang = $this->application->getDefaultLanguageCode();

		if ($instance->getID())
		{
			$this->registerImportedID($instance->getID());
		}

		if ((!$instance->isExistingRecord() && ('update' == $this->options['action']))
			|| ($instance->isExistingRecord() && ('add' == $this->options['action'])))
		{
			return false;
		}


		foreach ($profile->getFields() as $csvIndex => $field)
		{
			$column = $field['name'];
			$params = $field['params'];

			$lang = null;
			if (isset($params['language']))
			{
				$lang = $params['language'];
			}

			if (!isset($record[$csvIndex]) || empty($column))
			{
				continue;
			}
			$value = $record[$csvIndex];

			list($className, $field) = explode('.', $column, 2);
			
			if (method_exists($this, 'set_' . $className . '_' . $field))
			{
				$method = 'set_' . $className . '_' . $field;
				$this->$method($instance, $value, $record, $profile);
			}
			else if (method_exists($this, 'set_' . $field))
			{
				$method = 'set_' . $field;
				$this->$method($instance, $value, $record, $profile);
			}
			else if (isset($instance->$field) && ($instance->$field instanceof ARValueMapper) && ($className == $this->getClassName($className, $this->className)))
			{
				if (!$lang)
				{
					$instance->$field->set($value);
				}
				else
				{
					$instance->setValueByLang($field, $lang, $value);
				}
			}
		}

		$idBeforeSave = $instance->getID();
		$instance->save();

		$this->importAttributes($instance, $record, $profile->getSortedFields());

		foreach ($this->getReferencedData() as $section)
		{
			$method = 'import_' . $section;

			if (method_exists($this, $method))
			{
				$subProfile = $profile->extractSection($section);
				if ($subProfile->getFields())
				{
					$this->$method($instance, $record, $subProfile);
				}
			}
		}

		$this->afterSave($instance, $record);
	
		$id = $instance->getID();

		if ($this->callback)
		{
			call_user_func($this->callback, $instance);
		}

		$instance->__destruct();
		$instance->destruct(true);

		ActiveRecord::clearPool();

		return $id;
	}

	protected function getImporterInstance($type)
	{
		$class = $type . 'Import';
		$this->application->loadPluginClass('application.model.datasync.import', $class);
		return new $class($this->application);		
	}
	
	public function importRelatedRecord($type, ActiveRecordModel $instance, $record, CsvImportProfile $profile)
	{
		return $this->getImporterInstance($type)->importInstance($record, $profile, $instance);
	}

	public function disableRecords(ARSelectFilter $filter)
	{
		if ($disable = $this->getDisableFieldHandle())
		{
			$update = new ARUpdateFilter($filter->getCondition());
			$update->addModifier($disable->toString(), 0);
			ActiveRecordModel::updateRecordSet($this->className, $update, $this->getReferencedData());
		}
	}

	public function deleteRecords(ARSelectFilter $filter)
	{
		$del = new ARDeleteFilter($filter->getCondition());
		ActiveRecordModel::deleteRecordSet($this->className, $del, $this->getReferencedData());
	}

	public function isRootCategory()
	{
		return false;
	}

	public function getLastImportedRecordName()
	{
		return $this->lastImportName;
	}

	public function setLastImportedRecordName($name)
	{
		$this->lastImportName = $name;
	}

	public function getUID()
	{
		return $this->uid;
	}

	public function setUID($uid)
	{
		$this->uid = $uid;
	}

	public function beforeImport()
	{

	}

	public function afterImport()
	{

	}

	protected function afterSave(ActiveRecordModel $instance, $record)
	{

	}

	protected function getDisableFieldHandle()
	{

	}

	protected function getReferencedData()
	{
		return array();
	}

	public function importFile(Iterator $file, CsvImportProfile $profile)
	{
		$total = 0;

		do
		{
			$cnt = $this->importFileChunk($file, $profile, 5);
			$total += $cnt;
		}
		while (!is_null($cnt));

		return $total;
	}

	public function importFileChunk(Iterator $file, CsvImportProfile $profile, $count)
	{
		$processed = null;

		while ($file->valid())
		{
			$record = $file->current();

			if (!is_array($record))
			{
				$file->next();
				continue;
			}

			foreach ($record as &$cell)
			{
				$cell = trim($cell);
				if (!$this->isValidUTF8($cell) && function_exists('utf8_encode'))
				{
					$cell = utf8_encode($cell);
				}
			}
			$status = $this->importInstance($record, $profile);

			$file->next();

			if ($this->flushMessage)
			{
				echo $this->flushMessage;
				flush();
			}

			if (++$processed >= $count)
			{
				break;
			}
		}

		return $processed;
	}

	public function setImportPosition(CsvFile $file, $position)
	{
		$processed = 0;

		foreach ($file as $record)
		{
			if (!is_array($record))
			{
				continue;
			}

			if (++$processed < $position)
			{
				continue;
			}

			return;
		}
	}

	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function registerImportedID($id)
	{
		file_put_contents($this->getImportIDFileName(), $id . "\n", FILE_APPEND);
	}

	public function getImportedIDs()
	{
		$file = $this->getImportIDFileName();
		$res = file_exists($file) ? array_filter(explode("\n", file_get_contents($file))) : array();
		return $res;
	}

	public function getImportIDFileName()
	{
		$path = ClassLoader::getRealPath('cache.csvImport.') . $this->uid;
		if (!file_exists(dirname($path)))
		{
			mkdir(dirname($path));
		}

		return $path;
	}

	public function deletedImportIDFile()
	{
		$file = $this->getImportIDFileName();
		if (file_exists($file))
		{
			unlink($file);
		}
	}

	public function getMissingRecordFilter(CsvImportProfile $profile)
	{
		return new ARSelectFilter(new NotInCond(f($this->className . '.ID'), $this->getImportedIDs()));
	}

	protected function importAttributes(ActiveRecordModel $instance, $record, $fields, $attrIdentifier = 'eavField')
	{
		if (isset($fields[$attrIdentifier]))
		{
			$impReq = new Request();
			$fieldClass = ucfirst($attrIdentifier);
			$valueClass = $fieldClass . 'Value';

			foreach ($fields[$attrIdentifier] as $specFieldID => $csvIndex)
			{
				if (empty($record[$csvIndex]))
				{
					continue;
				}

				$attr = ActiveRecordModel::getInstanceByID($fieldClass, $specFieldID, ActiveRecord::LOAD_DATA);
				if ($attr->isSimpleNumbers())
				{
					$impReq->set($attr->getFormFieldName(), (float)$record[$csvIndex]);
				}
				else if ($attr->isSelector())
				{
					if ($attr->isMultiValue->get())
					{
						$values = explode(',', $record[$csvIndex]);
					}
					else
					{
						$values = array($record[$csvIndex]);
					}

					foreach ($values as $fieldValue)
					{
						$fieldValue = trim($fieldValue);

						$f = new ARSelectFilter(
								new LikeCond(
									MultilingualObject::getLangSearchHandle(
										new ARFieldHandle($valueClass, 'value'),
										$this->application->getDefaultLanguageCode()
									),
									$fieldValue . '%'
								)
							);
						$f->setLimit(1);

						var_dump($f->createString()); exit;

						if (!($value = $attr->getRelatedRecordSet($valueClass, $f)->shift()))
						{
							$value = call_user_func_array(array($valueClass, 'getNewInstance'), array($attr));

							if ($attr->type->get() == EavFieldCommon::TYPE_NUMBERS_SELECTOR)
							{
								$value->value->set($fieldValue);
							}
							else
							{
								$value->setValueByLang('value', $this->application->getDefaultLanguageCode(), $fieldValue);
							}

							$value->save();
						}

						if (!$attr->isMultiValue->get())
						{
							$impReq->set($attr->getFormFieldName(), $value->getID());
						}
						else
						{
							$impReq->set($value->getFormFieldName(), true);
						}
					}
				}
				else
				{
					$impReq->set($attr->getFormFieldName(), $record[$csvIndex]);
				}
			}

			$instance->loadRequestData($impReq);
			$instance->save();
		}
	}

	public function getGroupedFields($fields)
	{
		$groupedFields = array();
		foreach ($fields as $field => $fieldName)
		{
			if (strpos($field, '.'))
			{
				list($class, $field) = explode('.', $field, 2);
				$groupedFields[$class][$class . '.' . $field] = $fieldName;
			}
		}

		return $groupedFields;
	}

	public function skipHeader(CsvFile $file)
	{
		$record = $file->current();
		$file->next();
	}

	public function isCompleted(CsvFile $file)
	{
		return !$file->valid();
	}

	public function getClassName()
	{
		preg_match('/(.*)Import/', get_class($this), $match);
		return array_pop($match);
	}
	
	public function getColumnValue($record, CsvImportProfile $profile, $fieldName)
	{
		if ($profile->isColumnSet($fieldName))
		{
			return $record[$profile->getColumnIndex($fieldName)];
		}
	}

	public function setFlushMessage($msg)
	{
		$this->flushMessage = $msg;
	}
	
	public function setCallback($function)
	{
		$this->callback = $function;
	}

	protected function translate($key)
	{
		return $this->application->translate($key);
	}

	protected function makeText($key, $params)
	{
		return $this->application->makeText($key, $params);
	}

	protected function loadLanguageFile($file)
	{
		$this->application->loadLanguageFile($file);
	}

	protected function evalBool($value)
	{
		return !(!$value || in_array(strtolower($value), array('no', 'n', 'false', '0')));
	}

	protected function isValidUTF8($str)
	{
		// values of -1 represent disalloweded values for the first bytes in current UTF-8
		static $trailing_bytes = array (
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
			2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
		);

		$ups = unpack('C*', $str);
		if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
		for ($i = 1; $i <= $aCnt;)
		{
			if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
			if ($tbytes == -1) return false;

			$first = true;
			while ($tbytes > 0 && $i <= $aCnt)
			{
				$cbyte = $ups[$i++];
				if (($cbyte & 0xC0) != 0x80) return false;

				if ($first)
				{
					switch ($b1)
					{
						case 0xE0:
							if ($cbyte < 0xA0) return false;
							break;
						case 0xED:
							if ($cbyte > 0x9F) return false;
							break;
						case 0xF0:
							if ($cbyte < 0x90) return false;
							break;
						case 0xF4:
							if ($cbyte > 0x8F) return false;
							break;
						default:
							break;
					}
					$first = false;
				}
				$tbytes--;
			}
			if ($tbytes) return false; // incomplete sequence at EOS
		}
		return true;
	}

	public function __destruct()
	{
		$this->deletedImportIDFile();
	}
}

?>
