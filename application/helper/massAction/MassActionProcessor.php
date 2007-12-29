<?php

/**
 * @package application.helper.massAction
 * @author Integry Systems
 */
abstract class MassActionProcessor
{
	protected $grid;
	protected $params;
	protected $completionMessage;
	protected $pid;

	const MASS_ACTION_CHUNK_SIZE = 50;

	public function __construct(ActiveGrid $grid, $params = array())
	{
		$this->grid = $grid;
		$this->params = $params;
		$this->request = $grid->getApplication()->getRequest();
		$this->pid = uniqid();
	}

	public function setCompletionMessage($message)
	{
		$this->completionMessage = $message;
	}

	public function process($loadReferencedRecords = array())
	{
		set_time_limit(0);
		ignore_user_abort(true);

		$this->deleteCancelFile();

		$filter = $this->grid->getFilter();
		$filter->setLimit(0);

		$ids = array();
		foreach (ActiveRecordModel::getFieldValues($this->grid->getModelClass(), $filter, array('ID'), ActiveRecordModel::LOAD_REFERENCES) as $row)
		{
			$ids[] = $row['ID'];
		}

		$totalCount = count($ids);
		$progress = 0;

		$response = new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->completionMessage);

		ActiveRecord::beginTransaction();

		$chunkSize = (count($ids) / self::MASS_ACTION_CHUNK_SIZE) > 5 ? self::MASS_ACTION_CHUNK_SIZE : ceil(count($ids) / 5);

		foreach (array_chunk($ids, $chunkSize) as $chunk)
		{
			//echo round(memory_get_usage() / (1024*1024), 1) . "MB<br>";
			$response->flush('|' . base64_encode(json_encode(array('total' => $totalCount, 'progress' => $progress, 'pid' => $this->pid))));

			$this->processSet(ActiveRecordModel::getRecordSet($this->grid->getModelClass(), new ARSelectFilter(new INCond(new ARFieldHandle($this->grid->getModelClass(), 'ID'), $chunk)), $loadReferencedRecords));
			$progress += count($chunk);
		}

		ActiveRecord::commit();

		$response->flush('|');

		return $response;
	}

	public static function isCancelled($pid)
	{
		$cancelFile = self::getCancelFile($pid);
		$k = 0;
		$ret = false;

		// wait the cancel file for 5 seconds
		while (++$k < 6 && !$ret)
		{
			$ret = file_exists($cancelFile);
			if ($ret)
			{
				unlink($cancelFile);
				break;
			}
			else
			{
				sleep(1);
			}
		}

		return $ret;
	}

	protected function processSet(ARSet $set)
	{
		foreach ($set as $record)
		{
			$this->processRecord($record);
//echo round(memory_get_usage() / (1024*1024), 1) . "MB \n";
			if ('delete' == $this->getAction())
			{
				$this->deleteRecord($record);
			}
			else
			{
				$this->saveRecord($record);
			}

			$record->__destruct();
			unset($record);

			if (connection_aborted())
			{
				$this->cancel();
			}
		}

		$set->__destruct();
		unset($set);
	}

	protected function saveRecord(ActiveRecordModel $record)
	{
		$record->save();
	}

	protected function deleteRecord(ActiveRecordModel $record)
	{
		$record->delete();
	}

	protected function getAction()
	{
		return $this->request->get('act');
	}

	protected function getField()
	{
		return array_pop(explode('_', $this->getAction(), 2));
	}

	protected function processRecord(ActiveRecordModel $record)
	{
		throw new Exception('Implement me');
	}

	private function cancel()
	{
		file_put_contents($this->getCancelFile(), '');
		ActiveRecord::rollback();
		exit;
	}

	private function getCancelFile($pid = null)
	{
		return ClassLoader::getRealPath('cache') . '/.massCancel-' . ($pid ? $pid : $this->pid);
	}

	private function deleteCancelFile()
	{
		if (file_exists($this->getCancelFile()))
		{
			unlink($this->getCancelFile());
		}
	}
}

?>