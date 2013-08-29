<?php


/**
 * Language management
 * Handles adding languages, modifying language definitions (translations), activating and deactivating languages
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class LanguageController extends StoreManagementController
{
	const langFileExt = 'lng';

	/**
	 * @role language
	 * @return ActionResponse
	 */
	public function exportAction()
	{
		// preload current locale
		$this->locale;

		$tempDir = $this->config->getPath('cache/tmp/' . rand(1, 10000000));

		$locale = Locale::getInstance($this->request->gget('id'));

		$fileDir = $this->config->getPath('application/configuration/language/en');
		$files = $locale->translationManager()->getDefinitionFiles($fileDir);

		// prepare language files
		$translated = array();
		foreach ($files as $file)
		{
			$relPath = substr($file, strlen($fileDir) + 1);

			// get language default definitions
			$default = $locale->translationManager()->getFileDefs($relPath, true);
			if (!is_array($default))
			{
				$default = array();
			}

			// get translated definitions
			$transl = $locale->translationManager()->getCacheDefs($relPath, true);

			$transl = array_merge($default, $transl);

			$values = array();
			foreach($transl as $key => $value)
			{
				$values[] = $key . '=' . $value;
			}

			$path = $tempDir . '/' . $locale->getLocaleCode() . '/' . $relPath;

			if ($values)
			{
				if (!is_dir(dirname($path)))
				{
					mkdir(dirname($path), 0777, true);
				}

				file_put_contents($path, implode("\n", $values));
			}
		}

		// put the files in zip archive
		require_once($this->config->getPath('library/pclzip') . '/pclzip.lib.php');

		if (!is_dir($tempDir))
		{
			return new ActionRedirectResponse('backend.language', 'edit', array('id' => $locale->getLocaleCode()));
		}

		chdir($tempDir);
		$zip = dirname($tempDir) . '/temp_' . rand(1, 10000) . '.zip';
		$archive = new PclZip($zip);
		$archive->add($locale->getLocaleCode());

		// remove the temp directory
		$this->application->rmdir_recurse($tempDir);

		$response = new ObjectFileResponse(ObjectFile::getNewInstance('ObjectFile', $zip, 'LiveCart-' . $locale->getLocaleCode() . '.zip'));
		$response->deleteFileOnComplete();

		return $response;
	}

	/**
	 * Gets definitions from project files and updates them in database.
	 * @role language.update
	 * @return ActionResponse
	 */
	public function updateAction()
	{
		$enLocale = Locale::getInstance('en');
		$enLocale->translationManager()->updateDefinitions();

		return new ActionRedirectResponse($this->request->getControllerName(), 'index');
	}

	/**
	 * Displays definitions edit page.
	 * @return ActionResponse
	 * @role language
	 */
	public function editAction()
	{
		// preload current locale
		$this->locale;

		// get locale instance for the language being translated
		$editLocaleName = $this->request->gget('id');
		$editLocale = Locale::getInstance($editLocaleName);
		$editManager = $editLocale->translationManager();

		// get all English configuration files
		$enLocale = Locale::getInstance('en');
		$enManager = $enLocale->translationManager();

		$translated = array();
		$enDefs = array();

		foreach ($this->application->getConfigContainer()->getLanguageDirectories() as $fileDir)
		{
			$fileDir .= '/';
			$files = $enManager->getDefinitionFiles($fileDir . 'en');

			// get currently translated definitions
			foreach ($files as $file)
			{
				$relPath = $enManager->getRelativePathFromFullPath($file);

				// get default English definitions (to get all definition keys)
				$keys = $enManager->getFileDefs($file);
				$enDefs[$relPath] = $keys;

				foreach ($enDefs[$relPath] as $key => $value)
				{
					$enDefs[$relPath][$key] = htmlspecialchars($value);
				}

				foreach ($keys as $key => $value)
				{
					$keys[$key] = '';
				}

				// get language default definitions
				$default = $editManager->getFileDefs($relPath, true);

				if (!is_array($default))
				{
					$default = array();
				}

				// get translated definitions
				$transl = $editManager->getCacheDefs($relPath, true);

				// put all definitions together
				$translated[$relPath] = array_merge($keys, $default, $transl);
			}
		}

		$enDefs['Custom.lng'] = $enManager->getCacheDefs('Custom.lng', true);
		$translated['Custom.lng'] = $editManager->getCacheDefs('Custom.lng', true);

		if (!$enDefs['Custom.lng'])
		{
			unset($enDefs['Custom.lng']);
		}

		uksort($translated, array($this, 'sortTranslations'));

		if (!$this->config->get('SHOW_BACKEND_LANG_FILES'))
		{
			foreach ($enDefs as $key => $value)
			{
				if (substr($key, 0, 7) == 'backend' || 'Install.lng' == $key)
				{
					unset($enDefs[$key]);
				}
			}
		}

		$response = new ActionResponse();
		$response->set("id", $editLocaleName);
		$response->set("addForm", $this->buildAddPhraseForm());
		$response->set("translations", @json_encode($translated));
		$response->set("english", @json_encode($enDefs));
		$response->set("edit_language", $editLocale->info()->getLanguageName($editLocaleName));

		return $response;
	}

	private function sortTranslations($a, $b)
	{
		$dirA = substr($a, 0, strrpos($a, '/'));
		$dirB = substr($b, 0, strrpos($b, '/'));

		if ($dirA == $dirB)
		{
			return $a > $b ? -1 : 1;
		}
		else
		{
			return $dirA > $dirB ? -1 : 1;
		}
	}

	/**
	 * Saves translations
	 * @role language.update
	 * @return ActionRedirectResponse
	 */
	public function saveAction()
	{
		// preload current locale
		$this->locale;

		// get locale instance
		$localeCode = $this->request->gget("id");
		$editLocale = Locale::getInstance($localeCode);

		if (!$editLocale)
		{
		  	throw new ApplicationException('Locale "' . $localeCode .'" not found');
		}

		// get submited translation data
		$submitedLang = json_decode($this->request->gget("translations"), true);

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

		// check for custom phrases added to non-English locale
		if (isset($submitedLang['Custom.lng']))
		{
			$enLocale = Locale::getInstance('en');
			$custom = array_merge($editLocale->translationManager()->getCacheDefs('Custom.php', true), $enLocale->translationManager()->getCacheDefs('Custom.php', true));
			$enLocale->translationManager()->saveCacheData('en/Custom.php', $custom);
		}

		return new JSONResponse(false, 'success', $this->translate('_translations_were_successfully_saved'));
	}

	/**
	 * Displays main admin page.
	 * @return ActionResponse
	 * @role language
	 */
	public function indexAction()
	{
		// get all added languages
		$list = $this->getLanguages()->toArray();

		$response = new ActionResponse();
		$response->set("language", $this->request->gget("language"));
		$response->set("languageArray", json_encode($list));
		return $response;
	}

	/**
	 * @role language.create
	 */
	public function addFormAction()
	{
		// get all Locale languages
		$languagesSelect = $this->locale->info()->getAllLanguages();

		// get all system languages	and remove already added languages from Locale language list
		$list = $this->getLanguages()->toArray();
		foreach($list as $key => $value)
		{
			unset($languagesSelect[$value['ID']]);
		}

		// sort Locale language list
		asort($languagesSelect);

		$response = new ActionResponse();
		$response->set("languages_select", $languagesSelect);
		return $response;
	}

	/**
	 * Remove a language
	 * @role language.remove
	 * @return RawResponse
	 */
	public function deleteAction()
	{
		$langId = $this->request->gget('id');

		try
	  	{
			Language::deleteById($langId);
			return new JSONResponse(false, 'success');
		}
		catch (Exception $exc)
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_language'));
		}
	}

	/**
	 * Save language order
	 * @role language.sort
	 * @return RawResponse
	 */
	public function saveOrderAction()
	{
	  	$order = $this->request->gget('languageList');
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('Language', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('Language', $update);
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->gget('draggedId'));
		return $resp;
	}

	/**
	 * Sets default language.
	 * @role language.status
	 * @return ActionRedirectResponse
	 */
	public function setDefaultAction()
	{
		try
		{
			$r = ActiveRecord::getInstanceByID('Language', $this->request->gget('id'), true);
		}
		catch (ARNotFoundException $e)
		{
			return new RawResponse(0);
		}

		ActiveRecord::beginTransaction();

		$update = new ARUpdateFilter();
		$update->addModifier('isDefault', 0);
		ActiveRecord::updateRecordSet('Language', $update);

		$r->setAsDefault(true);
		$r->save();

		ActiveRecord::commit();

		return new ActionRedirectResponse('backend.language', 'index');
	}

	/**
	 * Sets if language is enabled
	 * @role language.status
	 * @return JSONResponse
	 */
	public function setEnabledAction()
	{
		$id = $this->request->gget('id');
		$lang = Language::getInstanceById($id);
		$lang->setAsEnabled($this->request->gget("status"));
		$lang->save();

		return new JSONResponse(array('language' => $lang->toArray()), 'success');
	}

	/**
	 * Add new language
	 * @role language.create
	 * @return JSONResponse
	 */
	public function addAction()
	{
		$lang = ActiveRecord::getNewInstance('Language');
		$lang->setID($this->request->gget("id"));
		$lang->save(ActiveRecord::PERFORM_INSERT);

		return new JSONResponse(array('language' => $lang->toArray()), 'success', $this->translate('_new_language_was_successfully_added'));
	}

	/**
	 * Displays system menu for switching active language
	 * @return ActionResponse
	 */
	public function langSwitchMenuAction()
	{
		$response = new ActionResponse();

		// get all system languages
		$list = $this->getLanguages(false)->toArray();

		foreach($list as $key => $value)
		{
			$list[$key]['name'] = $this->locale->info()->getOriginalLanguageName($value['ID']);
		}

		$response->set('returnRoute', $this->request->gget('returnRoute'));
		$response->set('languages', $list);
		$response->set('currentLanguage', $this->locale->getLocaleCode());
		return $response;
	}

	/**
	 * Changes active language
	 * @return RedirectResponse
	 * @todo Save language preference in User Settings, so the language would be selected automatically for subsequent visits
	 */
	public function changeLanguageAction()
	{
		$returnRoute = base64_decode($this->request->gget('returnRoute'));

		$lang = $this->request->gget('id');
		$langInst = Language::getInstanceById($lang);
		if ($langInst)
		{
			if ('/' == substr($returnRoute, 2, 1))
			{
			  	$returnRoute = $lang . '/' . substr($returnRoute, 3);
			}
			else
			{
			  	$returnRoute = $lang . '/' . $returnRoute;
			}
		}

		return new RedirectResponse($this->router->createUrlFromRoute($returnRoute));
	}

	/**
	 * Displays translation dialog menu for Live Translations
	 *
	 * @return ActionResponse
	 */
	public function translationDialogAction()
	{
	  	$id = $this->request->gget('id');
	  	$file = base64_decode($this->request->gget('file'));
	  	$translation = $this->locale->translationManager()->get($file, $id);

	  	$defaultTranslation = Locale::getInstance($this->application->getDefaultLanguageCode())->translationManager()->get($file, $id);

	  	$response = new ActionResponse();
	  	$response->set('id', $id);
	  	$response->set('file', $file);
	  	$response->set('translation', $translation);
	  	$response->set('defaultTranslation', $defaultTranslation);
	  	$response->set('language', Language::getInstanceByID($this->locale->getLocaleCode())->toArray());
	  	return $response;
	}

	/**
	 * Saves a single translation entry from Live Translations dialog menu
	 *
	 * @role language.update
	 * @return ActionResponse
	 */
	public function saveTranslationDialogAction()
	{
	  	$file = $this->request->gget('file');

	  	$this->locale->translationManager()->loadFile($file, true);
		$this->locale->translationManager()->updateValue($file, $this->request->gget('id'), $this->request->gget('translation'));

	  	return new RawResponse();
	}

	private function getLanguages($active = 0)
	{
	  	$filter = new ARSelectFilter();
	  	$filter->setOrder(new ARFieldHandle("Language", "position"), ARSelectFilter::ORDER_ASC);

		if ($active > 0)
		{
			$filter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isEnabled"), ($active == 1 ? 1 : 0)));
		}

		return ActiveRecord::getRecordSet("Language", $filter);
	}

	private function buildAddPhraseForm()
	{
		return new Form($this->buildAddPhraseValidator());
	}

	private function buildAddPhraseValidator()
	{
		$validator = $this->getValidator("addLangPhrase", $this->request);
		$validator->addCheck("key", new IsNotEmptyCheck($this->translate("_phrase_key_empty")));
		$validator->addCheck("value", new IsNotEmptyCheck($this->translate("_phrase_value_empty")));
		$validator->addFilter("key", new RegexFilter('[^_a-zA-Z0-9]'));

		return $validator;
	}
}

?>
