<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.template.Theme');

/**
 * Manage design themes
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class ThemeController extends StoreManagementController
{
	public function index()
	{
		$themes = array_merge(array('barebone' => 'barebone'), array_diff($this->application->getRenderer()->getThemeList(), array('barebone')));

		$response = new ActionResponse();
		$response->set('themes', json_encode($themes));
		$response->set('addForm', $this->buildForm());
		return $response;
	}

	public function edit()
	{
		$theme = new Theme($this->request->get('id'), $this->application);
		$arr = $theme->toArray();

		$form = $this->buildSettingsForm();
		$form->setData($arr);

		foreach ($theme->getParentThemes() as $key => $parent)
		{
			$form->set('parent_' . ($key + 1), $parent);
		}

		$response = new ActionResponse();
		$response->set('theme', $arr);
		$response->set('form', $form);
		$response->set('themes', $this->application->getRenderer()->getThemeList());
		return $response;
	}

	public function saveSettings()
	{
		$themes = array();
		for ($k = 1; $k <= 3; $k++)
		{
			if ($theme = $this->request->get('parent_' . $k))
			{
				$themes[] = $theme;
			}
		}

		$inst = new Theme($this->request->get('id'), $this->application);
		$inst->setParentThemes($themes);
		$inst->saveConfig();

		return new JSONResponse(false, 'success', $this->translate('_theme_saved'));
	}

	public function add()
	{
		$inst = new Theme($this->request->get('name'), $this->application);

		$errors = array();
		$validator = $this->buildValidator();
		$validator->isValid();

		if ($inst->isExistingTheme())
		{
			$validator->triggerError('name', $this->translate('_err_theme_exists'));
		}

		if ($errors = $validator->getErrorList())
		{
			return new JSONResponse(array('errors' => $errors));
		}
		else
		{
			$inst->create();
			return new JSONResponse($inst->toArray(), 'success', $this->translate('_theme_created'));
		}
	}

	public function delete()
	{
		$inst = new Theme($this->request->get('id'), $this->application);
		if ($inst->isCoreTheme())
		{
			return new JSONResponse($inst->toArray(), 'failure', $this->translate('_err_cannot_delete_core_theme'));
		}
		else
		{
			$inst->delete();
			return new JSONResponse($inst->toArray(), 'success', $this->maketext('_theme_deleted', array($inst->getName())));
		}
	}

	public function colors()
	{
		$inst = new Theme($this->request->get('id'), $this->application);

		$measurements = array('', 'auto', 'px', '%', 'em');
		$measurements = array_combine($measurements, $measurements);

		$response = new ActionResponse();
		$response->set('config', $this->getParsedStyleConfig($inst));
		$response->set('form', $this->buildColorsForm($inst));
		$response->set('measurements', $measurements);
		return $response;
	}

	private function getParsedStyleConfig(Theme $theme)
	{
		$themeName = $theme->getName();
		$conf = array();
		foreach ($theme->getStyleConfig() as $name => $sectionData)
		{
			$section = array();
			$open = true;
			if ('-' == $name[0])
			{
				$open = false;
				$name = substr($name, 1);
			}

			$section['name'] = $this->translate($name);
			$section['open'] = $open;

			$properties = array();
			foreach ($sectionData as $name => $value)
			{
				$property = array('var' => $name, 'name' => $this->translate($name), 'id' => $themeName . '_' . $name);
				list($property['type'], $property['selector']) = explode(' _ ', $value, 2);
				$properties[] = $property;
			}

			$section['properties'] = $properties;

			$conf[] = $section;
		}

		return $conf;
	}

	/**
	 * @return RequestValidator
	 */
	private function buildValidator()
	{
		$validator = $this->getValidator("theme", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate('_err_theme_name_empty')));
		$validator->addFilter("name", new RegexFilter('[^_-a-zA-Z0-9]'));

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildColorsForm(Theme $theme)
	{
		return new Form($this->buildColorsValidator($theme));
	}

	/**
	 * @return RequestValidator
	 */
	private function buildColorsValidator($theme)
	{
		$validator = $this->getValidator("themeColors", $this->request);

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildSettingsForm()
	{
		return new Form($this->getValidator("foo", $this->request));
	}
}

?>