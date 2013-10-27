app.controller('ProductController', function ($scope, $http, $resource, $modal)
{
    $scope.resource = $resource('../backend/product/:verb/:id', 
    	{id: $scope.id, verb: '@verb'}, 
    	{
    		query:  {method:'GET', isArray: false, params: { verb: 'lists' }},
    		mass:  {method:'POST', params: { verb: 'mass' }}
    	}
    );

	$scope.edit = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/product', 'edit'), 
					controller: 'EditProductController',
					resolve: {
							id: function() { return id } }
							});
	};
/*
	$scope.add = function()
	{
		return;
		var d = $modal.dialog({dialogFade: false, resolve: {categoryID: function(){ return $scope.id; }, id: function(){ return null; } }});
		d.open(Router.createUrl('backend/product', 'add'), 'EditProductController');
	};
*/
});

app.controller('ProductPresentationController', function ($scope, $http)
{
	$http.get(Router.createUrl('backend/product', 'presentation', {id: $scope.product.ID})).
		success(function(data)
		{
			$scope.presentation = data;
		});

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend.presentation', 'updatePresentation'), $scope.presentation);
	}

    $scope.cancel = function()
    {
        dialog.close();
    };
});

app.controller('EditProductController', function ($scope, $http, $modal, id)
{
	// if id != 0 --> edit product
	// if categoryID != 0 --> add new product
	$http.get(Router.createUrl('backend/product', 'get', {id: id})).
		success(function(data)
		{
			$scope.vals = data;
		});
		
	$http.get(Router.createUrl('backend/product', 'eav', {id: id})).
		success(function(data)
		{
			$scope.eav = data;
		});

	$scope.getSpecFieldTemplate = function(product)
	{
		if (!product)
		{
			return;
		}

		return Router.createUrl('backend/product', 'specFields', {id: product.ID, categoryID: categoryID});
	};

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/product', 'update'), $scope.vals);
	}
});

app.directive('quantityPrice', function($compile)
{
    return {
        restrict: "E",
        scope: true,
        controller: function($scope, $element, $attrs)
        {
			$scope.groups = [{id: '0', oldValue: '0'}, {id: '', oldValue: ''}];
			$scope.quantities = [];
			$scope.isInitialized = false;
			$scope.isActive = false;

			$scope.init = function(currency)
			{
				$scope.currency = currency;
				$scope.$watch('product.quantityPrice.' + currency + '.serializedRules', function(newPrice)
				{
					if (!newPrice || $scope.isInitialized)
					{
						return;
					}

					$scope.isInitialized = true;
					$scope.isActive = true;

					$scope.quantities = [];
					_.each(newPrice, function(prices, quant)
					{
						var quantity = {quantity: quant, oldValue: quant};
						_.each(prices, function(price, group)
						{
							quantity[group] = price;
						});

						$scope.quantities.push(quantity);
					});

					var groups = [];
					_.each(newPrice, function(prices) { groups = groups.concat(_.keys(prices)); });

					$scope.groups = [];
					_.each(_.uniq(groups), function(group)
					{
						var id = (group).toString();
						$scope.groups.push({id: id, oldValue: id});
					});

					$scope.groups.push({id: '', oldValue: ''});
					$scope.addQuantity();
					$scope.sortGroups();
				});
			};

			$scope.updateQuantities = function(quantity)
			{
				if (quantity.oldValue == '')
				{
					$scope.addQuantity();
				}

				quantity.oldValue = quantity.quantity;
			};

			$scope.updateOnBlur = function(quantity)
			{
				if (quantity.quantity == '')
				{
					$scope.quantities.splice($scope.quantities.indexOf(quantity), 1);
					$scope.addQuantity();
				}

				$scope.quantities = _.sortBy($scope.quantities, function(quantity)
				{
					return quantity.quantity != '' ? parseInt(quantity.quantity) : 'a';
				});
			};

			$scope.addQuantity = function()
			{
				if (($scope.quantities.length == 0) || (_.last($scope.quantities).quantity != ''))
				{
					$scope.quantities.push({quantity: '', oldValue: '', 0: ''});
				}
			};

			$scope.addGroup = function(group)
			{
				if (group.oldValue == '0')
				{
					$scope.groups.unshift({id: '0', oldValue: '0'});
				}
				else if (group.oldValue === '')
				{
					$scope.groups.push({id: '', oldValue: ''});
				}

				var existing = _.findWhere($scope.groups, {id: group.id, oldValue: group.id});
				if (existing)
				{
					$scope.groups.splice(_.indexOf($scope.groups, existing), 1);
				}

				_.each($scope.quantities, function(quantity)
				{
					quantity[group.id] = quantity[group.oldValue];
					delete quantity[group.oldValue];
				});

				group.oldValue = group.id;

				$scope.sortGroups();
			};

			$scope.sortGroups = function()
			{
				$scope.groups = _.sortBy($scope.groups, function(group) { return group.id != '0'; });
			};

			$scope.$watch('quantities', function()
			{
				if (!$scope.product)
				{
					return;
				}

				var rules = {};
				_.each($scope.quantities, function(quantity)
				{
					if ('' === quantity.quantity)
					{
						return;
					}

					rules[quantity.quantity] = {};
					_.each(quantity, function(price, id)
					{
						if (!isNaN(parseInt(id)))
						{
							rules[quantity.quantity][id] = price;
						}
					});
				});

				$scope.product.quantityPrice[$scope.currency].serializedRules = rules;
			}, true);

			$scope.addQuantity();
		}
    };
});
