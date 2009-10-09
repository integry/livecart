<?php

ClassLoader::import('application.model.session.SessionHandler');
ClassLoader::import('application.model.session.SessionData');

/**
 * Session storage and retrieval from database
 *
 * @package application.model.session
 * @author Integry Systems
 */
class DatabaseSessionHandler extends SessionHandler
{
	const KEEPALIVE_INTERVAL = 600;

	protected $db;
	protected $isExistingSession;
	protected $id;
	protected $originalData;

	public function open()
	{
		$this->db = ActiveRecordModel::getDBConnection();
		$this->db->sessionHandler = $this;
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($id)
	{
		$data = ActiveRecordModel::getRecordSetArray('SessionData', select(eq('SessionData.ID', $id)));
		$this->isExistingSession = count($data) > 0;

		if ($data)
		{
			$data = array_shift($data);
			$this->originalData = $data['data'];

			if (time() - $data['lastUpdated'] > self::KEEPALIVE_INTERVAL)
			{
				$this->forceUpdate = true;
			}

			$this->id = $data['ID'];
			$this->userID = $data['userID'];
			$this->cacheUpdated = $data['cacheUpdated'];

			return $data['data'];
		}

		return '';
	}

	public function write($id, $data)
	{
		try
		{
			if ($this->isExistingSession)
			{
				if (($this->originalData != $data) || $this->forceUpdate)
				{
					SessionData::updateData($id, $data, $this->userID, $this->cacheUpdated, $this->db);
				}
			}
			else
			{
				SessionData::insertData($id, $data,  $this->userID, $this->cacheUpdated, $this->db);
			}

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	public function destroy($id)
	{
		try
		{
			$inst = ActiveRecordModel::getInstanceByID('SessionData', $id, true);
			$inst->delete();
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	public function gc($max)
	{
		SessionData::deleteSessions($max);
	}
}

?>