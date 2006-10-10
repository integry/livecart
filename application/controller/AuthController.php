<?php

ClassLoader::import("framework.controller.Controller");

/**
 * Authorization controller
 *
 * @package application.controller
 * @author Saulius Rupainis <saulius@integry.net>
 */
class AuthController extends Controller 
{
	/**
	 * Action for displaying login form
	 *
	 * @return ActionResponse
	 */
	public function index() 
	{
		$response = new ActionResponse();
		
		$loginForm = $this->createLoginForm();
		$displayHandler = $loginForm->createDisplayer();
		$response->setValue("handler", $displayHandler);
		
		return $response;
	}
	
	/**
	 * Creates a login form instance (helper method)
	 *
	 * @return Form
	 */
	private function createLoginForm() 
	{
		ClassLoader::import("library.form.Form");
		$form = new Form("loginFrm");
		
		$passField = new FormTextField();
		$passField->SetName("password");
		$passField->SetTitle("Password");
		//$passField->AddValidator(new FormValRequire("You forgot to enter password!"));

		$emailField = new FormTextField();
		$emailField->SetName("email");
		$emailField->SetTitle("E-mail address");
		//$emailField->AddValidator(new FormValEmail("E-mail you have entered is invalid"));
		
		$rememberPassField = new FormCheckboxField();
		$rememberPassField->SetName("remember");
		$rememberPassField->SetTitle("Remember me on this computer");
			
		$form->AddField($emailField);
		$form->AddField($passField);
		$form->AddField($rememberPassField);
		
		return $form;
	}
	
	/**
	 * Action for authorizing a user and redirectiong it to a proper action 
	 * (if login fails it will be a login form)
	 *
	 * @return ActionRedirectResponse
	 */
	public function login() 
	{
		// pseudo login
		ClassLoader::import("application.model.user.User");
		
		@session_start();
		$user = User::getInstanceByID(1, User::LOAD_DATA);
		$_SESSION['user'] = serialize($user);
		
		return new ActionRedirectResponse("backend.index", "index");
	}
	
	/**
	 * Destroys user related session
	 * 
	 * @return ActionRedirectResponse
	 */
	public function logout()
	{
		session_destroy();
		unset($_SESSION);
		return new ActionRedirectResponse("index", "index");
	}
	
	public function test() 
	{
		//echo "<pre>"; print_r($_SERVER); echo "</pre>";
		return new ActionResponse();
	}
}

?>