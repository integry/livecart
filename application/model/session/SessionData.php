<?php

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
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ArChar::instance(32)));
		$schema->registerField(new ARField("lastUpdated", ArInteger::instance(12)));
		$schema->registerField(new ARField("cacheUpdated", ArInteger::instance(12)));
		$schema->registerField(new ARField("data", ArBinary::instance(0)));
	}

	/*####################  Static method implementations ####################*/

	public static function updateData($id, $data, $db)
	{
		$sql = 'UPDATE SessionData SET data="' . addslashes($data) . '", lastUpdated=' . time() . ' WHERE ID="' . $id .'"';
		$db->executeQuery($sql);
	}

	public static function insertData($id, $data, $db)
	{
		$sql = 'INSERT INTO SessionData SET ID="' . $id .'", data="' . addslashes($data) . '", lastUpdated=' . time();
		try
		{
			$db->executeQuery($sql);
		}
		catch (Exception $e)
		{
			self::updateData($id, $data, $db);
		}
	}

	public static function deleteSessions($max)
	{
		$sql = 'DELETE FROM SessionData WHERE lastUpdated < ' . (time() - $max);
		$db->executeQuery($sql);
	}

	public static function transformArray($array)
	{
		return $array;
	}
}

?>
