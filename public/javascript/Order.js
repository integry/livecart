function OrderController($scope, $http)
{
	$scope.setOrder = function(order)
	{
		$scope.order = order;
		$scope.ignoreUpdate = true;
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
			console.log($scope.order.OrderedItems);
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
		});
	}, true);
}
