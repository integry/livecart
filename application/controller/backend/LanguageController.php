<?php

ClassLoader::import("application.controller.backend.abstract.SiteManagementController");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.Locale.*");
/**
 * Language management
 * Handles adding languages, modifying language definitions (translations), activating and deactivating languages
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 * @role admin.site.language
 */
class LanguageController extends SiteManagementController
{
	
	const langFileExt = 'lng';

	protected function languageSectionBlock()
	{
		$list = Language::getLanguages(1);

		$response = new BlockResponse();
		$response->SetValue("languagesList", $list->toArray());

		$response->SetValue("language", $this->request->getValue("language"));
		$response->SetValue("controller", $this->request->getValue("controller"));
		$response->SetValue("action", $this->request->getValue("action"));
		$response->SetValue("id", $this->request->getValue("id"));

		return $response;
	}

	public function init()
	{
		parent::init();
		$this->addBlock("NAV", "languageSection");
	}

	/**
	 * Gets definitions from project files and updates them in database.
	 * @return ActionResponse
	 */
	public function update()
	{
		$enLocale = Locale::getInstance('en');
		$enLocale->translationManager()->updateDefinitions();

		return new ActionRedirectResponse($this->request->getControllerName(), 'index');
	}

	/**
	 * Displays definitions edit page.
	 * @return ActionResponse	 
	 */
	public function edit()
	{
		// get locale instance for the language being translated
		$editLocaleName = $this->request->getValue('id');
		$editLocale = Locale::getInstance($editLocaleName);

		// get all English configuration files
		$enLocale = Locale::getInstance('en');
		$fileDir = ClassLoader::getRealPath('application.configuration.language.en');
		$files = $enLocale->translationManager()->getDefinitionFiles($fileDir);

		// get currently translated definitions
		$translated = array();
		$enDefs = array();
		foreach ($files as $file)
		{
			$relPath = substr($file, strlen($fileDir));
			
			// get default English definitions (to get all definition keys)
			$keys = $enLocale->translationManager()->getFileDefs($file);

			$enDefs[$relPath] = $keys;

		  	foreach ($keys as $key => $value)
		  	{
			    $keys[$key] = '';
			}
				  		  			
			// get language default definitions
			$default = $editLocale->translationManager()->getFileDefs($relPath, true);
			
			// get translated definitions
			$transl = $editLocale->translationManager()->getCacheDefs($relPath, true);
			
			// put all definitions together
			$translated[$relPath] = array_merge($keys, $default, $transl);	
		}

		// modifying a single file definitions only
		if ($this->request->isValueSet('file'))
		{
			$file = $this->request->getValue("file");
			$toTranslate = $translated[$file];
			$translated = array();
			$translated[$file] = $toTranslate;
		}		

		// determine which definitions should be displayed (All, defined, undefined)
		$selectedAll = '';
		$selectedDefined = '';
		$selectedNotDefined = '';

		switch ($this->request->getValue("show"))
		{
			case 'all':
				$selectedAll = 'checked';
			break;

			case 'defined':
				$selectedDefined = 'checked';
			break;

			case 'notDefined':
				$selectedNotDefined = 'checked';
			break;
			
			default:
				$selectedAll = 'checked';
			break;
		}

		// remove definitions that do not need to be displayed
		if ($selectedDefined || $selectedNotDefined)
		{
			foreach ($translated as $file => &$values)
			{
			  	foreach ($values as $key => $value)
			  	{
				    if (($selectedDefined && '' == $value) || ($selectedNotDefined && '' != $value))
				    {
					  	unset($values[$key]);
					}
				}
				
				if (0 == count($values))
				{
				  	unset($translate[$file]);
				}
			}  
		}		

		$response = new ActionResponse();
		$response->SetValue("language", $this->request->getValue("language"));
		$response->SetValue("edit_language", $editLocale->info()->getLanguageName($editLocaleName));
		$response->setValue("id", $editLocaleName);

		$nfiles = array();
		foreach ($files as $key => $value)
		{
			$nfiles[$value] = ucfirst(basename($value, '.' . self::langFileExt));
		}	
	
		$files = array_merge(array('' => $this->locale->translator()->translate('_allFiles')), $nfiles);
		
		$response->setValue("files", $files);
		$response->setValue("file", $file);

		$response->setValue("en_definitions", $enDefs);
		$response->setValue("definitions", $translated);

		$response->setValue("selected_all", $selectedAll);
		$response->setValue("selected_defined", $selectedDefined);
		$response->setValue("selected_not_defined", $selectedNotDefined);
		$response->setValue("show", $this->request->getValue("show"));				

		return $response;
	}

