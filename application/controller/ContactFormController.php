<?php

class ContactFormController extends FrontendController
{
	public function indexAction()
	{
		$this->addBreadCrumb($this->translate('_contact_us'), $this->router->createUrl(array('controller' => 'contactForm')));
		return new ActionResponse('form', $this->buildForm());
	}

	public function sendAction()
	{
		if (!$this->buildValidator()->isValid())
		{
			return new ActionRedirectResponse('contactForm', 'index');
		}

		$email = new Email($this->application);
		$email->setTemplate('contactForm/contactForm');
		$email->setFrom($this->request->get('email'), $this->request->get('name'));
		$email->setTo($this->config->get('NOTIFICATION_EMAIL'), $this->config->get('STORE_NAME'));
		$email->set('message', $this->request->get('msg'));
		$email->send();

		return new ActionRedirectResponse('contactForm', 'sent');
	}

	public function sentAction()
	{
		$this->addBreadCrumb($this->translate('_contact_us'), $this->router->createUrl(array('controller' => 'contactForm')));
		$this->addBreadCrumb($this->translate('_form_sent'), '');

		return new ActionResponse();
	}

	public function buildFormAction()
	{
		return new Form($this->buildValidator());
	}

	public function buildValidatorAction(Request $request = null)
	{
		$request = $request ? $request : $this->request;

		$validator = $this->getValidator("contactForm", $request);
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name')));
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email')));
		$validator->addCheck('msg', new IsNotEmptyCheck($this->translate('_err_message')));
		$validator->addCheck('surname', new MaxLengthCheck('Please do not enter anything here', 0));

		return $validator;
	}
}

?>