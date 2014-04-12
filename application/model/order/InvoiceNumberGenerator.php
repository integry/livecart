<?php

namespace order;

/**
 * Defines abstract interface for invoice ID number generator classes
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
abstract class InvoiceNumberGenerator
{
	protected $order;

	public function __construct(CustomerOrder $order)
	{
		$this->order = $order;
	}

	public static function getGenerator(CustomerOrder $order)
	{
		$class = $order->getDI()->get('config')->get('INVOICE_NUMBER_GENERATOR');
		self::loadGeneratorClass($class, $order);
		$class = '\order\invoiceNumber\\' . $class;

		return new $class($order);
	}

	public static function getGeneratorClasses()
	{
		$files = self::getGeneratorFiles();
		unset($files['SequentialInvoiceNumber']);

		$classes = array_keys($files);
		array_unshift($classes, 'SequentialInvoiceNumber');

		return $classes;
	}

	private static function loadGeneratorClass($class, CustomerOrder $order)
	{
		$files = self::getGeneratorFiles($order);
		if (isset($files[$class]))
		{
			include_once $files[$class];
		}
	}

	private static function getGeneratorFiles(CustomerOrder $order)
	{
		$files = array();
		foreach ($order->getDI()->get('application')->getConfigContainer()->getDirectoriesByMountPath('application/model/order/invoiceNumber') as $dir)
		{
			foreach (glob($dir . '/*.php') as $file)
			{
				$files[basename($file, '.php')] = $file;
			}
		}

		return $files;
	}
	
	protected function getConfig()
	{
		return $this->order->getDI()->get('config');
	}

	public abstract function getNumber();
}

?>
