<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.template.CssFile');

/**
 * CSS file modification
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role template
 */
class CssEditorController extends StoreManagementController
{
	public function index()
	{
		$themes = $this->application->getRenderer()->getThemeList();

		$files = array();
		foreach (array_merge(array(''), $themes) as $theme)
		{
			$css = new CssFile($theme);
			$arr = $css->toArray();
			$files[$arr['id']] = $arr;
		}

		return new ActionResponse('categories', json_encode($files));
	}

	public function edit()
	{
		$css = new CssFile($this->request->get('file'));

		$response = new ActionResponse();
	  	$response->set('id', $css->getFileName());
	  	$response->set('file', $css->getFileName());
	  	$response->set('form', $this->getForm($css));
	  	$response->set('code', base64_encode($css->getCode()));
	  	$response->set('template', $css->toArray());
		return $response;
	}

	/**
	 * @role save
	 */
	public function save()
	{
		$code = $this->request->get('code');

		$css = new CssFile($this->request->get('file'));

		$css->setCode($code);
		$res = $css->save();

		if($res)
		{
			return new JSONResponse(array('css' => $css->toArray()), 'success', $this->translate('_css_file_has_been_successfully_updated'));
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_update_css_file'));
		}
	}

	public function emptyPage()
	{
		return new ActionResponse();
	}

	private function getForm(CssFile $css)
	{
		ClassLoader::import("framework.request.validator.Form");
		$form = new Form(new RequestValidator('cssFile', $this->request));
		$form->setData($css->toArray());
		$form->set('code', '');
		$form->set('fileName', $css->getFileName());
		$form->set('file', $css->getTheme());
		return $form;
	}
}

?>