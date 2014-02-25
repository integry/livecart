<?php

/**
 *
 * @package application/helper
 * @author Integry Systems
 */
class LiveCartValidator extends \Phalcon\Validation
{
	protected $isPluginProcessed = false;

	protected $restoredValues = array();

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function appendMessage($message)
	{
		if (!is_array($this->_messages))
		{
			$this->_messages = array();
		}

		$this->_messages[] = $message;
	}

	public function getFieldMessages($field)
	{
		$messages = array();
		foreach ((array)$this->getMessages() as $message)
		{
			if ($message->getField() == $field)
			{
				$messages[] = $message;
			}
		}

		return $messages;
	}

	public function getValidators($field)
	{
		$validators = array();
		if ($this->_validators)
		{
			foreach ($this->_validators as $validator)
			{
				if ($validator[0] == $field)
				{
					$validators[] = $validator[1];
				}
			}
		}

		return $validators;
	}

	public function setRestoredValues($values)
	{
		$this->restoredValues = $values;
	}

	public function setRestoredValue($key, $value)
	{
		$this->restoredValues[$key] = $value;
	}

	public function getRestoredValue($key)
	{
		if (!empty($this->restoredValues) && !empty($this->restoredValues[$key]))
		{
			return $this->restoredValues[$key];
		}
	}

	public function getRestoredAngularValue($key)
	{
		return htmlentities(json_encode($this->getRestoredValue($key)));
	}

	public function getAngularValues()
	{
		if (!empty($this->restoredValues))
		{
			return json_encode($this->restoredValues);
		}
	}

	public function getAngularErrType(\Phalcon\Validation\Validator $validator)
	{
		if ($validator instanceof AngularValidation)
		{
			return $validator->getAngularErrType();
		}

		$parts = explode('\\', get_class($validator));
		$class = end($parts);
		$map = array('PresenceOf' => 'required',
					 'Email' => 'email'
					 );
		if (!empty($map[$class]))
		{
			return $map[$class];
		}

		return $class;

		return 'required';
	}

	public function getAngularValidation(\Phalcon\Validation\Validator $validator)
	{
		if ($validator instanceof AngularValidation)
		{
			return $validator->getAngularValidation();
		}

		$errType = $this->getAngularErrType($validator);
		return 'ng-' . $errType . '="true"';
	}

	public function hasValidator($field, $type = null)
	{
		$validators = $this->getValidators($field);
		if ($type)
		{
			$filtered = array();
			foreach ($validators as $val)
			{
				$parts = explode('\\', get_class($val));
				$class = array_pop($parts);
				if ($class == $type)
				{
					$filtered[] = $val;
				}
			}
			$validators = $filtered;
		}

		return $validators;
	}

	public function hasFilter($field, $type)
	{
		$filters = (array)$this->getFilters($field);
		return array_search($type, $filters) !== false;
	}

	public function validate($data = null, $entity = null)
	{
		$this->processPlugins();
		return parent::validate($data, $entity);
	}

	public function setErrorMessage($field, $message)
	{
		$valMessage = new \Phalcon\Validation\Message();
		$valMessage->setField($field);
		$valMessage->setMessage($message);
		$this->appendMessage($valMessage);
	}

	public function getErrors()
	{
		$errors = array();
		foreach ($this->getMessages() as $message)
		{
			$errors[$message->getField()][] = $message->getMessage();
		}

		return $errors;
	}

	public function processPlugins()
	{
		if (!$this->isPluginProcessed)
		{
			foreach ($this->application->getPlugins('validator/' . $this->getName()) as $plugin)
			{
				include_once $plugin['path'];
				$inst = new $plugin['class']($this, $this->getDI());
				$inst->process();
			}
		}

		$this->isPluginProcessed = true;
	}
}

?>
