<?php


ClassLoader::importNow("application/helper/getDateFromString");
ClassLoader::importNow("library/openFlashChart/open-flash-chart");

/**
 * Generate reports and stats
 *
 * @package application/controller/backend
 * @author	Integry Systems
 */
class ReportController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function indexAction()
	{

		$this->set('thisMonth', date('m'));
		$this->set('lastMonth', date('Y-m', strtotime(date('m') . '/15 -1 month')));
	}

	public function salesAction()
	{
		$report = new SalesReport();
		$this->initReport($report);

		$this->loadLanguageFile('backend/CustomerOrder');
		$this->application->loadLanguageFiles();

		$type = $this->getOption('sales', 'number_orders');

		switch ($type)
		{
			case 'number_orders':
				$report->getOrderCounts();
				$report->setYLegend($this->translate('_num_orders'));
				break;
			case 'number_items':
				$report->getOrderedItemCounts();
				$report->setYLegend($this->translate('_num_items'));
				break;
			case 'total_orders':
				$report->setYLegend($this->translate('CustomerOrder.totalAmount') . ' (' . $this->application->getDefaultCurrencyCode() . ')');
				$report->getOrderTotals();
				break;
			case 'avg_total':
				$report->setYLegend($this->translate('CustomerOrder.totalAmount') . ' (' . $this->application->getDefaultCurrencyCode() . ')');
				$report->getAvgOrderTotals();
				break;
			case 'avg_items':
				$report->setYLegend($this->translate('_num_items'));
				$report->getAvgItemCounts();
				break;
			case 'payment_methods':
				$this->loadLanguageFile('backend/Settings');
				$this->application->loadLanguageFiles();
				$report->getPaymentMethodCounts($this);
				break;
			case 'currencies':
				$report->getCurrencyCounts();
				break;
			case 'status':
				$this->loadLanguageFile('User');
				$this->application->loadLanguageFiles();
				$report->getStatuses();
				break;
			case 'cancelled':
				$report->setYLegend($this->translate('_ratio_percent') . ' (%)');
				$report->getCancelledRatio();
				break;
			case 'unpaid':
				$report->setYLegend($this->translate('_ratio_percent') . ' (%)');
				$report->getUnpaidRatio();
				break;
		}

		$response = $this->getChartResponse($report);
		$this->set('type', $type);
	}

	public function bestsellersAction()
	{
		$report = new BestsellerReport();
		$this->initReport($report);

		$type = $this->getOption('bestsellers', 'number_items');

		switch ($type)
		{
			case 'number_items':
				$report->getBestsellersByCount();
				break;
			case 'total_items':
				$report->getBestsellersByTotal();
				$this->locale->translationManager()->setDefinition('_cnt', $this->translate('_total_amount') . ' (' . $this->application->getDefaultCurrencyCode() . ')');
				break;
		}

		$response = $this->getChartResponse($report);
		$this->set('type', $type);
	}

	public function customersAction()
	{
		$report = new CustomerReport();
		$this->initReport($report);

		$type = $this->getOption('customers', 'register_date');

		switch ($type)
		{
			case 'register_date':
				$report->setYLegend($this->translate('_num_customers'));
				$report->getCustomerCounts();
				break;
			case 'countries':
				$report->getCountries();
				break;
			case 'top_cust':
				$this->locale->translationManager()->setDefinition('_cnt', $this->translate('_total_amount') . ' (' . $this->application->getDefaultCurrencyCode() . ')');
				$report->getTopCustomers();
				break;
		}

		$response = $this->getChartResponse($report);
		$this->set('type', $type);
	}

	public function conversionAction()
	{
		$report = new ConversionReport();
		$this->initReport($report);

		$type = $this->getOption('conversion', 'ratio');

		switch ($type)
		{
			case 'ratio':
				$report->setYLegend($this->translate('_conv_ratio'));
				$report->getConversionRatio();
				break;
			case 'checkout':
				$this->loadLanguageFile('backend/CustomerOrder');
				$this->application->loadLanguageFiles();
				$report->getCheckoutSteps();
				break;
			case 'created':
				$report->setYLegend($this->translate('_num_carts'));
				$report->getCartCounts();
				break;
		}

		$response = $this->getChartResponse($report);
		$this->set('type', $type);
	}

	public function searchAction()
	{
		$report = new SearchReport();
		$this->initReport($report);

		$type = $this->getOption('search', 'top');

		switch ($type)
		{
			case 'top':
				$report->getTopSearches();
				break;
		}

		$response = $this->getChartResponse($report);
		$this->set('type', $type);
	}

	private function getChartResponse(Report $report)
	{

		if (Report::TABLE != $report->getChartType())
		{
			$this->set('chart', $report->getChartDataString());
		}
		else
		{
			$this->set('reportData', $report->getValues());
		}
		$this->set('chartType', $report->getChartType());
	}

	private function initReport(Report $report)
	{
		$report->setApplication($this->application);
		$report->setLocale($this->locale);
		$report->setInterval($this->getInterval());

		$range = $this->getDateRange();
		$report->setChartType($this->getChartTypeByInterval($this->getInterval()));

		if (!empty($range[0]))
		{
			$report->setFrom($range[0]);
		}

		if (!empty($range[1]))
		{
			$report->setTo($range[1]);
		}
	}

	private function getChartTypeByInterval($range)
	{
		switch ($range)
		{
			case 'day':
			case 'week':
			case 'month':
				return Report::LINE;

			default:
				return Report::BAR;
		}
	}

	private function getInterval($default = 'day')
	{
		return $this->getOption('interval', $default);
	}

	private function getOption($key, $defaultValue)
	{
		$options = json_decode($this->request->get('options'), true);
		return isset($options[$key]) ? $options[$key] : $defaultValue;
	}

	private function getDateRange()
	{
		if ($this->request->get('date') && ('all' != $this->request->get('date')))
		{
			$res = array();
			foreach (explode(' | ', $this->request->get('date')) as $part)
			{
				$res[] = ('now' == $part) ? null : getDateFromString($part);
			}

			return $res;
		}
	}
}

?>