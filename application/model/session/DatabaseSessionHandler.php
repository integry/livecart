<?php


/**
 * Session storage and retrieval from database
 *
 * @package application.model.session
 * @author Integry Systems
 */
class DatabaseSessionHandler extends BaseSessionHandler
{
	const KEEPALIVE_INTERVAL = 60;

	protected $db;
	protected $isExistingSession;
	protected $id;
	protected $originalData;

	public function open($savePath, $sessionName)
	{
		try
		{
			$this->db = ActiveRecordModel::getDBConnection();
			$this->db->sessionHandler = $this;
			return true;
		}
		catch (SQLException $e)
		{
			return false;
		}
	}

	public function close()
	{
		return true;
	}

	public function read($id)
	{
		if (!$this->db)
		{
			return;
		}

		try
		{
			$data = ActiveRecordModel::getRecordSetArray('SessionData', select(eq('SessionData.ID', $id)));
		}
		catch (SQLException $e)
		{
			return '';
		}

		$this->isExistingSession = count($data) > 0;

		if ($data)
		{
			$data = array_shift($data);
			$this->originalData = $data['data'];

			if ((time() - $data['lastUpdated'] > self::KEEPALIVE_INTERVAL) || (!$data['userID'] && !$data['cacheUpdated']))
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