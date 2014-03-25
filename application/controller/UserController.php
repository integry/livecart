<?php

use Phalcon\Validation\Validator;
use \user\User;

/**
 *  Handles user account related logic
 *
 *  @author Integry Systems
 *  @package application/controller
 */
class UserController extends FrontendController
{
 	const PASSWORD_MIN_LENGTH = 5;

 	const COUNT_RECENT_FILES = 5;

 	public function initialize()
 	{
 		parent::initialize();

 		$this->loadLanguageFile('Frontend');
 		$this->loadLanguageFile('User');
	}

	private function addAccountBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_your_account'), $this->url->get('user'));
	}

	private function addAddressBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_manage_addresses'), $this->url->get('user/addresses'));
	}

	private function addFilesBreadcrumb()
	{
		$this->addBreadCrumb($this->translate('_your_files'), $this->url->get('user/files'));
	}

	/**
	 *	@role login
	 */
	public function indexAction()
	{
		return;
		//return $this->response->redirect('seller/index');
		$this->addAccountBreadcrumb();

		// get recent orders
		$f = new ARSelectFilter();
		$f->limit($this->config->get('USER_COUNT_RECENT_ORDERS'));
		$f->setCondition(new IsNullCond('CustomerOrder.parentID'));
		$orders = $this->loadOrders($f);
		$orderArray = $this->getOrderArray($orders);

		// get last invoice & unpaid count
		$pendingInvoiceCount = $this->user->countPendingInvoices();

		$lastInvoiceArray = array();
		if ($pendingInvoiceCount)
		{
			$f = new ARSelectFilter();
			$f->limit(1);
			$f->setCondition(new AndChainCondition(array(
				new IsNotNullCond('CustomerOrder.parentID'))
			));
			$lastInvoiceArray = $this->getOrderArray($this->loadOrders($f));
		}

		// get downloadable items
		$f = query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$f->limit(self::COUNT_RECENT_FILES);



		$this->set('orders', $orderArray);
		$this->set('files', $this->loadDownloadableItems(query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()))));

		// get unread messages
				$f = query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$f->andWhere('OrderNote.isAdmin = :OrderNote.isAdmin:', array('OrderNote.isAdmin' => 1));
		$f->andWhere('OrderNote.isRead = :OrderNote.isRead:', array('OrderNote.isRead' => 0));
		$f->orderBy('OrderNote.ID', 'DESC');
		$this->set('notes', ActiveRecordModel::getRecordSetArray('OrderNote', $f, array('User', 'CustomerOrder')));

		// feedback/confirmation message that was stored in session by some other action
		$this->set('userConfirm', $this->session->pullValue('userConfirm'));
		$this->set('pendingInvoiceCount', $pendingInvoiceCount);
		$this->set('lastInvoiceArray', $lastInvoiceArray);

	}

	/**
	 *	@role login
	 */
	public function ordersAction()
	{
		$this->addAccountBreadcrumb();
		$this->addBreadCrumb($this->translate('_your_orders'), '');

		$page = $this->request->get('id', 1);
		$perPage = $this->getOrdersPerPage();
		$f = $this->getOrderListPaginateFilter($page, $perPage);
		$f->setCondition(new IsNullCond(new ARFieldHandle('CustomerOrder','parentID')));
		return $this->getOrdersListResponse($this->loadOrders($f), $page, $perPage);
	}

	/**
	 *	@role login
	 */
	public function invoicesAction()
	{
		$page = $this->request->get('id', 1);
		$perPage = $this->getOrdersPerPage();
		$f = $this->getOrderListPaginateFilter($page, $perPage);
		$f->andWhere(
			new AndChainCondition(array(
				new IsNotNullCond(new ARFieldHandle('CustomerOrder','parentID')),
				new EqualsCond(new ARFieldHandle('CustomerOrder','isRecurring'), 1)
			))
		);
		$f->orderBy(new ARFieldHandle('CustomerOrder','dateDue'));
		return $this->getOrdersListResponse($this->loadOrders($f), $page, $perPage);
	}

	/**
	 *	@role login
	 */
	public function pendingInvoicesAction()
	{
		$page = $this->request->get('id', 1);
		$perPage = $this->getOrdersPerPage();
		$f = $this->getOrderListPaginateFilter($page, $perPage);
		$f->andWhere(
			new AndChainCondition(array(
				new IsNotNullCond(new ARFieldHandle('CustomerOrder','parentID')),
				new EqualsCond(new ARFieldHandle('CustomerOrder','isRecurring'), 1),
				new EqualsCond(new ARFieldHandle('CustomerOrder','isPaid'), 0)
			))
		);
		$f->orderBy(new ARFieldHandle('CustomerOrder','dateDue'));
		return $this->getOrdersListResponse($this->loadOrders($f), $page, $perPage);
	}

	private function getOrdersListResponse($orders, $page, $perPage)
	{
		$orderArray = $this->getOrderArray($orders);
		$today = strtotime(date('Y-m-d', time()));
		foreach ($orderArray as $k=>$order)
		{
			if ($orderArray[$k]['isRecurring'])
			{
				$orderArray[$k]['overdue'] = $today > strtotime(date('Y-m-d',strtotime($order['dateDue'])));
			}
		}

		$this->set('from', ($perPage * ($page - 1)) + 1);
		$this->set('to', min($perPage * $page, $orders->getTotalRecordCount()));
		$this->set('count', $orders->getTotalRecordCount());
		$this->set('currentPage', $page);
		$this->set('perPage', $perPage);
		$this->set('user', $this->user->toArray());
		$this->set('orders', $orderArray);

	}

	private function getOrdersPerPage()
	{
		$perPage = $this->config->get('USER_ORDERS_PER_PAGE');
		if (!$perPage)
		{
			$perPage = 100000;
		}
		return $perPage;
	}

	private function getOrderListPaginateFilter($page, $perPage, $filter = null)
	{
		if ($filter == null)
		{
			$filter = new ARSelectFilter();
		}
		$filter->limit($perPage, ($page - 1) * $perPage);

		return $filter;
	}

	private function loadOrders(ARSelectFilter $f)
	{
		$f->andWhere('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$f->andWhere('CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => 1));
		$f->orderBy('CustomerOrder.dateCompleted', 'DESC');

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


		$f = new ARSelectFilter(new INCond('OrderNote.orderID', empty($ids) ? array(-1) : array_keys($ids)));
		$f->andWhere('OrderNote.isAdmin = :OrderNote.isAdmin:', array('OrderNote.isAdmin' => 1));
		$f->andWhere('OrderNote.isRead = :OrderNote.isRead:', array('OrderNote.isRead' => 0));
		$f->setGrouping('OrderNote.orderID');

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
	public function filesAction()
	{
		$this->addAccountBreadcrumb();
		$this->addFilesBreadcrumb();


		$this->set('user', $this->user->toArray());
		$this->set('files', $this->loadDownloadableItems(query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()))));
	}

	/**
	 *	@role login
	 */
	public function itemAction()
	{
		$item = OrderedItem::getInstanceByID($this->request->get('id'), ActiveRecordModel::LOAD_DATA, OrderedItem::LOAD_REFERENCES);
		$item->customerOrder->loadAll();
		$item->loadOptions();
		$subItems = $item->getSubitems();
		$item = $item->toArray();

		$this->addAccountBreadcrumb();
		$this->addFilesBreadcrumb();
		$this->addBreadCrumb(isset($item['Product']['name_lang']) ? $item['Product']['name_lang'] : $item['Product']['Parent']['name_lang'], '');

		$f = query::query()->where('OrderedItem.ID = :OrderedItem.ID:', array('OrderedItem.ID' => $item['ID']));
		$f->andWhere('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));

		$fileArray = $this->loadDownloadableItems($f);
		if (!$fileArray && !$subItems)
		{
			return $this->response->redirect('user/index');
		}


		$this->set('user', $this->user->toArray());
		$this->set('files', $fileArray);
		$this->set('item', $item);

		if ($subItems)
		{
			$this->set('subItems', $subItems->toArray());
		}

	}

	private function loadDownloadableItems(ARSelectFilter $f)
	{
		return ProductFile::getOrderFiles($f);
	}

	/**
	 *	@role login
	 */
	public function changePasswordAction()
	{
		$this->addAccountBreadcrumb();

		$this->addBreadCrumb($this->translate('_change_pass'), '');

		$this->set('user', $this->user->toArray());
		$this->set('form', $this->buildPasswordChangeForm());
	}

	/**
	 *	@role login
	 */
	public function doChangePasswordAction()
	{
		if (!$this->buildPasswordChangeValidator()->isValid())
		{
			return $this->response->redirect('user/changePassword');
		}

		$this->user->setPassword($this->request->get('password'));
		$this->user->save();

		$this->session->set('userConfirm', $this->translate('_confirm_password_change'));

		return $this->response->redirect('user/index');
	}

	/**
	 *	@role login
	 */
	public function changeEmailAction()
	{
		$this->addAccountBreadcrumb();

		$this->addBreadCrumb($this->translate('_change_email'), '');

		$this->set('user', $this->user->toArray());
		$this->set('form', $this->buildEmailChangeForm());
	}

	/**
	 *	@role login
	 */
	public function doChangeEmailAction()
	{
		if (!$this->buildEmailChangeValidator()->isValid())
		{
			return $this->response->redirect('user/changeEmail');
		}

		$this->user->email->set($this->request->get('email'));
		$this->user->save();

		$this->session->set('userConfirm', $this->translate('_confirm_email_change'));

		return $this->response->redirect('user/index');
	}

	/**
	 *	@role login
	 */
	public function personalAction()
	{
		$this->addAccountBreadcrumb();
		$this->addBreadcrumb($this->translate('_personal_info'), '');

		$form = $this->buildPersonalDataForm($this->user);
		$this->set('form', $form);
		$this->user->getSpecification()->setFormResponse($response, $form);

	}

	/**
	 *	@role login
	 */
	public function savePersonalAction()
	{
		$validator = $this->buildPersonalDataValidator($this->user);
		if (!$validator->isValid())
		{
			return $this->response->redirect('user/personal');
		}

		$this->user->loadRequestData($this->request, array('firstName', 'lastName', 'companyName'));
		$this->user->save();

		$this->setMessage($this->translate('_personal_data_saved'));

		return $this->response->redirect('user/index');
	}

	/**
	 *	@role login
	 */
	public function addressesAction()
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();


		$this->set('user', $this->user->toArray());
		$this->set('billingAddresses', $this->user->getBillingAddressArray());
		$this->set('shippingAddresses', $this->user->getShippingAddressArray());
	}

	/**
	 *	@role login
	 */
	public function viewOrderAction()
	{
		$id = $this->request->get('id');
		if ($order = $this->user->getorderBy($id))
		{
			$this->addAccountBreadcrumb();
			$this->addBreadCrumb($this->translate('_your_orders'), $this->url->get('user/orders'));
			$this->addBreadCrumb($order->invoiceNumber, '');

			// mark all notes as read
			$notes = $order->getNotes();
			foreach ($notes as $note)
			{
				if (!$note->isRead && $note->isAdmin)
				{
					$note->isRead->set(true);
					$note->save();
				}
			}

			$response = null;
			$orderArray = $order->toArray();

			if ($order->isRecurring == true)
			{
				// find invoices
				$page = $this->request->get('page', 1);
				$perPage = $this->getOrdersPerPage();
				$f = $this->getOrderListPaginateFilter($page, $perPage);
				$f->andWhere(
					new AndChainCondition(array(
						new EqualsCond(new ARFieldHandle('CustomerOrder','parentID'), $order->getID()),
						new EqualsCond(new ARFieldHandle('CustomerOrder','isRecurring'), 1)
					))
				);
				$f->orderBy(new ARFieldHandle('CustomerOrder','dateDue'));
				$response = $this->getOrdersListResponse($this->loadOrders($f), $page, $perPage);

				$recurringProductPeriods = array();
				foreach ($order->getShipments() as $shipment)
				{
					foreach ($shipment->getItems() as $orderedItem)
					{
						$recurringProductPeriods[$orderedItem->getID()] =  RecurringItem::getInstanceByOrderedItem($orderedItem) -> toArray();
					}
				}
												$this->set('nextRebillDate', $order->getNextRebillDate());
				$this->set('periodTypesPlural', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL));
				$this->set('periodTypesSingle', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_SINGLE));
				$this->set('recurringProductPeriodsByItemId', $recurringProductPeriods);
				$this->loadLanguageFile('Product');
			}

			if (!$response)
			{

			}

			$this->set('subscriptionStatus', $order->getSubscriptionStatus());
			$this->set('canCancelRebills', $order->canUserCancelRebills());
			$this->set('order', $orderArray);
			$this->set('notes', $notes->toArray());
			$this->set('user', $this->user->toArray());
			$this->set('noteForm', $this->buildNoteForm());

		}
		else
		{
			return $this->response->redirect('user/index');
		}
	}

	/**
	 *	@role login
	 */
	public function reorderAction()
	{
		$order = $this->user->getorderBy($this->request->get('id'));
		if ($order)
		{
						$newOrder = clone $order;
			SessionOrder::save($newOrder);
			return $this->response->redirect('order/index');
		}
		else
		{
			return $this->response->redirect('user/index');
		}
	}

	/**
	 *	@role login
	 */
	public function addNoteAction()
	{

		$f = query::query()->where('CustomerOrder.ID = :CustomerOrder.ID:', array('CustomerOrder.ID' => $this->request->get('id')));
		$f->andWhere('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$set = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
		if (!$set->count() || !$this->buildNoteValidator()->isValid())
		{
			return $this->response->redirect('user/index');
		}

		$order = $set->shift();
		$note = OrderNote::getNewInstance($order, $this->user);
		$note->text->set($this->request->get('text'));
		$note->isAdmin->set(false);
		$note->save();

		if ($this->config->get('NOTIFY_NEW_NOTE'))
		{
			$order->user->load();

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
	public function orderInvoiceAction()
	{
		$this->addAccountBreadcrumb();
		$this->application->setTheme('');

		$order = $this->getorderBy($this->request->get('id'));

		if (!$order)
		{
			return $this->response->redirect('user/index');
		}


		$this->set('order', $order->toArray(array('payments' => true)));
		$this->set('user', $this->user->toArray());
	}

	public function registerAction()
	{
		if ($this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return $this->response->redirect('user/registerAddress');
		}

		$this->setValidator($this->buildRegValidator());

		//$this->sessionUser->getAnonymousUser()->getSpecification()->setFormResponse($response, $form);
	}

	public function registerAddressAction()
	{
		$this->request->set('return', 'user/index');
		$response = $this->checkout();
		$response->get('form')->set('regType', 'register');
	}

	public function doRegisterAction()
	{
		$this->sessionUser->destroy();

		$validator = $this->buildRegValidator();
		$validator->validate($_REQUEST);

		if ($validator->getErrors())
		{
			return $this->returnErrors('user/register', $validator);
		}

		if (!$this->createUser($this->request->get('password')))
		{
			return $this->response->redirect('user/register');
		}

//		$this->mergeorderBy();

		if (!$this->config->get('REG_EMAIL_CONFIRM'))
		{
			if ($this->request->get('return'))
			{
				return new RedirectResponse($this->request->get('return'));
			}
			else
			{
				return $this->response->redirect('user/index');
			}
		}
		else
		{
			return $this->response->redirect('user/unconfirmed');
		}
	}

	public function unconfirmedAction()
	{

	}

	public function confirmAction()
	{
		$success = false;

		$email = $this->request->get('email');
		$email = str_replace(' ', '+', $email);
		$user = User::getInstanceByEmail($email);
		
		if (!$user)
		{
			foreach (User::query()->where('COALESCE(email, "") = "" AND preferences LIKE :code:', array('code' => '%' . $this->request->get('code') . '%'))->execute() as $acc)
			{
				if ($acc->getPreference('email') == $email)
				{
					$user = $acc;
				}
			}
		}
		
		if ($user && $user->getPreference('confirmation'))
		{
			if ($this->request->get('code') == $user->getPreference('confirmation'))
			{
				$user->setPreference('confirmation', null);
				$user->isEnabled = true;
				
				if (!$user->email && ($email = $user->getPreference('email')))
				{
					$user->email = $user->getPreference('email');
				}
				
				$user->save();

				$this->sessionUser->setUser($user);

				$success = true;

				//$this->sendWelcomeEmail($user);
			}
		}

		$this->set('success', $success);
		$this->set('ret', $this->request->get('ret'));
	}

	/**
	 *  Login form
	 */
	public function loginAction()
	{
		if ($this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return $this->response->redirect('user/registerAddress');
		}

		$this->addBreadCrumb($this->translate('_login'), $this->url->get('user/login'));

		$form = $this->buildRegForm();

		$this->set('regForm', $form);
		$this->set('email', $this->request->get('email'));
		$this->set('failed', $this->request->get('failed'));
		$this->set('return', $this->request->get('return'));

		//$this->sessionUser->getAnonymousUser()->getSpecification()->setFormResponse($response, $form);
	}

	/**
	 *  Process actual login
	 */
	public function doLoginAction()
	{
		$user = User::getInstanceByLogin($this->request->get('email'), $this->request->get('password'));
		if (!$user)
		{
			return $this->response->redirect('user/login?failed=true');
		}

		// login
		$this->sessionUser->setUser($user);

		$this->user = $user;
		$this->mergeorderBy();

		if ($return = $this->request->get('return'))
		{
			return $this->response->redirect($return, true);
		}
		else
		{
			return $this->response->redirect('user/index');
		}
	}

	private function mergeorderBy()
	{
		return;
		// load the last un-finalized order by this user
		$f = query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$f->andWhere(new NotEqualsCond('CustomerOrder.isFinalized', true));
		$f->orderBy('CustomerOrder.dateCreated', 'DESC');
		$f->limit(1);
		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);

		if (!$this->order->user || $this->order->user->getID() == $this->user->getID())
		{
			if ($s->count())
			{
				$order = $s->shift();
				if ($this->order->getID() != $order->getID())
				{
					$sessionOrder = SessionOrder::getorderBy();
					$order->loadItems();
					$order->merge($sessionOrder);
					$order->save();
					SessionOrder::orderBy($order);
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

	public function remindPasswordAction()
	{
		//$this->addBreadCrumb($this->translate('_remind_password'), $this->url->get('user/login'));
		$this->setValidator($this->buildPasswordReminderValidator());
		$this->set('ret', $this->request->get('return'));
	}

	public function doRemindPasswordAction()
	{
		$email = $this->request->get('email') ? $this->request->get('email') : $this->request->getJson('email');
		$user = \user\User::getInstanceByEmail($email);
		if ($user && $user->isEnabled && !$user->getPreference('confirmation'))
		{
			$user->setPassword($user->getAutoGeneratedPassword());
			$user->save();

			$email = new Email($this->getDI());
			$email->setUser($user);
			$email->setTemplate('user/remindPassword');
			$email->send();
		}

		return $this->response->redirect('user/remindComplete?email=' . $email);
	}

	public function remindCompleteAction()
	{

		$this->set('email', $this->request->get('email'));
	}

	public function logoutAction()
	{
		$this->sessionUser->destroy();
		return $this->response->redirect('');
	}

	public function checkoutAction()
	{
		if ($this->config->get('DISABLE_GUEST_CHECKOUT') && !$this->config->get('REQUIRE_REG_ADDRESS'))
		{
			return new ActionRedirectResponse('user', 'login', array('query' => array('return' => $this->url->get('checkout/pay'))));
		}

		$form = $this->buildForm();

		$form->set('billing_country', $this->config->get('DEF_COUNTRY'));
		$form->set('shipping_country', $this->config->get('DEF_COUNTRY'));
		$form->set('return', $this->request->get('return'));


		$this->set('form', $form);
		$this->set('countries', $this->getCountryList($form));
		$this->set('states', $this->getStateList($form->get('billing_country')));
		$this->set('shippingStates', $this->getStateList($form->get('shipping_country')));
		$this->set('order', $this->order->toArray());

		$this->sessionUser->getAnonymousUser()->getSpecification()->setFormResponse($response, $form);
		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, 'billing_');
		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, 'shipping_');

	}

	public function processCheckoutRegistrationAction()
	{
		ActiveRecordModel::beginTransaction();

		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			$action = $this->request->get('regType') == 'register' ? 'registerAddress' : 'checkout';
			return new ActionRedirectResponse('user', $action, array('query' => array('return' => $this->request->get('return'))));
		}

		// create user account
		$user = $this->createUser($this->request->get('password'), 'billing_');

		// create billing and shipping address
		$address = $this->createAddress('billing_');
		$billingAddress = BillingAddress::getNewInstance($user, $address);
		$billingAddress->save();

		$shippingAddress = ShippingAddress::getNewInstance($user, $this->request->get('sameAsBilling') ? clone $address : $this->createAddress('shipping_'));
		$shippingAddress->save();

		$user->defaultShippingAddress->set($shippingAddress);
		$user->defaultBillingAddress->set($billingAddress);
		$user->save();

		// set order addresses
		$this->order->billingAddress->set($billingAddress->userAddress);

		$this->order->loadItems();
		if ($this->order->isShippingRequired())
		{
			$this->order->shippingAddress->set($shippingAddress->userAddress);
		}

		$this->order->save();
		$this->order->setUser($user);
		SessionOrder::save($this->order);

		ActiveRecordModel::commit();

		if ($return = $this->request->get('return'))
		{
			return new RedirectResponse($this->router->createUrlFromRoute($return));
		}
		else
		{
			return $this->response->redirect('checkout/shipping');
		}
	}

	private function createAddress($prefix)
	{
		// get address state
		if ($this->request->get($prefix . 'state_select'))
		{
			try
			{
				$state = State::getInstanceByID($this->request->get($prefix . 'state_select'), ActiveRecordModel::LOAD_DATA);
			}
			catch (Exception $e)
			{
				throw new ApplicationException('State not found');
			}

			$country = $state->countryID;
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
	public function deleteShippingAddressAction()
	{
		try
		{
			return $this->deleteAddress(ShippingAddress::getUserAddress($this->request->get('id'), $this->user));
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
		}
	}

	/**
	 *	@role login
	 */
	public function deleteBillingAddressAction()
	{
		try
		{
			return $this->deleteAddress(BillingAddress::getUserAddress($this->request->get('id'), $this->user));
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
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
	public function editShippingAddressAction()
	{
		try
		{
			$response = $this->editAddress(ShippingAddress::getUserAddress($this->request->get('id'), $this->user));
			$this->addBreadCrumb($this->translate('_edit_shipping_address'), '');
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
		}
	}

	/**
	 *	@role login
	 */
	public function editBillingAddressAction()
	{
		try
		{
			$response = $this->editAddress(BillingAddress::getUserAddress($this->request->get('id'), $this->user));
			$this->addBreadCrumb($this->translate('_edit_shipping_address'), '');
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
		}
	}

	private function editAddress(UserAddressType $addressType)
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();

		$form = $this->buildAddressForm();
		$address = $addressType->userAddress;

		$form->setData($address->toArray());
		$form->set('country', $address->countryID);
		$form->set('state_text', $address->stateName);

		if ($address->state)
		{
			$form->set('state_select', $address->state->getID());
		}


		$this->set('form', $form);
		$this->set('return', $this->request->get('return'));
		$this->set('countries', $this->getCountryList($form));
		$this->set('states', $this->getStateList($form->get('country')));
		$this->set('address', $address->toArray());
		$this->set('addressType', $addressType->toArray());

		$address->getSpecification()->setFormResponse($response, $form);

	}

	/**
	 *	@role login
	 */
	public function saveShippingAddressAction()
	{
		try
		{
			$address = ShippingAddress::getUserAddress($this->request->get('id'), $this->user);
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
		}

		return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editShippingAddress', array('id' =>$this->request->get('id'), 'query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *	@role login
	 */
	public function saveBillingAddressAction()
	{
		try
		{
			$address = BillingAddress::getUserAddress($this->request->get('id'), $this->user);
		}
		catch (ARNotFoundException $e)
		{
			return $this->response->redirect('user/index');
		}

		return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editBillingAddress', array('id' =>$this->request->get('id'), 'query' => array('return' => $this->request->get('return')))));
	}

	private function doSaveAddress(UserAddressType $address, ActionRedirectResponse $invalidResponse)
	{
		$address = $address->userAddress;
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
	public function addBillingAddressAction($shipping = false)
	{
		$this->addAccountBreadcrumb();
		$this->addAddressBreadcrumb();
   		if (!$shipping)
   		{
			$this->addBreadCrumb($this->translate('_add_billing_address'), '');
		}

		$form = $this->buildAddressForm();

		$form->set('firstName', $this->user->firstName);
		$form->set('lastName', $this->user->lastName);
		$form->set('companyName', $this->user->companyName);

		$this->user->loadAddresses();

		if ($this->user->defaultBillingAddress)
		{
			$form->set('country', $this->user->defaultBillingAddress->userAddress->countryID);
			$form->set('phone', $this->user->defaultBillingAddress->userAddress->phone);
		}
		else
		{
			$form->set('country', $this->config->get('DEF_COUNTRY'));
		}


		$this->set('form', $form);
		$this->set('return', $this->request->get('return'));
		$this->set('countries', $this->getCountryList($form));
		$this->set('states', $this->getStateList($form->get('country')));

		UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, '');

	}

	/**
	 *	@role login
	 */
	public function addShippingAddressAction()
	{
		$response = $this->addBillingAddress(true);
		$this->addBreadCrumb($this->translate('_add_shipping_address'), '');
	}

	/**
	 *	@role login
	 */
	public function doAddBillingAddressAction()
	{
		return $this->doAddAddress('BillingAddress', new ActionRedirectResponse('user', 'addBillingAddress', array('query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *	@role login
	 */
	public function doAddShippingAddressAction()
	{
		return $this->doAddAddress('ShippingAddress', new ActionRedirectResponse('user', 'addShippingAddress', array('query' => array('return' => $this->request->get('return')))));
	}

	/**
	 *  Return a list of states for the selected country
	 *  @return JSONResponse
	 */
	public function statesAction()
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
	public function downloadAction()
	{
		// get and validate OrderedItem instance first
		$f = query::query()->where('OrderedItem.ID = :OrderedItem.ID:', array('OrderedItem.ID' => $this->request->get('id')));
		$f->andWhere('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));

		$s = ActiveRecordModel::getRecordSet('OrderedItem', $f, array('CustomerOrder', 'Product'));

		// OrderedItem does not exist
		if (!$s->count())
		{
			return $this->response->redirect('user/index');
		}

		$item = $s->shift();
		$file = $item->getFileByID($this->request->get('fileID'));

		// file does not exist for OrderedItem
		if (!$file)
		{
			return $this->response->redirect('user/index');
		}

		OrderedFile::getInstance($item, $file)->registerDownload();

		// download expired
		if (!$item->isDownloadable($file))
		{
			$this->setMessage($this->translate('_download_limit_reached'));
			return $this->response->redirect('user/index');
		}

		return new ObjectFileResponse($file);
	}

	/**
	 *	Make payment for unpaid or partially paid order
	 *	@role login
	 */
	public function payAction()
	{
		$this->loadLanguageFile('Checkout');
		$this->addAccountBreadcrumb();
		$this->addBreadCrumb($this->translate('_pay'), '');

		$order = $this->getorderBy($this->request->get('id'));

		if (!$order || $order->isPaid)
		{
			return $this->response->redirect('user/index');
		}


		$this->set('order', $order->toArray(array('payments' => true)));
		$this->set('id', $order->getID());

		$checkout = new CheckoutController($this->application);
		$checkout->setPaymentMethodResponse($response, $order);

	}

	/**
	 *	@return User
	 */
	private function createUser($password = '')
	{
		$user = User::getNewInstance($this->request->get('email'), $this->request->get('password'));
		$user->isEnabled = !$this->config->get('REG_EMAIL_CONFIRM');
		$user->photoReviewed = 1;

		// custom fields
		$user->loadRequestData($this->request, true);

		if ($password)
		{
			$this->session->set('password', $password);
			$user->setPassword($password);
		}

		$user->photoReviewed = 1;
		$res = $user->save();

		if (!$res)
		{
			$this->setErrors($user->getMessages(), 'user/register');
			return false;
		}

		if (!$this->config->get('REG_EMAIL_CONFIRM'))
		{
			$this->sessionUser->setUser($user);
			//$this->sendWelcomeEmail($user);
		}
		else
		{
			$code = rand(1, 10000000) . rand(1, 10000000);
			$user->setPreference('confirmation', $code);
			$user->save();

			$email = new Email($this->getDI());
			$email->setUser($user);
			$email->set('code', $code);
			$email->set('ret', $this->request->get('ret'));
			$email->setTemplate('user/confirm');
			$email->send();
		}

		return $user;
	}

	public function sendWelcomeEmailAction(User $user)
	{
		// send welcome email with user account details
		if ($this->config->get('EMAIL_NEW_USER'))
		{
			if ($this->session->get('password'))
			{
				$user->setPassword($this->session->get('password'));
			}

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

		}
		else
		{
			return $failureResponse;
		}
	}

	/**************  VALIDATION ******************/
	private function buildPasswordReminderValidator()
	{
		$validator = $this->getValidator("remindPassword", $this->request);
		$validator->add('email', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_email'))));
		$validator->add('email', new Validator\Email(array('message' => $this->translate('_err_invalid_email'))));

		return $validator;
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

		$validator = $this->getValidator("passwordChange", $this->request);
		$validator->add('currentpassword', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_current_password'))));
		$validator->add('currentpassword', new IsPasswordCorrectCheck($this->translate('_err_incorrect_current_password'), $this->user));

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
//		return new Form($this->buildRegValidator());
	}

	private function buildRegValidator()
	{
		$validator = $this->getValidator("userRegistration", $this->request);
		$this->validateName($validator);
		$this->validateEmail($validator);
		$this->validatePassword($validator);

		//$this->sessionUser->getAnonymousUser()->getSpecification()->setValidation($validator);

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

		if (!$this->config->get('REQUIRE_SAME_ADDRESS') && $this->order->isShippingRequired())
		{
			$this->validateAddress($validator, 'shipping_', true);
		}

		$this->sessionUser->getAnonymousUser()->getSpecification()->setValidation($validator);

		return $validator;
	}

	private function validateName(\Phalcon\Validation $validator, $fieldPrefix = '', $orCheck = false)
	{
		$validation = array('firstName' => '_err_enter_first_name',
							'lastName' => '_err_enter_last_name');

		$displayedFields = $this->config->get('USER_FIELDS');

		if (empty($displayedFields['FIRSTNAME']))
		{
			unset($validation['firstName']);
		}
		if (empty($displayedFields['LASTNAME']))
		{
			unset($validation['lastName']);
		}

		foreach ($validation as $field => $error)
		{
			$field = $fieldPrefix . $field;
			$check = new Validator\PresenceOf(array('message' => $this->translate($error)));
			//$check = $orCheck ? new OrCheck(array($field, 'sameAsBilling'), array($check, new Validator\PresenceOf()), $this->request) : $check;
			$validator->add($field, $check);
		}
	}

	private function validateEmail(\Phalcon\Validation $validator, $uniqueError = '_err_not_unique_email')
	{
		$validator->add('email', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_email'))));
		$validator->add('email', new Validator\Email(array('message' => $this->translate('_err_invalid_email'))));
		$validator->setFilters('email', 'email');

		$emailErr = $this->translate($uniqueError);
		$emailErr = str_replace('%1', $this->url->get('user/login', array('email' => $this->request->get('email'))), $emailErr);
		$validator->add('email', new \helper\check\IsUniqueEmailCheck(array('message' => $emailErr)));
	}

	public function validateAddress(\Phalcon\Validation $validator, $fieldPrefix = '')
	{
		$this->validateName($validator, $fieldPrefix);

		$checks = array();

		$displayedFields = $this->config->get('USER_FIELDS');

		if ($this->config->get('REQUIRE_PHONE') && !empty($displayedFields['PHONE']))
		{
			$checks['phone'] = new Validator\PresenceOf(array('message' => $this->translate('_err_enter_phone')));
		}

		if (!empty($displayedFields['ADDRESS1']))
		{
			$checks['address1'] = new Validator\PresenceOf(array('message' => $this->translate('_err_enter_address')));
		}

		if (!empty($displayedFields['CITY']))
		{
			$checks['city'] = new Validator\PresenceOf(array('message' => $this->translate('_err_enter_city')));
		}

		if (!empty($displayedFields['COUNTRY']))
		{
			$checks['country'] = new Validator\PresenceOf(array('message' => $this->translate('_err_select_country')));
		}

		if (!empty($displayedFields['POSTALCODE']))
		{
			$checks['postalCode'] = new Validator\PresenceOf(array('message' => $this->translate('_err_enter_zip')));
		}
		
		foreach ($checks as $field => $check)
		{
			$validator->add($fieldPrefix . $field, $check);
		}

/*
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
			$check = $orCheck ? new OrCheck(array($field, 'sameAsBilling'), array($checks[$key], new Validator\PresenceOf()), $this->request) : $checks[$key];
			$validator->add($field, $check);
		}

		if (!empty($displayedFields['STATE']))
		{
			$fieldList = array($fieldPrefix . 'state_select', $fieldPrefix . 'state_text');
			$checkList = array(new Validator\PresenceOf(array('message' => $this->translate('_err_select_state')), new Validator\PresenceOf()));
			if ($orCheck)
			{
				$fieldList[] = 'sameAsBilling';
				$checkList[] = new Validator\PresenceOf();
			}
			$stateCheck = new OrCheck($fieldList, $checkList, $this->request);
			$validator->add($fieldPrefix . 'state_select', $stateCheck);
		}
		*/
	}

	private function validatePassword(\Phalcon\Validation $validator)
	{
		$validator->add('password', new Validator\StringLength(array(
			'min' => self::PASSWORD_MIN_LENGTH,
			'messageMinimum' => sprintf($this->translate('_err_short_password'), self::PASSWORD_MIN_LENGTH)
			)));
		$validator->add('password', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_password'))));
		$validator->add('confpassword', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_password'))));
		$validator->add('confpassword', new helper\check\PasswordMatchCheck(array('message' => $this->translate('_err_password_match'), 'field1' => 'password', 'field2' => 'confpassword')));
	}

	private function buildNoteForm()
	{
		return new Form($this->buildNoteValidator());
	}

	private function buildNoteValidator()
	{
		$validator = $this->getValidator("orderNote", $this->request);
		$validator->add('text', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_note'))));
		$validator->addFilter('text', new HtmlSpecialCharsFilter);
		return $validator;
	}

	private function getorderBy($id)
	{
		$f = query::query()->where('CustomerOrder.ID = :CustomerOrder.ID:', array('CustomerOrder.ID' => $id));
		$f->andWhere('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $this->user->getID()));
		$f->andWhere('CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => true));
		$f->andWhere('CustomerOrder.isCancelled = :CustomerOrder.isCancelled:', array('CustomerOrder.isCancelled' => 0));

		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, array('User'));
		if ($s->count())
		{
			$order = $s->shift();
			$order->loadAll();

			if ($order->user)
			{
				$order->user->getSpecification();
			}

			return $order;
		}
	}

	public function invoicesMenuBlockAction()
	{
		if ($this->user->isAnonymous())
		{
			return;
		}
		$response = new BlockResponse();
		$hasPendingInvoices = $this->user->hasPendingInvoices();
		$this->set('hasPendingInvoices', $hasPendingInvoices);
		$this->set('hasInvoices', $hasPendingInvoices ? true : $this->user->hasInvoices()); // if user has pending invoice, he invoices (at least one pending invoice)

	}

	public function cancelFurtherRebillsAction()
	{
												$request = $this->getRequest();
		$id = $request->get('id');

		$page = $request->get('page');
		$params = array('id'=>$id);
		if ($page > 1)
		{
			$params['query'] = array('page'=>$page);
		}

		$status = false;
		$order = CustomerOrder::getInstanceById($id, true);
		$userID = $this->user->getID();
		if(
			$order->userID->getID()
			&& $order->userID->getID() == $userID
			&& true == $order->canUserCancelRebills()
		) {
			$status = $order->cancelRecurring($this->getRequestCurrency());
			if ($status != false)
			{
				$order->cancelFurtherRebills();
			}
		}
		if ($status == false)
		{

			$this->setErrorMessage($this->translate('_cannot_cancel_subscription_contantc_store_administrator'));
		}
		return new ActionRedirectResponse('user', 'viewOrder', $params);
	}

	public function generateTestInvoicesAction()
	{
		return ;


		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_PAYMENT_DUE_DATE_DAYS', 7);
		$config->save();

		// data
		$userID = 110;
		$product1ID = 339;
		$recurringProductPeriodID = 19;
		// ~

		// create first order
		$user = User::getInstanceByID($userID, true);
		$product1 = Product::getInstanceByID($product1ID, true);
		$order = CustomerOrder::getNewInstance($user);
		$order->save(true);
		$rpp = RecurringProductPeriod::getInstanceByID($recurringProductPeriodID);

		$item = $order->addProduct($product1, 1, true);
		$item->save();
		$recurringItem = RecurringItem::getNewInstance($rpp, $item);
		$recurringItem->setupPrice->set(100);
		$recurringItem->periodPrice->set(25);
		$recurringItem->save();
		$order->finalize();

		// generate invoices
		echo '<pre>Invoices for {CustomerOrder ID:'.$order->getID().'}:',"\n";
		$now = time();
		for ($ts = $now; $ts < strtotime('+20 months', $now); $ts = $ts + 60 * 60 * 24)
		{
			$z = CustomerOrder::generateRecurringInvoices(date('Y-m-d', $ts));
			foreach ($z as $id)
			{
				echo '{CustomerOrder ID:'.$id.'}',"\n";
			}
		}
		die('-done-</pre>');
	}
}

?>
