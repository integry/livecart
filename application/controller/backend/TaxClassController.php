<?php


/**
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role delivery
 */
class TaxClassController extends StoreManagementController
{
	/**
	 * List all system currencies
	 */
	public function indexAction()
	{


		$classesForms = array();
		$classes = array();
		foreach(TaxClass::getAllClasses() as $class)
		{
			$classes[] = $class->toArray();
			$classesForms[] = $this->createClassForm($class);
		}

		$this->set("classesForms", $classesForms);
		$this->set("classes", $classes);

		$newClass = TaxClass::getNewInstance('');
		$this->set("newClassForm", $this->createClassForm($newClass));
		$this->set("newClass", $newClass->toArray());

	}

	public function editAction()
	{
		$class = TaxClass::getInstanceByID((int)$this->request->get('id'), true);

		$form = $this->createClassForm($class);
		$form->setData($class->toArray());


		$this->set('class', $class->toArray());
		$this->set('classForm', $form);

	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		$service = TaxClass::getInstanceByID((int)$this->request->get('id'));
		$service->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$class = TaxClass::getInstanceByID((int)$this->request->get('id'));

		return $this->saveClass($class);
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$class = TaxClass::getNewInstance($this->request->get('name'));
		$class->position->set(1000);

		return $this->saveClass($class);
	}

	private function saveClass(TaxClass $class)
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
	private function createClassForm(TaxClass $class)
	{
		$form = new Form($this->createClassFormValidator($class));

		$form->setData($class->toArray());

		return $form;
	}

	/**
	 * @return RequestValidator
	 */
	public function createClassFormValidatorAction(TaxClass $class)
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
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
		   $class = TaxClass::getInstanceByID((int)$key);
		   $class->position->set((int)$position);
		   $class->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>