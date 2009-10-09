<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.order.OrderedItem');
ClassLoader::import('application.model.order.OrderNote');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.delivery.State');
ClassLoader::import('application.model.user.*');

/**
 *  Handles user account related logic
 *
 *  @author Integry Systems
 *  @package application.controller
 */
class UserController extends FrontendController
{
 	const PASSWORD_MIN_LENGTH = 5;

 	const COUNT_RECENT_FILES = 5;

 	public function init()
 	{
 		parent::init();

 		if ($this->user->getID())
 		{
 			$this->user->load();
		}
	}

	private function addAccountBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_your_account'), $this->router->createUrl(array('controller' => 'user'), true));
	}

	private function addAddressBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_manage_addresses'), $this->router->createUrl(array('controller' => 'user', 'action' => 'addresses'), true));
	}

	private function addFilesBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_your_files'), $this->router->createUrl(array('controller' => 'user', 'action' => 'files'), true));
	}

	/**
	 *	@role login
	 */
	public function index()
	{
		$this->addAccountBreadcrumb();

		// get recent orders
		$f = new ARSelectFilter();
		$f->setLimit($this->config->get('USER_COUNT_RECENT_ORDERS'));
		$orders = $this->loadOrders($f);

		$orderArray = $this->getOrderArray($orders);

		// get downloadable items
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->setLimit(self::COUNT_RECENT_FILES);

		$response = new ActionResponse();

		$response->set('orders', $orderArray);
		$response->set('files', $this->loadDownloadableItems(new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()))));

		// get unread messages
		ClassLoader::import('application.model.order.OrderNote');
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('OrderNote', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderNote', 'isAdmin'), 1));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderNote', 'isRead'), 0));
		$f->setOrder(new ARFieldHandle('OrderNote', 'ID'), 'DESC');
		$response->set('notes', ActiveRecordModel::getRecordSetArray('OrderNote', $f, array('User')));

		// feedback/confirmation message that was stored in session by some other action
		$response->set('userConfirm', $this->session->pullValue('userConfirm'));

		return $response;
	}

	/**
	 *	@role login
	 */
	public function orders()
	{
		$this->addAccountBreadcrumb();
		$this->addBreadCrumb($this->translate('_your_orders'), '');

		$perPage = $this->config->get('USER_ORDERS_PER_PAGE');
		if (!$perPage)
		{
			$perPage = 100000;
		}
		$page = $this->request->get('id', 1);

		$f = new ARSelectFilter();
		$f->setLimit($perPage, ($page - 1) * $perPage);
		$orders = $this->loadOrders($f);

		$orderArray = $this->getOrderArray($orders);

		$response = new ActionResponse();
		$response->set('from', ($perPage * ($page - 1)) + 1);
		$response->set('to', min($perPage * $page, $orders->getTotalRecordCount()));
		$response->set('count', $orders->getTotalRecordCount());
		$response->set('currentPage', $page);
		$response->set('perPage', $perPage);
		$response->set('user', $this->user->toArray());
		$response->set('orders', $orderArray);
		return $response;
	}

	private function loadOrders(ARSelectFilter $f)
	{
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCompleted'), 'DESC');

		$orders = ActiveRecordModel::getRecordSet('CustomerOrder', $f);

		foreach ($orders as $order)
		{
			$order->loadAll();
		}

		return $orders;
	}

	private function getOrderArray(ARSet $orders)
	{
		$orderArray = $orders->toArray();

		$ids = array();
		foreach ($orderArray as $key => $order)
		{
			$ids[$order['ID']] = $key;
		}

		ClassLoader::import('application.model.order.OrderNote');

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('OrderNote', 'orderID'), empty($ids) ? array(-1) : array_keys($ids)));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderNote', 'isAdmin'), 1));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderNote', 'isRead'), 0));
		$f->setGrouping(new ARFieldHandle('OrderNote', 'orderID'));

	  	$query = new ARSelectQueryBuilder();
	  	$query->setFilter($f);
	  	$query->includeTable('OrderNote');
		$query->removeFieldList();
		$query->addField('COUNT(*)', null, 'cnt');
		$query->addField('orderID');

		foreach (ActiveRecordModel::getDataBySQL($query->getPreparedStatement(ActiveRecord::getDBConnection())) as $res)
		{
			$orderArray[$ids[$res['orderID']]]['unreadMessageCount'] = $res['cnt'];
		}

		return $orderArray;
	}

	/**
	 *	@role login
	 */
	public function files()
	{
		$this->addAccountBreadcrumb();
		$this->addFilesBreadcrumb();

		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('files', $this->loadDownloadableItems(new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()))));
		return $response;
	}

	/**
	 *	@role login
	 */
	public function item()
	{
		$item = ActiveRecordModel::getInstanceById('OrderedItem', $this->request->get('id'), ActiveRecordModel::LOAD_DATA, OrderedItem::LOAD_REFERENCES);
		$item->customerOrder->get()->loadAll();
		$item->loadOptions();
		$subItems = $item->getSubitems();
		$item = $item->toArray();

		$this->addAccountBreadcrumb();
		$this->addFilesBreadcrumb();
		$this->addBreadCrumb(isset($item['Product']['name_lang']) ? $item['Product']['name_lang'] : $item['Product']['Parent']['name_lang'], '');

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('OrderedItem', 'ID'), $item['ID']));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));

		$fileArray = $this->loadDownloadableItems($f);
		if (!$fileArray && !$subItems)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('files', $fileArray);
		$response->set('item', $item);

		if ($subItems)
		{
			$response->set('subItems', $subItems->toArray());
		}

		return $response;
	}

	private function loadDownloadableItems(ARSelectFilter $f)
	{
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isPaid'), true));
		//$f->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'type'), Product::TYPE_DOWNLOADABLE));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'ID'), 'DESC');

		$downloadable = ActiveRecordModel::getRecordSet('OrderedItem', $f, array('Product', 'CustomerOrder'));
		$fileArray = array();
		foreach ($downloadable as &$item)
		{
			$files = $item->getProduct()->getFiles();
			$itemFiles = array();
			foreach ($files as $file)
			{
				if ($item->isDownloadable($file))
				{
					$itemFiles[] = $file->toArray();
				}
			}

			if (!$itemFiles)
			{
				continue;
			}

			$array = $item->toArray();
			$array['Product']['Files'] = ProductFileGroup::mergeGroupsWithFields($item->getProduct()->getFileGroups()->toArray(), $itemFiles);

			foreach ($array['Product']['Files'] as $key => $file)
			{
				if (!isset($file['ID']))
				{
					unset($array['Product']['Files'][$key]);
				}
			}

			$fileArray[] = $array;
		}

		return $fileArray;
	}

	/**
	 *	@role login
	 */
	public function changePassword()
	{
		$this->addAccountBreadcrumb();

		$this->addBreadCrumb($this->translate('_change_pass'), '');
		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('form', $this->buildPasswordChangeForm());
		return $response;
	}

	/**
	 *	@role login
	 */
	public function doChangePassword()
	{
		if (!$this->buildPasswordChangeValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'changePassword');
		}

		$this->user->setPassword($this->request->get('password'));
		$this->user->save();

		$this->session->set('userConfirm', $this->translate('_confirm_password_change'));

		return new ActionRedirectResponse('user', 'index');
	}

	/**
	 *	@role login
	 */
	public function changeEmail()
	{
		$this->addAccountBreadcrumb();

		$this->addBreadCrumb($this->translate('_change_email'), '');
		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('form', $this->buildEmailChangeForm());
		return $response;
	}

	/**
	 *	@role login
	 */
	public function doChangeEmail()
	{
		if (!$this->buildEmailChangeValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'changeEmail');
		}

		$this->user->email->set($this->request->get('email'));
		$this->user->save();

		$this->session->set('userConfirm', $this->translate('_confirm_email_change'));

		return new ActionRedirectResponse('user', 'index');
	}

	/**
	 *	@role login
	 */
	public function personal()
	{
		$this->addAccountBreadcrumb();
		$this->addBreadcrumb($this->translate('_personal_info'), '');

		$form = $this->buildPersonalDataForm($this->user);
		$response = new ActionResponse('form', $form);
		$this->user->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	/**
	 *	@role login
	 */
	public function savePersonal()
	{
		$validator = $this->buildPersonalDataValidator($this->user);
		if (!$validator->isValid())
		{
			return new ActionRedirectResponse('user', 'personal');
		}

		$this->user->loadRequestData($this->request, array('firstName', 'lastName', 'companyName'));
		$this->user->save();

		$this->setMessage($this->translate('_personal_data_saved'));

		return new ActionRedirectResponse('user', 'index');
	}

	/**
	 *	@role login
	 */
	public function addresses()
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();

		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('billingAddresses', $this->user->getBillingAddressArray());
		$response->set('shippingAddresses', $this->user->getShippingAddressArray());
		return $response;
	}

	/**
	 *	@role login
	 */
	public function viewOrder()
	{
		if ($order = $this->user->getOrder($this->request->get('id')))
		{
			$this->addAccountBreadcrumb();
			$this->addBreadCrumb($this->translate('_your_orders'), $this->router->createUrl(array('controller' => 'user', 'action' => 'orders'), true));
			$this->addBreadCrumb($order->invoiceNumber->get(), '');

			// mark all notes as read
			$notes = $order->getNotes();
			foreach ($notes as $note)
			{
				if (!$note->isRead->get() && $note->isAdmin->get())
				{
					$note->isRead->set(true);
					$note->save();
				}
			}

			$response = new ActionResponse();
			$response->set('order', $order->toArray());
			$response->set('notes', $notes->toArray());
			$response->set('user', $this->user->toArray());
			$response->set('noteForm', $this->buildNoteForm());
			return $response;
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	/**
	 *	@role login
	 */
	public function reorder()
	{
		$order = $this->user->getOrder($this->request->get('id'));
		if ($order)
		{
			ClassLoader::import('application.model.order.SessionOrder');
			$newOrder = clone $order;
			SessionOrder::save($newOrder);
			return new ActionRedirectResponse('order', 'index');
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	/**
	 *	@role login
	 */
	public function addNote()
	{
		ClassLoader::import('application.model.order.OrderNote');

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$set = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
		if (!$set->size() || !$this->buildNoteValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$order = $set->get(0);
		$note = OrderNote::getNewInstance($order, $this->user);
		$note->text->set($this->request->get('text'));
		$note->isAdmin->set(false);
		$note->save();

		if ($this->config->get('NOTIFY_NEW_NOTE'))
		{
			$order->user->get()->load();

			$email = new Email($this->application);
			$email->setTo($this->config->get('NOTIFICATION_EMAIL'), $this->config->get('STORE_NAME'));
			$email->setTemplate('notify.message');
			$email->set('order', $order->toArray(array('payments' => true)));
			$email->set('message', $note->toArray());
			$email->set('user', $this->user->toArray());
			$email->send();
		}

		return new ActionRedirectResponse('user', 'viewOrder', array('id' => $order->getID()));
	}

	/**
	 *	@role login
	 */
	public function orderInvoice()
	{
		$this->addAccountBreadcrumb();
		$this->application->setTheme('');

		$order = $this->getOrder($this->request->get('id'));

		if (!$order)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$response = new ActionResponse();
		$response->set('order', $order->toArray(array('payments' => true)));
		$response->set('user', $this->user->toArray());
		return $response;
	}

	public function register()
	{
		if ($this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return new ActionRedirectResponse('user', 'registerAddress');
		}

		$form = $this->buildRegForm();
		$response = new ActionResponse('regForm', $form);

		SessionUser::getAnonymousUser()->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	public function registerAddress()
	{
		$this->request->set('return', $this->router->createUrl(array('controller' => 'user', 'action' => 'index')));
		$response = $this->checkout();
		$response->get('form')->set('regType', 'register');
		return $response;
	}

	public function doRegister()
	{
		if (!$this->buildRegValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'register');
		}

		$this->order;

		$user = $this->createUser($this->request->get('password'));
		$this->user = $user;
		$this->mergeOrder();

		if (!$this->config->get('REG_EMAIL_CONFIRM'))
		{
			if ($this->request->get('return'))
			{
				return new RedirectResponse($this->request->get('return'));
			}
			else
			{
				return new ActionRedirectResponse('user', 'index');
			}
		}
		else
		{
			return new ActionRedirectResponse('user', 'unconfirmed');
		}
	}

	public function unconfirmed()
	{
		return new ActionResponse();
	}

	public function confirm()
	{
		$success = false;

		$user = User::getInstanceByEmail($this->request->get('email'));
		if ($user && !$user->isEnabled->get() && $user->getPreference('confirmation'))
		{
			if ($this->request->get('code') == $user->getPreference('confirmation'))
			{
				$user->setPreference('confirmation', null);
				$user->isEnabled->set(true);
				$user->save();

				SessionUser::setUser($user);

				$success = true;

				$this->sendWelcomeEmail($user);
			}
		}

		return new ActionResponse('success', $success);
	}

	/**
	 *  Login form
	 */
	public function login()
	{
		if ($this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return new ActionRedirectResponse('user', 'registerAddress');
		}

		$this->addBreadCrumb($this->translate('_login'), $this->router->createUrl(array('controller' => 'user', 'action' => 'login'), true));

		$form = $this->buildRegForm();
		$response = new ActionResponse();
		$response->set('regForm', $form);
		$response->set('email', $this->request->get('email'));
		$response->set('failed', $this->request->get('failed'));
		$response->set('return', $this->request->get('return'));

		SessionUser::getAnonymousUser()->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	/**
	 *  Process actual login
	 */
	public function doLogin()
	{
		$user = User::getInstanceByLogin($this->request->get('email'), $this->request->get('password'));
		if (!$user)
		{
			return new ActionRedirectResponse('user', 'login', array('query' => 'failed=true'));
		}

		// login
		SessionUser::setUser($user);

		$this->user = $user;
		$this->mergeOrder();

		if ($return = $this->request->get('return'))
		{
			if (substr($return, 0, 1) != '/')
			{
				$return = $this->router->createUrlFromRoute($return);
			}

			return new RedirectResponse($return);
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	private function mergeOrder()
	{
		// load the last un-finalized order by this user
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCreated'), 'DESC');
		$f->setLimit(1);
		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);

		if (!$this->order->user->get() || $this->order->user->get()->getID() == $this->user->getID())
		{
			if ($s->size())
			{
				$order = $s->get(0);
				if ($this->order->getID() != $order->getID())
				{
					$sessionOrder = SessionOrder::getOrder();
					$order->loadItems();
					$order->merge($sessionOrder);
					$order->save();
					SessionOrder::setOrder($order);
					$this->order->delete();
				}
			}
			else
			{
				if ($this->order->getID())
				{
					$this->order->setUser($this->user);
					SessionOrder::save($this->order);
				}
			}
		}
	}

	public function remindPassword()
	{
		$this->addBreadCrumb($this->translate('_remind_password'), $this->router->createUrl(array('controller' => 'user', 'action' => 'login'), true));

		$response = new ActionResponse();
		$response->set('form', $this->buildPasswordReminderForm());
		$response->set('return', $this->request->get('return'));
		return $response;
	}

	public function doRemindPassword()
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('User', 'email'), $this->request->get('email')));
		$s = ActiveRecordModel::getRecordSet('User', $f);
		if ($s->size())
		{
			$user = $s->get(0);
			$user->setPassword($user->getAutoGeneratedPassword());
			$user->save();

			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('user.remindPassword');
			$email->send();
		}

		return new ActionRedirectResponse('user', 'remindComplete', array('query' => 'email=' . $this->request->get('email')));
	}

	public function remindComplete()
	{
		$response = new ActionResponse();
		$response->set('email', $this->request->get('email'));
		return $response;
	}

	public function logout()
	{
		SessionUser::destroy();
		return new ActionRedirectResponse('index', 'index');
	}

	public function checkout()
	{
		if ($this->config->get('DISABLE_GUEST_CHECKOUT') && !$this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return new ActionRedirectResponse('user', 'login', array('query' => array('return' => $this->router->createUrl(array('controller' => 'checkout', 'action' => 'pay')))));
		}

		$form = $this->buildForm();

		$form->set('billing_country', $this->config->get('DEF_COUNTRY'));
		$form->set('shipping_country', $this->config->get('DEF_COUNTRY'));
		$form->set('return', $this->request->get('return'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('countries', $this->getCountryList($form));
		$response->set('states', $this->getStateList($form->get('billing_country')));
		$response->set('shippingStates', $this->getStateList($form->get('shipping_country')));
		$response->set('order', $this->order->toArray());

		SessionUser::getAnonymousUser()->getSpecification()->setFormResponse($response, $form);
		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, 'billing_');
		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, 'shipping_');

		return $response;
	}

	public function processCheckoutRegistration()
	{
		ActiveRecordModel::beginTransaction();

		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			$action = $this->request->get('regType') == 'register' ? 'registerAddress' : 'checkout';
			return new ActionRedirectResponse('user', $action, array('query' => array('return' => $this->request->get('return'))));
		}

		// create user account
		$user = $this->createUser(null, 'billing_');

		// create billing and shipping address
		$address = $this->createAddress('billing_');
		$billingAddress = BillingAddress::getNewInstance($user, $address);
		$billingAddress->save();

		$shippingAddress = ShippingAddress::getNewInstance($user, $this->request->get('sameAsBilling') ? clone $address : $this->createAddress('shipping_'));
		$shippingAddress->save();

		if ($this->request->get('password'))
		{
			$user->setPassword($this->request->get('password'));
		}

		$user->defaultShippingAddress->set($shippingAddress);
		$user->defaultBillingAddress->set($billingAddress);
		$user->save();

		// set order addresses
		$this->order->billingAddress->set($billingAddress->userAddress->get());
		$this->order->shippingAddress->set($shippingAddress->userAddress->get());
		$this->order->setUser($user);
		SessionOrder::save($this->order);

		ActiveRecordModel::commit();

		if ($return = $this->request->get('return'))
		{
			return new RedirectResponse($this->router->createUrlFromRoute($return));
		}
		else
		{
			return new ActionRedirectResponse('checkout', 'shipping');
		}
	}

	private function createAddress($prefix)
	{
		// get address state
		if ($this->request->get($prefix . 'state_select'))
		{
			try
			{
				$state = ActiveRecordModel::getInstanceByID('State', $this->request->get($prefix . 'state_select'), ActiveRecordModel::LOAD_DATA);
			}
			catch (Exception $e)
			{
				throw new ApplicationException('State not found');
			}

			$country = $state->countryID->get();
		}
		else
		{
			$country = $this->request->get($prefix . 'country');
		}

		$address = UserAddress::getNewInstance();
		$address->loadRequestData($this->request, $prefix);

		if (isset($state))
		{
			$address->state->set($state);
		}
		else
		{
			$address->stateName->set($this->request->get($prefix . 'state_text'));
		}

		$address->countryID->set($country);
		$address->save();

		return $address;
	}

	/**
	 *	@role login
	 */
	public function deleteShippingAddress()
	{
		try
		{
			return $this->deleteAddress(ShippingAddress::getUserAddress($this->request->get('id'), $this->user));
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	/**
	 *	@role login
	 */
	public function deleteBillingAddress()
	{
		try
		{
			return $this->deleteAddress(BillingAddress::getUserAddress($this->request->get('id'), $this->user));
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	private function deleteAddress(UserAddressType $address)
	{
		$address->delete();
		return new RedirectResponse($this->router->createURLFromRoute($this->request->get('return')));
	}

	/**
	 *	@role login
	 */
	public function editShippingAddress()
	{
		try
		{
			$response = $this->editAddress(ShippingAddress::getUserAddress($this->request->get('id'), $this->user));
			$this->addBreadCrumb($this->translate('_edit_shipping_address'), '');
			return $response;
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	/**
	 *	@role login
	 */
	public function editBillingAddress()
	{
		try
		{
			$response = $this->editAddress(BillingAddress::getUserAddress($this->request->get('id'), $this->user));
			$this->addBreadCrumb($this->translate('_edit_shipping_address'), '');
			return $response;
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	private function editAddress(UserAddressType $addressType)
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();

		$form = $this->buildAddressForm();
		$address = $addressType->userAddress->get();

		$form->setData($address->toArray());
		$form->set('country', $address->countryID->get());
		$form->set('state_text', $address->stateName->get());

		if ($address->state->get())
		{
			$form->set('state_select', $address->state->get()->getID());
		}

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('return', $this->request->get('return'));
		$response->set('countries', $this->getCountryList($form));
		$response->set('states', $this->getStateList($form->get('country')));
		$response->set('address', $address->toArray());
		$response->set('addressType', $addressType->toArray());

		$address->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	/**
	 *	@role login
	 */
	public function saveShippingAddress()
	{
		try
		{
			$address = ShippingAddress::getUserAddress($this->request->get('id'), $this->user);
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editShippingAddress', array('id' =>$this->request->get('id'), 'query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *	@role login
	 */
	public function saveBillingAddress()
	{
		try
		{
			$address = BillingAddress::getUserAddress($this->request->get('id'), $this->user);
		}
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editBillingAddress', array('id' =>$this->request->get('id'), 'query' => array('return' => $this->request->get('return')))));
	}

	private function doSaveAddress(UserAddressType $address, ActionRedirectResponse $invalidResponse)
	{
		$address = $address->userAddress->get();
		if ($this->buildAddressValidator()->isValid())
		{
			$this->saveAddress($address);
			return new RedirectResponse($this->router->createURLFromRoute($this->request->get('return')));
		}
		else
		{
			return $invalidResponse;
		}
	}

	/**
	 *	@role login
	 */
	public function addBillingAddress($shipping = false)
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();
   		if (!$shipping)
   		{
			$this->addBreadCrumb($this->translate('_add_billing_address'), '');
		}

		$form = $this->buildAddressForm();

		$form->set('firstName', $this->user->firstName->get());
		$form->set('lastName', $this->user->lastName->get());
		$form->set('companyName', $this->user->companyName->get());

		$this->user->loadAddresses();

		if ($this->user->defaultBillingAddress->get())
		{
			$form->set('country', $this->user->defaultBillingAddress->get()->userAddress->get()->countryID->get());
			$form->set('phone', $this->user->defaultBillingAddress->get()->userAddress->get()->phone->get());
		}
		else
		{
			$form->set('country', $this->config->get('DEF_COUNTRY'));
		}

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('return', $this->request->get('return'));
		$response->set('countries', $this->getCountryList($form));
		$response->set('states', $this->getStateList($form->get('country')));

		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, '');

		return $response;
	}

	/**
	 *	@role login
	 */
	public function addShippingAddress()
	{
		$response = $this->addBillingAddress(true);
		$this->addBreadCrumb($this->translate('_add_shipping_address'), '');
		return $response;
	}

	/**
	 *	@role login
	 */
	public function doAddBillingAddress()
	{
		return $this->doAddAddress('BillingAddress', new ActionRedirectResponse('user', 'addBillingAddress', array('query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *	@role login
	 */
	public function doAddShippingAddress()
	{
		return $this->doAddAddress('ShippingAddress', new ActionRedirectResponse('user', 'addShippingAddress', array('query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *  Return a list of states for the selected country
	 *  @return JSONResponse
	 */
	public function states()
	{
		$states = State::getStatesByCountry($this->request->get('country'));
		return new JSONResponse($states);
	}

	/**
	 *  Download an ordered file
	 *
	 *  @return ObjectFileResponse
	 *  @return ActionRedirectResponse
	 *	@role login
	 */
	public function download()
	{
		// get and validate OrderedItem instance first
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('OrderedItem', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));

		$s = ActiveRecordModel::getRecordSet('OrderedItem', $f, array('CustomerOrder', 'Product'));

		// OrderedItem does not exist
		if (!$s->size())
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$item = $s->get(0);
		$file = $item->getFileByID($this->request->get('fileID'));

		// file does not exist for OrderedItem
		if (!$file)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		// download expired
		if (!$item->isDownloadable($file))
		{
			return new ActionRedirectResponse('user', 'downloadExpired', array('id' => $item->getID(), 'query' => array('fileID' => $file->getID())));
		}

		return new ObjectFileResponse($file);
	}

	/**
	 *	Make payment for unpaid or partially paid order
	 *	@role login
	 */
	public function pay()
	{
		$this->loadLanguageFile('Checkout');
		$this->addAccountBreadcrumb();
		$this->addBreadCrumb($this->translate('_pay'), '');

		$order = $this->getOrder($this->request->get('id'));

		if (!$order || $order->isPaid->get())
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$response = new ActionResponse();
		$response->set('order', $order->toArray(array('payments' => true)));
		$response->set('id', $order->getID());

		$checkout = new CheckoutController($this->application);
		$checkout->setPaymentMethodResponse($response, $order);

		return $response;
	}

	/**
	 *	@return User
	 */
	private function createUser($password = '', $prefix = '')
	{
		$user = User::getNewInstance($this->request->get('email'), $this->request->get('password'));
		$user->firstName->set($this->request->get($prefix . 'firstName'));
		$user->lastName->set($this->request->get($prefix . 'lastName'));
		$user->companyName->set($this->request->get($prefix . 'companyName'));
		$user->email->set($this->request->get('email'));
		$user->isEnabled->set(!$this->config->get('REG_EMAIL_CONFIRM'));

		// custom fields
		$user->loadRequestData($this->request, array());

		if ($password)
		{
			$this->session->set('password', $password);
			$user->setPassword($password);
		}

		$user->save();

		if (!$this->config->get('REG_EMAIL_CONFIRM'))
		{
			SessionUser::setUser($user);
			$this->sendWelcomeEmail($user);
		}
		else
		{
			$code = rand(1, 10000000) . rand(1, 10000000);
			$user->setPreference('confirmation', $code);
			$user->save();

			$email = new Email($this->application);
			$email->setUser($user);
			$email->set('code', $code);
			$email->setTemplate('user.confirm');
			$email->send();
		}

		return $user;
	}

	private function sendWelcomeEmail(User $user)
	{
		// send welcome email with user account details
		if ($this->config->get('EMAIL_NEW_USER'))
		{
			$user->setPassword($this->session->get('password'));
			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('user.new');
			$email->send();
		}
	}

	private function doAddAddress($addressClass, Response $failureResponse)
	{
		$validator = $this->buildAddressValidator();
		if ($validator->isValid())
		{
			$address = UserAddress::getNewInstance();
			$this->saveAddress($address);

			$addressType = call_user_func_array(array($addressClass, 'getNewInstance'), array($this->user, $address));
			$addressType->save();

			if ($this->request->get('return'))
			{
				$response = new RedirectResponse($this->router->createURLFromRoute($this->request->get('return')));
			}
			else
			{
				$response = new ActionRedirectResponse('user', 'addresses');
			}

			return $response;
		}
		else
		{
			return $failureResponse;
		}
	}

	/**************  VALIDATION ******************/
	private function buildPasswordReminderForm()
	{
		$validator = $this->getValidator("emailChange", $this->request);
		$this->validateEmail($validator, '_err_not_unique_email_for_change');

		return new Form($validator);
	}

	private function buildEmailChangeForm()
	{
		return new Form($this->buildEmailChangeValidator());
	}

	private function buildEmailChangeValidator()
	{
		$validator = $this->getValidator("emailChange", $this->request);
		$this->validateEmail($validator, '_err_not_unique_email_for_change');

		return $validator;
	}

	private function buildPasswordChangeForm()
	{
		return new Form($this->buildPasswordChangeValidator());
	}

	private function buildPasswordChangeValidator()
	{
		ClassLoader::import("application.helper.check.IsPasswordCorrectCheck");

		$validator = $this->getValidator("passwordChange", $this->request);
		$validator->addCheck('currentpassword', new IsNotEmptyCheck($this->translate('_err_enter_current_password')));
		$validator->addCheck('currentpassword', new IsPasswordCorrectCheck($this->translate('_err_incorrect_current_password'), $this->user));

		$this->validatePassword($validator);

		return $validator;
	}

	private function buildPersonalDataForm(User $user)
	{
		$form = new Form($this->buildPersonalDataValidator($user));
		$form->setData($this->user->toArray());
		return $form;
	}

	private function buildPersonalDataValidator(User $user)
	{
		$validator = $this->getValidator("userData", $this->request);
		$this->validateName($validator);
		$user->getSpecification()->setValidation($validator);
		return $validator;
	}

	private function buildRegForm()
	{
		return new Form($this->buildRegValidator());
	}

	private function buildRegValidator()
	{
		$validator = $this->getValidator("userRegistration", $this->request);
		$this->validateName($validator);
		$this->validateEmail($validator);
		$this->validatePassword($validator);

		SessionUser::getAnonymousUser()->getSpecification()->setValidation($validator);

		return $validator;
	}

	private function buildAddressForm()
	{
		return new Form($this->buildAddressValidator());
	}

	private function buildAddressValidator()
	{
		$validator = $this->getValidator("userAddress", $this->request);
		$this->validateAddress($validator);
		return $validator;
	}

	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{
		// validate contact info
		$validator = $this->getValidator("registrationValidator", $this->request);

		$this->validateAddress($validator, 'billing_');
		$this->validateEmail($validator);

		if (($this->config->get('PASSWORD_GENERATION') == 'PASSWORD_REQUIRE') || $this->request->get('password'))
		{
			$this->validatePassword($validator);
		}

		if ($this->order->isShippingRequired())
		{
			$this->validateAddress($validator, 'shipping_', true);
		}

		SessionUser::getAnonymousUser()->getSpecification()->setValidation($validator);

		return $validator;
	}

	private function validateName(RequestValidator $validator, $fieldPrefix = '', $orCheck = false)
	{
		foreach (array('firstName' => '_err_enter_first_name',
						'lastName' => '_err_enter_last_name') as $field => $error)
		{
			$field = $fieldPrefix . $field;
			$check = new IsNotEmptyCheck($this->translate($error));
			$check = $orCheck ? new OrCheck(array($field, 'sameAsBilling'), array($check, new IsNotEmptyCheck('')), $this->request) : $check;
			$validator->addCheck($field, $check);
		}
	}

	private function validateEmail(RequestValidator $validator, $uniqueError = '_err_not_unique_email')
	{
		ClassLoader::import("application.helper.check.IsUniqueEmailCheck");

		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_enter_email')));
		$validator->addCheck('email', new IsValidEmailCheck($this->translate('_err_invalid_email')));

		$emailErr = $this->translate($uniqueError);
		$emailErr = str_replace('%1', $this->router->createUrl(array('controller' => 'user', 'action' => 'login', 'query' => array('email' => $this->request->get('email'))), true), $emailErr);
		$validator->addCheck('email', new IsUniqueEmailCheck($emailErr));
	}

	public function validateAddress(RequestValidator $validator, $fieldPrefix = '', $orCheck = false)
	{
		$this->validateName($validator, $fieldPrefix, $orCheck);

		$fields = $checks = array();

		if ($this->config->get('REQUIRE_PHONE'))
		{
			$fields[] = $fieldPrefix . 'phone';
			$checks[] = new IsNotEmptyCheck($this->translate('_err_enter_phone'));
		}

		$fields[] = $fieldPrefix . 'address1';
		$checks[] = new IsNotEmptyCheck($this->translate('_err_enter_address'));

		$fields[] = $fieldPrefix . 'city';
		$checks[] = new IsNotEmptyCheck($this->translate('_err_enter_city'));

		$fields[] = $fieldPrefix . 'country';
		$checks[] = new IsNotEmptyCheck($this->translate('_err_select_country'));

		$fields[] = $fieldPrefix . 'postalCode';
		$checks[] = new IsNotEmptyCheck($this->translate('_err_enter_zip'));

		// custom field validation
		$tempVal = $this->getValidator('tempVal', $this->request);
		UserAddress::getNewInstance()->getSpecification()->setValidation($tempVal, null, $fieldPrefix);
		foreach ($tempVal->getValidatorVars() as $var)
		{
			foreach ($var->getChecks() as $check)
			{
				$fields[] = $var->getName();
				$checks[] = $check;
			}

			foreach ($var->getFilters() as $filter)
			{
				$validator->addFilter($var->getName(), $filter);
			}
		}

		foreach ($fields as $key => $field)
		{
			$check = $orCheck ? new OrCheck(array($field, 'sameAsBilling'), array($checks[$key], new IsNotEmptyCheck('')), $this->request) : $checks[$key];
			$validator->addCheck($field, $check);
		}

		if (!$this->config->get('DISABLE_STATE'))
		{
			$fieldList = array($fieldPrefix . 'state_select', $fieldPrefix . 'state_text');
			$checkList = array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck(''));
			if ($orCheck)
			{
				$fieldList[] = 'sameAsBilling';
				$checkList[] = new IsNotEmptyCheck('');
			}
			$stateCheck = new OrCheck($fieldList, $checkList, $this->request);
			$validator->addCheck($fieldPrefix . 'state_select', $stateCheck);
		}
	}

	private function validatePassword(RequestValidator $validator)
	{
		ClassLoader::import("application.helper.check.PasswordMatchCheck");
		$validator->addCheck('password', new MinLengthCheck(sprintf($this->translate('_err_short_password'), self::PASSWORD_MIN_LENGTH), self::PASSWORD_MIN_LENGTH));
		$validator->addCheck('password', new IsNotEmptyCheck($this->translate('_err_enter_password')));
		$validator->addCheck('confpassword', new IsNotEmptyCheck($this->translate('_err_enter_password')));
		$validator->addCheck('confpassword', new PasswordMatchCheck($this->translate('_err_password_match'), $this->request, 'password', 'confpassword'));
	}

	private function buildNoteForm()
	{
		return new Form($this->buildNoteValidator());
	}

	private function buildNoteValidator()
	{
		$validator = $this->getValidator("orderNote", $this->request);
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_enter_note')));
		$validator->addFilter('text', new HtmlSpecialCharsFilter);
		return $validator;
	}

	private function getOrder($id)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $id));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));

		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, array('User'));
		if ($s->size())
		{
			$order = $s->get(0);
			$order->loadAll();

			if ($order->user->get())
			{
				$order->user->get()->getSpecification();
			}

			return $order;
		}
	}
}

?>