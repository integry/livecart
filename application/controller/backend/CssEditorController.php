<?php


/**
 * CSS file modification
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role template
 */
class CssEditorController extends StoreManagementController
{
	public function indexAction()
	{
		$themes = $this->application->getRenderer()->getThemeList();

		$files = array();
		foreach (array_merge(array(''), $themes, array('email')) as $theme)
		{
			$css = new EditedCssFile($theme);
			$arr = $css->toArray();
			$files[$arr['id']] = $arr;
		}

		return new ActionResponse('categories', json_encode($files));
	}

	public function editAction()
	{
		$css = new EditedCssFile($this->request->get('file'));

		$response = new ActionResponse();
		$tabid = $this->getRequest()->get('tabid');
		$response->set('tabid', $this->getRequest()->get('tabid'));
		$response->set('id', $css->getFileName());
		$response->set('file', $css->getFileName());
		$response->set('form', $this->getForm($css));
		$response->set('code', base64_encode($css->getCode()));
		$response->set('template', $css->toArray());
		if ($tabid == '')
		{
			// client side does not do tab handling by itself (eg. opened in theme editor tab)
			$response->set('noTabHandling', true);

			// give css file filename part as tabid, because client side can instantiate more than one editors.
			$response->set('tabid', str_replace('.css','',$css->getFileName()));
		}
		return $response;
	}

	/**
	 * @role save
	 */
	public function saveAction()
	{
		$code = $this->request->get('code');

		$css = new EditedCssFile($this->request->get('file'));

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

	public function emptyPageAction()
	{
		return new ActionResponse();
	}

	private function getForm(EditedCssFile $css)
	{
		$form = new Form($this->getValidator('cssFile', $this->request));
		$form->setData($css->toArray());
		$form->set('code', '');
		$form->set('fileName', $css->getFileName());
		$form->set('file', $css->getTheme());
		return $form;
	}

	public function templateDataAction()
	{
		$request = $this->getRequest();
		$version = $request->get('version');
		$css = new EditedCssFile($request->get('file'),$version);
		return new JSONResponse($css->toArray());
	}

}

?>