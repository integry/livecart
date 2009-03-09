<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.OrderNote");

/**
 * Manage order notes (communication with customer)
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role order
 */
class OrderNoteController extends StoreManagementController
{
	public function index()
	{
		$order = CustomerOrder::getInstanceById($this->request->get('id'));

		$notes = $order->getNotes();
		foreach ($notes as $note)
		{
			if (!$note->isRead->get() && !$note->isAdmin->get())
			{
				$note->isRead->set(true);
				$note->save();
			}
		}

		$response = new ActionResponse();
		$response->set('form', $this->buildOrderNoteForm());
		$response->set('order', $order->toArray());
		$response->set('notes', $notes->toArray());
		return $response;
	}

	public function view()
	{
		return new ActionResponse('note', ActiveRecordModel::getInstanceById('OrderNote', $this->request->get('id'), OrderNote::LOAD_DATA, OrderNote::LOAD_REFERENCES)->toArray());
	}

	public function add()
	{
		if ($this->buildOrderNoteValidator()->isValid())
		{
			$order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA);

			$note = OrderNote::getNewInstance($order, $this->user);
			$note->isAdmin->set(true);
			$note->text->set($this->request->get('comment'));
			$note->save();

			if ($this->config->get('EMAIL_ORDERNOTE'))
			{
				$order->user->get()->load();

				$email = new Email($this->application);
				$email->setUser($order->user->get());
				$email->setTemplate('order.message');
				$email->set('order', $order->toArray(array('payments' => true)));
				$email->set('message', $note->toArray());
				$email->send();
			}

			return new ActionRedirectResponse('backend.orderNote', 'view', array('id' => $note->getID()));
		}
		else
		{
			return new RawResponse('invalid');
		}
	}

	private function buildOrderNoteForm()
	{
		return new Form($this->buildOrderNoteValidator());
	}

	private function buildOrderNoteValidator()
	{
		$validator = $this->getValidator("orderNote", $this->request);
		$validator->addCheck('comment', new IsNotEmptyCheck($this->translate('_err_enter_text')));
		return $validator;
	}
}

?>