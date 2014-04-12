<?php


use Phalcon\Validation\Validator;
use heysuccess\application\model\SuccessStory;

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class SimplesettingsController extends ControllerBackend// implements MassActionInterface
{
    public function indexAction()
	{
		$this->set('config', $this->config->toArray());
	}

	public function saveAction()
	{
		if (!$this->request->getJsonRawBody())
		{
			return;
		}
		
		$this->config->setAll($this->request->getJsonRawBody());
		$this->config->save();
		echo json_encode(array('success' => true));
	}
}

?>
