function OrderController($scope, $rootScope, $http)
{
	$scope.setOrder = function(order)
	{
		if (order.OrderedItems)
		{
			angular.forEach(order.OrderedItems, function(item)
			{
				if (item.options && (item.options instanceof Array))
				{
					item.options = {};
				}
			});
		}
		
		$scope.order = order;
		$scope.ignoreUpdate = true;
		
		$rootScope.setOrder(order);
	};
	
	$scope.incCount = function(item)
	{
		item.count = parseFloat(item.count) + 1;
	};
	
	$scope.decCount = function(item)
	{
		item.count = parseFloat(item.count) - 1;
		if (item.count < 0)
		{
			item.count = 0;
		}
	};
	
	$scope.remove = function(item)
	{
		var i = $scope.order.OrderedItems.indexOf(item);
		if (i > -1)
		{
			$scope.order.OrderedItems.splice(i, 1);
		}
	};
	
	$scope.getOptions = function(item)
	{
		if (!$scope.options)
		{
			$http.post('../order/options/', {ids: _.pluck($scope.order.OrderedItems, 'productID')}).success(function(res)
			{
				$scope.options = res;
			});
			
			$scope.options = {};
		}
		
		if ($scope.options && $scope.options[item.productID])
		{
			return $scope.options[item.productID];
		}
	};
	
	$scope.$watch('order.OrderedItems', function(newVal, oldVal)
	{
		if (!oldVal || $scope.ignoreUpdate)
		{
			$scope.ignoreUpdate = false;
			return;
		}
		
		$http.post('../order/update', {order: $scope.order}).success(function(res)
		{
			$scope.setOrder(res.order);
			$scope.options = false;
		});
	}, true);
}
