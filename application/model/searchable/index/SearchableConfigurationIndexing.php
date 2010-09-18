<?php

/**
 * Index configuration for searching
 *
 * @package application.model.searchable
 * @author Integry Systems
 * 
 * 
 * todo: load language files from modules
 *       drop index when adding new language
 * 
 */

class SearchableConfigurationIndexing
{
	private $config;
	private $application;
	private $list = array();
	private $locales = array();
	private $localeCodes = array();

	public function __construct(Config $config, $application)
	{
		$this->config = $config;
		$this->application = $application;
		$this->initLocales();
	}

	public function buildIndex($id)
	{
		ActiveRecordModel::beginTransaction();
		
		ClassLoader::import('application.model.searchable.item.SearchableItem');
		$this->_values = $this->config->getValues();

		if(SearchableItem::getRecordCount() == 0)
		{
			$id = null; // with id null will reindex all
		}
		SearchableItem::bulkClearIndex($id);
		$this->buildList(null, $id);
		
		ActiveRecordModel::commit();
	}

	private function buildList($tree=null, $id)
	{
		if($tree === null)
		{
			$tree = $this->config->getTree();
		}
		foreach($tree as $sectionID=>&$node)
		{
			if($id==null || $sectionID == $id)
			{
				$sectionTitle = $this->config->getSectionTitle($sectionID);
				$sectionMeta = array('section_id' =>$sectionID /*, 'section_title' => $sectionTitle*/);

				$this->addItem($sectionMeta, $this->translationArray($sectionTitle));

				foreach($this->config->getSectionLayout($sectionID) as $layoutKey=>$data)
				{
					$this->addItem($sectionMeta, $this->translationArray($layoutKey));
				}

				foreach($this->config->getSettingsBySection($sectionID) as $configKey=>$meta)
				{
					$key = $meta['title'];
					// add field label.
					$this->addItem(array_merge($sectionMeta, $meta), $this->translationArray($configKey));
					// $this->addItem(array_merge($sectionMeta, $meta), $this->translationArray($key));
					if(array_key_exists('type', $meta))
					{
						if(is_array($meta['type']))
						{
							if($meta['extra'] == 'multi')
							{
								// multiple checkboxes, translate and add all keys with value true
								if(is_array($this->_values[$key]))
								{
									foreach($this->_values[$key] as $checkboxKey=>$checkboxValue)
									{
										if($checkboxValue == true)
										{
											$this->addItem(array_merge($sectionMeta, $meta), $this->translationArray($checkboxKey));
										}
									}
								}
							}
							else
							{
								// dropdown, add only selected value (not every possible option)
								$this->addItem(array_merge($sectionMeta, $meta), $this->translationArray($this->_values[$key]));
							}
						}
						// types: string, image, num, float, longtext has input fields, add field value (everything except arrays and bool)
						else if(in_array($meta['type'], array('string', 'image', 'num', 'float', 'longtext')))
						{
							$this->addItem(array_merge($sectionMeta, $meta), $this->_values[$key]);
						}
					}
				}
			}
			
			if(array_key_exists('subs', $node))
			{
				$node['subs'] = $this->buildList($node['subs'], $id);
			}
			// translating to all languages makes list *very* long
			// add by one section.
			if(array_key_exists($sectionID, $this->list))
			{
				SearchableItem::bulkAddIndex($this->list[$sectionID]);
				unset($this->list[$sectionID]);
			}
		}
		return $tree;
	}

	private function addItem($meta, $value)
	{
		if(array_key_exists('section_id', $meta) == false)
		{
			return false;
		}

		if(array_key_exists($meta['section_id'], $this->list) == false)
		{
			$this->list[$meta['section_id']] = array();
		}

		if(is_array($value))
		{
			foreach($this->localeCodes as $localeCode)
			{
				if(array_key_exists($localeCode, $value))
				{
					$meta['locale'] = $localeCode;
					$v = $value[$localeCode];
					if(strlen($v) > 3)
					{
						$this->list[$meta['section_id']][]= array('section'=>$meta['section_id'], 'meta'=>$meta, 'value'=>$v);
					}
				}
			}
		}
		else
		{
			if(strlen($value) > 3)
			{
				$this->list[$meta['section_id']][]= array('section'=>$meta['section_id'], 'meta'=>$meta, 'value'=>$value);
			}
		}
	}

	private function translationArray($key)
	{
		$translationArray = array();
		foreach($this->locales as $localeCode => $locale)
		{
			//$def = $locale->translator()->translateIfExists($key);
			$def = $locale->translator()->translate($key);
			if ($def)
			{
				$translationArray[$localeCode] = $def;
			}
		}
		return $translationArray;
	}

	private function initLocales()
	{
		ClassLoader::import('library.locale.Locale');
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("Language", "position"), ARSelectFilter::ORDER_ASC);
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isEnabled"), 1));
		$languages=ActiveRecord::getRecordSetArray("Language", $filter);
		foreach($languages as $language)
		{
			$locale = Locale::getInstance($language['ID']);
			$locale->translationManager()->setCacheFileDir(ClassLoader::getRealPath('storage.language'));
			foreach ($this->application->getConfigContainer()->getLanguageDirectories() as $dir)
			{
				$locale->translationManager()->setDefinitionFileDir($dir);
			}
			$locale->translationManager()->setDefinitionFileDir(ClassLoader::getRealPath('storage.language'));
			$locale->translationManager()->loadFile('backend/Settings');
			$this->locales[$language['ID']] = $locale;
		}
		$this->localeCodes = array_keys($this->locales);
	}
}

?>