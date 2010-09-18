<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Searchable item entry
 *
 * @package application.model.searchable
 * @author Integry Systems <http://integry.com>
 */
class SearchableItem extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__CLASS__);
		$schema->registerField(new ARField("section", ARVarchar::instance(64)));
		$schema->registerField(new ARField("value", ARText::instance() ));
		$schema->registerField(new ARField("locale", ARVarchar::instance(2)));
		$schema->registerField(new ARField("meta", ARText::instance()));
		// $schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		// $schema->registerField(new ARField("type", ARVarchar::instance(16)));
	}


	public static function getRecordCount($type=null)
	{
		return ActiveRecordModel::getRecordCount(__CLASS__, select()); // add type filter, when more than one searchable item type.
	}

	public static function bulkClearIndex($section)
	{
		ActiveRecordModel::executeUpdate('DELETE FROM '.__CLASS__.(
			$section
				? ' WHERE section=0x'.bin2hex($section)
				:''
			)
		); // and type = ..
	}

	public static function bulkAddIndex($list)
	{
		$chunks = array();
		while($item = array_pop($list))
		{
			$locale = array_key_exists('locale', $item['meta']) ? $item['meta']['locale'] : null;
			if($locale && strlen($locale))
			{
				$locale = '0x'.bin2hex($locale);
			}
			else
			{
				$locale = 'NULL';
			}
			$chunks[] = sprintf('(%s, %s, %s, %s)',
				'0x'.bin2hex($item['value']),
				$locale,
				array_key_exists('section', $item) ? '0x'.bin2hex($item['section']) : 'NULL',
				'0x'.bin2hex(serialize($item['meta']))
			);
		}
		ActiveRecordModel::executeUpdate('INSERT INTO '.__CLASS__.'(value, locale, section, meta) VALUES '.implode(',',$chunks));
	}
}

?>