<?php
ClassLoader::import('application.model.order.CustomerOrder');
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

		foreach (ActiveRecordModel::getDataBySQL($query->createString()) as $res)
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
		$item = ActiveRecordModel::getInstanceById('OrderedItem', $this->request->get('id'), ActiveRecordModel::LOAD_DATA, array('CustomerOrder', 'Product'))->toArray();

		$this->addAccountBreadcrumb();
		$this->addFilesBreadcrumb();
		$this->addBreadCrumb($item['Product']['name_lang'], '');

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('OrderedItem', 'ID'), $item['ID']));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));

		$fileArray = $this->loadDownloadableItems($f);

		if (!$fileArray)
		{
			return new ActionRedirectResponse('user', 'index');
		}

		$response = new ActionResponse();
		$response->set('user', $this->user->toArray());
		$response->set('files', $fileArray);
		$response->set('item', $item);
		return $response;
	}

	private function loadDownloadableItems(ARSelectFilter $f)
	{
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isPaid'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'type'), Product::TYPE_DOWNLOADABLE));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'ID'), 'DESC');

		$downloadable = ActiveRecordModel::getRecordSet('OrderedItem', $f, array('Product', 'CustomerOrder'));
		$fileArray = array();
		foreach ($downloadable as &$item)
		{
			$files = $item->product->get()->getFiles();
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
			$array['Product']['Files'] = ProductFileGroup::mergeGroupsWithFields($item->product->get()->getFileGroups()->toArray(), $itemFiles);

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
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));

		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);
		if ($s->size())
		{
			$order = $s->get(0);
			$order->loadAll();

			$this->addAccountBreadcrumb();
			$this->addBreadCrumb($this->translate('_your_orders'), $this->router->createUrl(array('controller' => 'user', 'action' => 'orders'), true));
			$this->addBreadCrumb($order->getID(), '');

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

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));

		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
		if ($s->size())
		{
			$order = $s->get(0);
			$order->loadAll();
			$response = new ActionResponse();
			$response->set('order', $order->toArray(array('payments' => true)));
			$response->set('user', $this->user->toArray());
			return $response;
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	public function register()
	{
		$response = new ActionResponse();
		$response->set('regForm', $this->buildRegForm());
		return $response;
	}

	public function doRegister()
	{
		if (!$this->buildRegValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'register');
		}

		$user = $this->createUser($this->request->get('password'));

		if ($this->request->isValueSet('return'))
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}

	/**
	 *  Login form
	 */
	public function login()
	{
		$response = new ActionResponse();
		$response->set('regForm', $this->buildRegForm());
		$response->set('email', $this->request->get('email'));
		$response->set('failed', $this->request->get('failed'));
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

		// load the last un-finalized order by this user
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $user->getID()));
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCreated'), 'DESC');
		$f->setLimit(1);
		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);

		if (!$this->order->user->get() || $this->order->user->get()->getID() == $user->getID())
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
					$this->order->user->set($user);
					SessionOrder::save($this->order);
				}
			}
		}

		return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
	}

	public function remindPassword()
	{
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
		$form = $this->buildForm();

		$form->set('billing_country', $this->config->get('DEF_COUNTRY'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('countries', $this->getCountryList($form));
		$response->set('states', $this->getStateList($form->get('billing_country')));
		return $response;
	}

	public function processCheckoutRegistration()
	{
		ActiveRecordModel::beginTransaction();

		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			return new ActionRedirectResponse('user', 'checkout');
		}

		// create user account
		$user = $this->createUser();

		// get billing address state
		if ($this->request->get('billing_state_select'))
		{
			try
			{
				$billingState = ActiveRecordModel::getInstanceByID('State', $this->request->get('billing_state_select'), ActiveRecordModel::LOAD_DATA);
			}
			catch (Exception $e)
			{
				throw new ApplicationException('State not found');
			}

			$billingCountry = $billingState->countryID->get();
		}

		// create user billing addresses
		$address = UserAddress::getNewInstance();
		$address->firstName->set($user->firstName->get());
		$address->lastName->set($user->lastName->get());
		$address->companyName->set($user->companyName->get());
		$address->address1->set($this->request->get('billing_address1'));
		$address->address2->set($this->request->get('billing_address2'));
		$address->city->set($this->request->get('billing_city'));
		$address->countryID->set($this->request->get('billing_country'));
		$address->postalCode->set($this->request->get('billing_zip'));
		$address->phone->set($this->request->get('phone'));
		if (isset($billingState))
		{
			$address->state->set($billingState);
		}
		else
		{
			$address->stateName->set($this->request->get('billing_state_text'));
		}
		$address->save();

		$billingAddress = BillingAddress::getNewInstance($user, $address);
		$billingAddress->save();

		// create user shipping address
		if ($this->request->get('sameAsBilling'))
		{
			$address = clone $address;
		}
		else
		{
			$address = UserAddress::getNewInstance();
			$address->name->set($user->name->get());
			$address->address1->set($this->request->get('shipping_address1'));
			$address->address2->set($this->request->get('shipping_address2'));
		}

		$address->save();
		$shippingAddress = ShippingAddress::getNewInstance($user, $address);
		$shippingAddress->save();

		$user->defaultShippingAddress->set($shippingAddress);
		$user->defaultBillingAddress->set($billingAddress);
		$user->save();

		// set order addresses
		$this->order->billingAddress->set($billingAddress->userAddress->get());
		$this->order->shippingAddress->set($shippingAddress->userAddress->get());
		$this->order->user->set($user);
		SessionOrder::save($this->order);

		ActiveRecordModel::commit();

		return new ActionRedirectResponse('checkout', 'shipping');
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

		$form->set('zip', $address->postalCode->get());

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('return', $this->request->get('return'));
		$response->set('countries', $this->getCountryList($form));
		$response->set('states', $this->getStateList($form->get('country')));
		$response->set('address', $address->toArray());
		$response->set('addressType', $addressType->toArray());
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
	 *	@return User
	 */
	private function createUser($password = '')
	{
		$user = User::getNewInstance($this->request->get('email'), $this->request->get('password'));
		$user->firstName->set($this->request->get('firstName'));
		$user->lastName->set($this->request->get('lastName'));
		$user->companyName->set($this->request->get('companyName'));
		$user->email->set($this->request->get('email'));
		$user->isEnabled->set(true);

		if ($password)
		{
			$user->setPassword($password);
		}

		$user->save();

		SessionUser::setUser($user);

		// send welcome email with user account details
		if ($this->config->get('EMAIL_NEW_USER'))
		{
			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('user.new');
			$email->send();
		}

		return $user;
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

	private function saveAddress(UserAddress $address)
	{
		$address->loadRequestData($this->request);
		$address->countryID->set($this->request->get('country'));
		$address->postalCode->set($this->request->get('zip'));
		$address->stateName->set($this->request->get('state_text'));
		if ($this->request->get('state_select'))
		{
			$address->state->set(State::getStateByIDAndCountry($this->request->get('state_select'), $this->request->get('country')));
		}
		else
		{
			$address->state->set(null);
		}
		$address->save();
	}

	private function getCountryList(Form $form)
	{
		$defCountry = $this->config->get('DEF_COUNTRY');

		$countries = $this->application->getEnabledCountries();
		asort($countries);

		// set default country first
		if (isset($countries[$defCountry]))
		{
			$d = $countries[$defCountry];
			unset($countries[$defCountry]);
			$countries = array_merge(array($defCountry => $d), $countries);
		}

		return $countries;
	}

	private function getStateList($country)
	{
		$states = State::getStatesByCountry($country);

		if ($states)
		{
			$states = array('' => '') + $states;
		}

		return $states;
	}

	/**************  VALIDATION ******************/
	private function buildPasswordReminderForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		$validator = new RequestValidator("emailChange", $this->request);
		$this->validateEmail($validator, '_err_not_unique_email_for_change');

		return new Form($validator);
	}

	private function buildEmailChangeForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildEmailChangeValidator());
	}

	private function buildEmailChangeValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("emailChange", $this->request);
		$this->validateEmail($validator, '_err_not_unique_email_for_change');

		return $validator;
	}

	private function buildPasswordChangeForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildPasswordChangeValidator());
	}

	private function buildPasswordChangeValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		ClassLoader::import("application.helper.check.IsPasswordCorrectCheck");

		$validator = new RequestValidator("passwordChange", $this->request);
		$validator->addCheck('currentpassword', new IsNotEmptyCheck($this->translate('_err_enter_current_password')));
		$validator->addCheck('currentpassword', new IsPasswordCorrectCheck($this->translate('_err_incorrect_current_password'), $this->user));

		$this->validatePassword($validator);

		return $validator;
	}

	private function buildRegForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildRegValidator());
	}

	private function buildRegValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("userRegistration", $this->request);
		$this->validateName($validator);
		$this->validateEmail($validator);
		$this->validatePassword($validator);
		return $validator;
	}

	private function buildAddressForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildAddressValidator());
	}

	private function buildAddressValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("userAddress", $this->request);
		$this->validateAddress($validator);
		return $validator;
	}

	private function buildForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		// validate contact info
		$validator = new RequestValidator("registrationValidator", $this->request);
		$this->validateEmail($validator);

		// validate billing info
		$this->validateAddress($validator, 'billing_');

		// validate shipping address
		$shippingCondition = new ShippingAddressCheckCondition($this->request);
		$validator->addCheck('shipping_address1', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_address'))));
		$validator->addCheck('shipping_city', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_city'))));
		$validator->addCheck('shipping_country', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_select_country'))));
		$validator->addCheck('shipping_zip', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_zip'))));

		$stateCheck = new OrCheck(array('shipping_state_select', 'shipping_state_text'), array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck('')), $this->request);
		$validator->addCheck('shipping_state_select', new ConditionalCheck($shippingCondition, $stateCheck));
//		$validator->addCheck('billing_state_select', new IsValidStateCheck($this->translate('_err_select_state')));

//		$validator->addConditionalCheck($shippingCondition, )

		return $validator;
	}

	private function validateName(RequestValidator $validator)
	{
		$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_enter_first_name')));
		$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_enter_last_name')));
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

	private function validateAddress(RequestValidator $validator, $fieldPrefix = '')
	{
		$this->validateName($validator);

		if ($this->config->get('REQUIRE_PHONE'))
		{
			$validator->addCheck('phone', new IsNotEmptyCheck($this->translate('_err_enter_phone')));
		}

		$validator->addCheck($fieldPrefix . 'address1', new IsNotEmptyCheck($this->translate('_err_enter_address')));
		$validator->addCheck($fieldPrefix . 'city', new IsNotEmptyCheck($this->translate('_err_enter_city')));
		$validator->addCheck($fieldPrefix . 'country', new IsNotEmptyCheck($this->translate('_err_select_country')));
		$validator->addCheck($fieldPrefix . 'zip', new IsNotEmptyCheck($this->translate('_err_enter_zip')));

		$stateCheck = new OrCheck(array($fieldPrefix . 'state_select', $fieldPrefix . 'state_text'), array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck('')), $this->request);
		$validator->addCheck($fieldPrefix . 'state_select', $stateCheck);
//		$validator->addCheck('billing_state_select', new IsValidStateCheck($this->translate('_err_select_state')));
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
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildNoteValidator());
	}

	private function buildNoteValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("orderNote", $this->request);
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_enter_note')));
		$validator->addFilter('text', new HtmlSpecialCharsFilter);
		return $validator;
	}
}

ClassLoader::import('framework.request.validator.check.CheckCondition');

class ShippingAddressCheckCondition extends CheckCondition
{
	function isSatisfied()
	{
		return !$this->request->isValueSet('sameAsBilling');
	}
}

?>