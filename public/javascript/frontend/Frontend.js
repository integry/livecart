/**
 *	@author Integry Systems
 */

var app = angular.module('LiveCart', ['ui.bootstrap', 'backendComponents']);

function CheckoutController($scope)
{
	$scope.step = 'login';
	
	$scope.setOrder = function(order)
	{
		$scope.vals = order;
	};
	
	$scope.setAnon = function()
	{
		$scope.changeStep('shipping');
		$scope.isAnon = true;
	};
	
	$scope.logIn = function()
	{
		
	};
	
	$scope.saveShippingAddress = function(form)
	{
		$scope.isSubmitted = 1;

		if (!form.$valid)
		{
			return;
		}
		
		$http.post('../checkout/shipping', {order: $scope.vals}).success(function(res)
		{
			$scope.vals = res.order;
			$scope.shippingMethods = res.shipping;
			
			$scope.changeStep('method');
		});
	};
	
	$scope.saveShippingMethod = function(form)
	{
		$scope.isSubmitted = 1;
		if (!form.$valid)
		{
			return;
		}
		
		$scope.changeStep('payment');
	};

	$scope.saveBillingAddress = function(form)
	{
		$scope.isSubmitted = 1;
		if (!form.$valid)
		{
			return;
		}
		
		$scope.changeStep('payment');
	};
	
	$scope.changeStep = function(num)
	{
		$scope.isSubmitted = 0;
		$scope.step = num;
	};
};
