<?php

ClassLoader::import("application.controller.backend.abstract.SiteManagementController");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.Locale.*");
/**
 *
 * @package application.controller.backend
 */
class LanguageController extends SiteManagementController
{

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
		$path = ClassLoader::getRealPath("application.language_definitions.en");

		$enLocale = Locale::getInstance('en');
		$enLocale->translationManager()->setDefinitionFileDir($path);
		$enLocale->translationManager()->updateDefinitions();

		return new ActionRedirectResponse($this->request->getControllerName(), 'edit', array('id' => 'en'));
	}

	/**
	 * Displays definitions edit page.
	 */
	public function edit()
	{
		// get locale instance for the language being translated
		$editLocaleName = $this->request->getValue('id');
		$editLocale = Locale::getInstance($editLocaleName);

		// get all English configuration files
		$enLocale = Locale::getInstance('en');
		$fileDir = ClassLoader::getRealPath('application.language_definitions.en');
		$files = $enLocale->translationManager()->getDefinitionFiles($fileDir);

		// determine which definition belongs to which file
		$fileDefs = array();
		foreach ($files as $file)
		{
		  	$filePath = $fileDir . '/' . $file;
		  	$unsorted = $enLocale->translationManager()->getFileDefs($filePath);
		  	foreach ($unsorted as $key => $value)
		  	{
				$fileDefs[$key] = $file;
			}
		}

		// get currently translated definitions
		$unsorted = $editLocale->translationManager()->getTranslatedDefinitions($editLocale->getLocaleCode(), false);
		
		// sort definitions by files
		$translated = array();
		foreach ($unsorted as $key => $value)
		{		 	
		  	$file = isset($fileDefs[$key]) ? $fileDefs[$key] : '';
			$translated[$file][$key] = $value;
		}

		echo $this->request->getValue("file");

		// modifying a single file definitions only
		if ($this->request->isValueSet('file'))
		{
			$file = $this->request->getValue("file");
			$toTranslate = $translated[$file];
			$translate = array();
			$translate[$file] = $toTranslate;
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

			case 'not_defined':
				$selectedNotDefined = 'checked';
			break;
			
			default:
				$selectedAll = 'checked';
			break;
		}

		// remove definitions that do not need to be displayed
		if ($selectedDefined || $selectedNotDefined)
		{
			foreach ($translate as $file => &$values)
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

		$files = array_merge(array('' => $this->locale->translator()->translate('allFiles')), $files);
		$response->setValue("files", $files);
		$response->setValue("file", $file);

		$response->setValue("en_definitions", $enLocale->translationManager()->getTranslatedDefinitions('en'));
		$response->setValue("definitions", $translated);

		$response->setValue("selected_all", $selected_all);
		$response->setValue("selected_defined", $selected_defined);
		$response->setValue("selected_not_defined", $selected_not_defined);
		$response->setValue("show", $this->request->getValue("show"));				

		return $response;
	}

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
		
		// get existing translation data
		$existing = $editLocale->translationManager()->getTranslatedDefinitions($localeCode, false);
		
		// merge together
		$langData = array_merge($existing, $submitedLang);
		
		// save translations
		$editLocale->translationManager()->saveDefinitions($langData);
		
		return new ActionRedirectResponse($this->request->getControllerName(), 'edit', array('id' => $localeCode));
	}
	
	/**
	 * Displays main admin page.
	 */
	public function index()
	{

		$languages_select = $this->locale->info()->getAllLanguages();

		$list = Language::getLanguages()->toArray();
		$count_active = 0;
		foreach($list as $key => $value)
		{
			if ($value["isEnabled"] == 1)
			{
				$count_active++;
			}
			//unset($languages_select[$value['code']]);
			$list[$key]['name'] = $this->locale->info()->getLanguageName($value['ID']);
		}

		$response = new ActionResponse();
		$response->SetValue("language", $this->request->getValue("language"));
		$response->SetValue("languagesList", $list);
		$response->SetValue("languages_select", $languages_select);
		$response->SetValue("count_all", count($list));
		$response->SetValue("count_active", $count_active);

		/*for ($i = 1; $i < 200; $i ++) {

		$number[] = $i;
		}*/

		//$response->SetValue("number", $number);
		return $response;
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
		if ($this->request->isValueSet("change_active"))
		{
			Language::setEnabled($this->request->getValue("change_active"), $this->request->getValue("change_to"));
		}

		$array = array("id" => 0);
		if ($this->request->isValueSet("language"))
		{
			$array["language"] = $this->request->getValue("language");
		}
		return new ActionRedirectResponse($this->request->getControllerName(), "index", $array);
	}

	/**
	 * Sets default language.
	 * @return ActionRedirectResponse
	 */
	public function setDefault()
	{
		if ($this->request->isValueSet("change_to"))
		{
			Language::setDefault($this->request->getValue("change_to"));
		}

		$array = array("id" => 0);
		if ($this->request->isValueSet("language"))
		{
			$array["language"] = $this->request->getValue("language");
		}
		return new ActionRedirectResponse($this->request->getControllerName(), "index", $array);
	}
}

?>