	/**
	 * Saves translations
	 * @return ActionRedirectResponse
	 */
	public function save()
	{
		// get locale instance
		$localeCode = $this->request->getValue("id");		
		$editLocale = Locale::getInstance($localeCode);
		
		if (!$editLocale)
		{
		  	throw new ApplicationException('Locale "' . $localeCode .'" not found');
		}
		
		// get submited translation data
		$submitedLang = $this->request->getValue("lang");
		if (!is_array($submitedLang))
		{
		  	$submitedLang = array();
		}
		
		// walk through all files and update definitions
		foreach ($submitedLang as $file => $data)
		{
		  	$file = substr($file, 0, -4);
			$existing = $editLocale->translationManager()->getCacheDefs($file . '.php', true);				
		  	$data = array_merge($existing, $data);
		  	$editLocale->translationManager()->saveCacheData($localeCode . '/' . $file, $data);
		}
			
		return new ActionRedirectResponse($this->request->getControllerName(), 'edit', array('id' => $localeCode));
	}
	
	/**
	 * Displays main admin page.
	 * @return ActionResponse
	 */
	public function index()
	{
		// get all Locale languages
		$languagesSelect = $this->locale->info()->getAllLanguages();

		// get all system languages
		$list = Language::getLanguages()->toArray();
		$countActive = 0;
		foreach($list as $key => $value)
		{
			if ($value["isEnabled"] == 1)
			{
				$countActive++;
			}
			$list[$key]['name'] = $this->locale->info()->getLanguageName($value['ID']);
			
			// remove already added languages from Locale language list
			unset($languagesSelect[$value['ID']]);			
		}

		// sort Locale language list
		asort($languagesSelect);

		$response = new ActionResponse();
		$response->SetValue("language", $this->request->getValue("language"));
		$response->SetValue("languagesList", $list);
		$response->SetValue("languages_select", $languagesSelect);
		$response->SetValue("count_all", count($list));
		$response->SetValue("count_active", $countActive);

		return $response;
	}
	
	public function delete()
	{  	
		$langId = $this->request->getValue('id');
		
		try
	  	{
			// make sure the language record exists
			$inst = Language::getInstanceById($langId);
			
			$success = $langId;
			
			// make sure it's not the default language
			if (true == $inst->isDefault->get())
				$success = false;
			
			// remove it
			if ($success)
			{
				ActiveRecord::deleteByID('Language', $langId);
			}

		}
		catch (Exception $exc)
		{			  	
		  	$success = false;
		}
		  
		$resp = new RawResponse();
	  	$resp->setContent($success);
		return $resp;
	}

	public function saveOrder()
	{
	  	$order = $this->request->getValue('languageList');
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('Language', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('Language', $update);  	
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->getValue('draggedId'));
		return $resp;		  	
	}

	/**
	 * Sets default language.
	 * @return ActionRedirectResponse
	 */
	function setDefault()
	{
		Language::setDefault($this->request->getValue("id"));  	
		return new ActionRedirectResponse($this->request->getControllerName(), "index");		
	}

	/**
	 * Displays variuos information.
	 * @return ActionResponse
	 */
	public function information()
	{
		$locale = Locale::GetInstance($this->localeName);
		$response = new ActionResponse();

		//$masyvas["I18Nv2::getInfo()"] = I18Nv2::getInfo();
		$masyvas["\$locale->GetCountries()"] = $locale->info()->GetAllCountries();
		$masyvas["\$locale->GetLanguages()"] = $locale->info()->GetAllLanguages();
		$masyvas["\$locale->GetCurrencies()"] = $locale->info()->GetAllCurrencies();

		$response->SetValue("masyvas", $masyvas);
		return $response;
	}

	/**
	 * @todo Perdaryti siuo metu neveikia
	 */
	public function add()
	{
		if ($this->request->isValueSet("new_language"))
		{
			Language::add($this->request->getValue("new_language"));
		}

		return new ActionRedirectResponse($this->request->getControllerName(), "index", array());
	}

	/**
	 * Sets if language is enabled
	 * @return ActionRedirectResponse
	 */
	public function setEnabled()
	{
		$id = $this->request->getValue('id');		
		Language::setEnabled($id, $this->request->getValue("status"));
		
		$this->setLayout('empty');		
		$response = new ActionResponse();
		$item = Language::getInstanceById($id)->toArray();
		$item['name'] = $this->locale->info()->getLanguageName($item['ID']);
		$response->setValue('item', $item);

		return $response;		
	}

}

?>
