<?php


/**
 * Backend error pages
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class ErrController extends BackendController
{
	public function indexAction()
	{
		$response = new ActionResponse();
		$response->set('id', $this->request->gget('id'));
		$response->set('ajax', $this->request->gget('ajax'));
		$response->set('description', HTTPStatusException::getCodeMeaning($this->request->gget('id')));

		return $response;
	}

	public function redirectAction()
	{
		$id = $this->request->gget('id');
		$params = array();

		if($this->isAjax())
		{
			$params['query'] = array('ajax' => 1);
		}

		switch($id)
		{
			case 401:
				$response = new ActionRedirectResponse('backend.session', 'index', $params);
				if ($this->isAjax())
				{
					return new JSONResponse(array('__redirect' => $response->getUrl($this->router)));
				}
				else
				{
					return $response;
				}
			case 403:
			case 404:
				$params['id'] = $id;
				return new ActionRedirectResponse('backend.err', 'index', $params);
			default:
			   	return new RawResponse('error ' . $this->request->gget('id'));
		}
	}
}

?>