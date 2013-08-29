<?php


/**
 * Application settings management
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role settings
 */
class SettingsController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function indexAction()
	{
		$this->config->updateSettings();
		$tree = array('children' => $this->config->getTree());

		if (file_exists($this->getPrivateLabelFile()))
		{
			unset($tree['49-private-label']);
		}

		$response = new ActionResponse('categories', json_encode($tree));
		$response->set('settings', json_encode($this->config->toArray()));

		$defLang = $this->application->getDefaultLanguageCode();
		$languages = $this->application->getLanguageArray(LiveCart::INCLUDE_DEFAULT);

		$values = $layouts = array();

		foreach ($this->config->getSectionList() as $section)
		{
			$values = array_merge($values, $this->config->getSettingsBySection($section));
			$layouts[$section] = $this->config->getSectionLayout($section);
		}

		$form = $this->getForm($values, $this->getValidationRules($values));
		$types = $multiLingualValues = array();

		foreach ($values as $key => $value)
		{
			if (($this->config->isMultiLingual($key) && ('string' == $value['type'])) || 'longtext' == $value['type'])
			{
				foreach ($languages as $lang)
				{
					$form->set($key . ($lang != $defLang ? '_' . $lang : ''), $this->config->getValueByLang($key, $lang));
				}

				$multiLingualValues[$key] = true;
			}
			else
			{
				if ($this->config->isValueSet($key))
				{
					$form->set($key, $this->config->get($key));
				}
			}

			$types[$key] = $value['type'];
		}

		$response->set('form', $form);
		$response->set('values', $values);
		$response->set('types', $types);
		$response->set('layouts', $layouts);
		$response->set('multiLingualValues', $multiLingualValues);
		return $response;
	}

	/**
	 * @role update
	 */
	public function saveAction()
	{
		$values = $layouts = array();

		foreach ($this->config->getSectionList() as $section)
		{
			$values = array_merge($values, $this->config->getSettingsBySection($section));
		}

		$validation = $this->getValidationRules($values);
		$validator = $this->buildValidator($values, $validation);

		if (!$validator->isValid())
		{
		  	return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_note_save_section'));
		}
		else
		{
			$languages = $this->application->getLanguageArray();
			$defLang = $this->application->getDefaultLanguageCode();

			$this->config->setAutoSave(false);
			$data = array();
			foreach ($values as $key => $value)
			{
				if (($this->config->isMultiLingual($key) && 'string' == $value['type']) || 'longtext' == $value['type'])
				{
					$this->config->setValueByLang($key, $defLang, $this->request->get($key));
					foreach ($languages as $lang)
					{
						$this->config->setValueByLang($key, $lang, $this->request->get($key . '_' . $lang));
					}
				}
				else if ('image' == $value['type'])
				{
					$file = 'upload/' . $key . '-' . $_FILES[$key]['name'];
					$path = $this->config->getPath('public/') . $file;
					if (@move_uploaded_file($_FILES[$key]['tmp_name'], $path))
					{
						$this->config->set($key, $file);
					}
				}
				else if ('bool' == $value['type'])
				{
					$this->config->set($key, $this->request->get($key, null, 0));
				}
				else
				{
					$this->config->set($key, $this->request->get($key));
				}

				$data[$key] = $this->config->get($key);
			}

			$this->config->save();
			$this->config->setAutoSave(true);

						SearchableConfigurationIndexing::buildIndexIfNeeded();
//			$sc = new SearchableConfigurationIndexing($this->config, $this->application);
//			$sc->buildIndex($this->request->get('id'));

			return new JSONResponse($data, 'success', $this->translate('_save_conf'));
		}
	}

	/**
	 * @role update
	 */
	public function disablePrivateLabelAction()
	{
		file_put_contents($this->getPrivateLabelFile(), '');
	}

	private function getPrivateLabelFile()
	{
		return $this->config->getPath('storage/') . 'privateLabelDisabled';
	}

	private function getValidationRules(&$values)
	{
		// look for validation rules
		$validation = array();
		foreach ($values as $key => $value)
		{
			if (substr($key, 0, 9) == 'validate_')
			{
				// add quotes, so that json_decode wouldn't return NULLs
				$value = str_replace(array('<', '>'), array('{', '}'), $value['value']);
				$value = preg_replace('/[a-zA-Z0-9_]{1,}/', '"\\0"', $value);

				$validation[substr($key, 9)] = json_decode('{' . $value . '}', true);
				unset($values[$key]);
			}
		}

		return $validation;
	}

	private function getForm($settings, $validation)
	{
		$form = new Form($this->buildValidator($settings, $validation));

		// set multi-select values
		foreach ($settings as $key => $value)
		{
			if ($this->config->isValueSet($value['title']))
			{
				if ('multi' == $value['extra'])
				{
					$values = $this->config->get($value['title']);

					if (is_array($values))
					{
						foreach ($values as $key => $val)
						{
							$form->set($value['title'] . '[' . $key . ']', 1);
						}
					}
				}
			}
		}

		return $form;
	}

	private function buildValidator($settings, $validation)
	{
		$val = $this->getValidator('settings', $this->request);
		foreach ($settings as $key => $value)
		{
			if (('num' == $value['type']) || ('float' == $value['type']))
			{
				$val->addCheck($key, new IsNumericCheck($this->translate('_err_numeric')));
				$val->addCheck($key, new MinValueCheck($this->translate('_err_negative'), 0));
				$val->addFilter($key, new NumericFilter());
			}

			if ('num' == $value['type'])
			{
				$val->addFilter($key, new RegexFilter('\.'));
			}
		}

		// apply custom validation rules
		foreach ($validation as $field => $validators)
		{
			foreach ($validators as $validator => $constraint)
			{
				foreach ($constraint as $c => $error)
				{
					$val->addCheck($field, new $validator($this->translate($error), $c));
				}
			}
		}

		return $val;
	}
}

?>