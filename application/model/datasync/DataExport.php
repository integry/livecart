<?php


/**
 * Handle data export in CSV format
 *
 * @package application/model
 * @author Integry Systems <http://integry.com>
 */
abstract class DataExport
{
	protected $profile;

	public function __construct(ExportProfile $profile)
	{
		$this->profile = $profile;
	}

	protected abstract function writeData($data);

	public function exportInstance(ActiveRecordModel $instance)
	{
		return $this->exportArray($instance->toArray());
	}

	public function exportArray($array)
	{
		$result = array();
		foreach ($this->profile->getFields() as $fieldData)
		{
			$field = $fieldData['field'];

			$res = '';
			if (!strlen($field))
			{
				$res = '';
			}
			else if (preg_match('/"(.*)"/', $field, $match))
			{
				$res = $match[1];
			}
			else if (!preg_match('/[^\/-_a-zA-Z0-9]/', $field))
			{
				$res = $this->getValue($array, $field);
			}
			else if (preg_match_all('/\%\{(.*)\}/msU', $field, $matches))
			{
				foreach ($matches[1] as $key)
				{
					$field = str_replace('%{' . $key . '}', $this->getValue($array, $key), $field);
				}

				$res = $field;
			}

			$result[] = $res;
		}

		$this->writeData($result);
	}

	private function getValue($array, $key)
	{
		$res = $array;
		foreach (explode('/', $key) as $key)
		{
			if (isset($res[$key]))
			{
				$res = $res[$key];
			}
			else
			{
				$res = '';
				break;
			}
		}

		return $res;
	}
}

?>
