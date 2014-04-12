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

function CategoryController($scope)
{
	$scope.$watch('sort', function(sort, old)
	{
		if (sort == old)
		{
			return;
		}
		
		setGetParameter('sort', sort)
	});
};

function setGetParameter(paramName, paramValue)
{
    var url = window.location.href;
    if (url.indexOf(paramName + "=") >= 0)
    {
        var prefix = url.substring(0, url.indexOf(paramName));
        var suffix = url.substring(url.indexOf(paramName));
        suffix = suffix.substring(suffix.indexOf("=") + 1);
        suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
        url = prefix + paramName + "=" + paramValue + suffix;
    }
    else
    {
    if (url.indexOf("?") < 0)
        url += "?" + paramName + "=" + paramValue;
    else
        url += "&" + paramName + "=" + paramValue;
    }
    window.location.href = url;
}
