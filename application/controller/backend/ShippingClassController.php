<?php


/**
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role delivery
 */
class ShippingClassController extends StoreManagementController
{
	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function indexAction()
	{
		$response = new ActionResponse();

		$classesForms = array();
		$classes = array();
		foreach(ShippingClass::getAllClasses() as $class)
		{
			$classes[] = $class->toArray();
			$classesForms[] = $this->createClassForm($class);
		}

		$response->set("classesForms", $classesForms);
		$response->set("classes", $classes);

		$newClass = ShippingClass::getNewInstance('');
		$response->set("newClassForm", $this->createClassForm($newClass));
		$response->set("newClass", $newClass->toArray());

		return $response;
	}

	public function editAction()
	{
		$class = ShippingClass::getInstanceByID((int)$this->request->gget('id'), true);

		$form = $this->createClassForm($class);
		$form->setData($class->toArray());

		$response = new ActionResponse();
		$response->set('class', $class->toArray());
		$response->set('classForm', $form);

		return $response;
	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		$service = ShippingClass::getInstanceByID((int)$this->request->gget('id'));
		$service->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$class = ShippingClass::getInstanceByID((int)$this->request->gget('id'));

		return $this->saveClass($class);
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$class = ShippingClass::getNewInstance($this->request->gget('name'));
		$class->position->set(1000);

		return $this->saveClass($class);
	}

	private function saveClass(ShippingClass $class)
	{
		$validator = $this->createClassFormValidator($class);

		if($validator->isValid())
		{
			$class->setValueArrayByLang(array('name'), $this->application->getDefaultLanguageCode(), $this->application->getLanguageArray(true, false), $this->request);

			$class->save();

			return new JSONResponse(array('class' => $class->toArray()), 'success');
		}
		else
		{

			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_class_entry'));
		}
	}

	/**
	 * @return Form
	 */
	private function createClassForm(ShippingClass $class)
	{
		$form = new Form($this->createClassFormValidator($class));

		$form->setData($class->toArray());

		return $form;
	}

	/**
	 * @return RequestValidator
	 */
	public function createClassFormValidatorAction(ShippingClass $class)
	{
		$validator = $this->getValidator("classForm_" . $class->isExistingRecord() ? $class->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_the_name_should_not_be_empty")));

		return $validator;
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $key)
		{
		   $class = ShippingClass::getInstanceByID((int)$key);
		   $class->position->set((int)$position);
		   $class->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>