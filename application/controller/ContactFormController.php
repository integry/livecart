<?php

class ContactFormController extends FrontendController
{
	public function indexAction()
	{
		$this->addBreadCrumb($this->translate('_contact_us'), $this->router->createUrl(array('controller' => 'contactForm')));
		$this->set('form', $this->buildForm());
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


	}

	public function buildFormAction()
	{
		return new Form($this->buildValidator());
	}

	public function buildValidatorAction(\Phalcon\Http\Request $request = null)
	{
		$request = $request ? $request : $this->request;

		$validator = $this->getValidator("contactForm", $request);
		$validator->add('name', new Validator\PresenceOf(array('message' => $this->translate('_err_name'))));
		$validator->add('email', new Validator\PresenceOf(array('message' => $this->translate('_err_email'))));
		$validator->add('msg', new Validator\PresenceOf(array('message' => $this->translate('_err_message'))));
		$validator->add('surname', new MaxLengthCheck('Please do not enter anything here', 0));

		return $validator;
	}
}

?>