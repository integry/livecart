<?php

ClassLoader::import("library.*");
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.ShippingClass");

/**
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role delivery
 */
class ShippingClassController extends StoreManagementController
{
	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function index()
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

	public function edit()
	{
		$class = ShippingClass::getInstanceByID((int)$this->request->get('id'), true);

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
	public function delete()
	{
		$service = ShippingClass::getInstanceByID((int)$this->request->get('id'));
		$service->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function update()
	{
		$class = ShippingClass::getInstanceByID((int)$this->request->get('id'));

		return $this->saveClass($class);
	}

	/**
	 * @role create
	 */
	public function create()
	{
		$class = ShippingClass::getNewInstance($this->request->get('name'));
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
	public function createClassFormValidator(ShippingClass $class)
	{
		$validator = $this->getValidator("classForm_" . $class->isExistingRecord() ? $class->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_the_name_should_not_be_empty")));

		return $validator;
	}

	/**
	 * @role update
	 */
	public function sort()
	{
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
		   $class = ShippingClass::getInstanceByID((int)$key);
		   $class->position->set((int)$position);
		   $class->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>