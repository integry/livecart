<?php

ClassLoader::import("application.model.user.User");

/**
 * Handles session data storage in database
 *
 * @package application.model.session
 * @author Integry Systems <http://integry.com>
 */
class SessionData extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $userID;
		public $lastUpdated;
		public $cacheUpdated;
		public $data;
	}

	/*####################  Static method implementations ####################*/

	public static function updateData($id, $data, $userID, $cacheUpdated, $db)
	{
		$sql = 'UPDATE SessionData SET ' . self::enumerateUpdateFields($data, $userID, $cacheUpdated) . ' WHERE ID="' . $id .'"';
		self::executeQuery($db, $sql);
	}

	public static function insertData($id, $data, $userID, $cacheUpdated, $db)
	{
		if (!$userID)
		{
			$cacheUpdated = time();
		}

		$sql = 'INSERT INTO SessionData SET ID="' . $id .'", ' . self::enumerateUpdateFields($data, $userID, $cacheUpdated);
		try
		{
			self::executeQuery($db, $sql);
		}
		catch (Exception $e)
		{
			self::updateData($id, $data, $userID, $cacheUpdated, $db);
		}
	}

	private static function enumerateUpdateFields($data, $userID, $cacheUpdated)
	{
		return 'data="' . addslashes($data) . ($userID ? '", userID="' . $userID : '') . '", cacheUpdated="' . $cacheUpdated . '", lastUpdated=' . time();
	}

	public static function deleteSessions($max)
	{
		$sql = 'DELETE FROM SessionData WHERE lastUpdated < ' . (time() - $max);
		self::executeQuery(null, $sql);
	}

	public static function transformArray($array)
	{
		return $array;
	}

	private function executeQuery($db, $sql)
	{
		if (!$db)
		{
			try
			{
				$db = ActiveRecord::getDBConnection();
			}
			catch (SQLException $e)
			{
				throw $e;
			}
		}

		if ($db)
		{
			$db->query($sql);
			ActiveRecord::commit();
		}
	}
}

?>
