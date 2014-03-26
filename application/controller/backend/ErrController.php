<?php


/**
 * Backend error pages
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class ErrController extends ControllerBackend
{
	public function indexAction()
	{
throw new Exception('whats up?');
var_dump('error', $this->request->get('id'));exit;
		$this->set('id', $this->request->get('id'));
		$this->set('ajax', $this->request->get('ajax'));
		//$this->set('description', HTTPStatusException::getCodeMeaning($this->request->get('id')));

	}

	public function redirectAction()
	{
		$id = $this->request->get('id');
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
				}
			case 403:
			case 404:
				$params['id'] = $id;
				return new ActionRedirectResponse('backend.err', 'index', $params);
			default:
			   	return new RawResponse('error ' . $this->request->get('id'));
		}
	}
}

?>
