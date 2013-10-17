<?php


/**
 * Handles dynamic interface customizations
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role customize
 */
class CustomizeController extends StoreManagementController
{
	public function indexAction()
	{

		$this->set('isCustomizationModeEnabled', $this->application->isCustomizationMode());
		$this->set('isTranslationModeEnabled', $this->application->isTranslationMode());
	}

	public function translationModeAction()
	{
	  	if ($this->application->isTranslationMode())
	  	{
			$this->session->unsetValue('translationMode');
		}
		else
		{
			$this->session->set('translationMode', true);
		}

		return $this->response->redirect('backend/customize/index');
	}

	public function modeAction()
	{

	  	if (($this->application->isCustomizationMode() && !$this->request->isValueSet('mode')) || ('exit' == $this->request->get('mode')))
	  	{
			$this->session->unsetValue('customizationMode');
		}
		else
		{
			$this->session->set('customizationMode', true);
			$this->session->set('customizationModeType', $this->request->get('mode', 'template'));
		}

		if (!$this->request->isValueSet('return'))
		{
			return $this->response->redirect('backend/customize/index');
		}
		else
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
		}
	}

	public function saveCssAction()
	{
		$params = json_decode($this->request->get('result'), true);

		$theme = $params['theme'];

		if (!$theme)
		{
			$theme = 'barebone';
		}

		// save custom CSS
		$css = new CssFile('upload/css/' . $theme . '.css');
		$css->setSource($params['css']);
		$css->save();

		// deleted rules
		foreach ($params['deletedRules'] as $file => $selectors)
		{
			$css = CssFile::getInstanceFromUrl($file, $theme);
			foreach ($selectors as $selector)
			{
				$css->deleteSelector($selector);
			}

			$css->save();
		}

		// deleted properties
		foreach ($params['deletedProperties'] as $file => $selectors)
		{
			$css = CssFile::getInstanceFromUrl($file, $theme);
			foreach ($selectors as $selector => $properties)
			{
				foreach ($properties as $property => $value)
				{
					$css->deleteProperty($selector, $property);
				}
			}

			$css->save();
		}
	}

	public function changeThemeAction()
	{
		$this->session->set('customizationTheme', $this->getRequest()->get('theme'));
		return new JSONResponse(null, "success");
	}
}

?>