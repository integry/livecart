app.controller('ProductController', function ($scope, $http, $resource, $modal)
{
    $scope.resource = $resource('../backend/product/:verb/:id', 
    	{id: $scope.id, verb: '@verb'}, 
    	{
    		query:  {method:'POST', isArray: false, params: { verb: 'lists' }},
    		mass:  {method:'POST', params: { verb: 'mass' }}
    	}
    );

	$scope.add = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/product', 'edit'), 
					controller: 'EditProductController',
					resolve: {
							categoryID: function() { return $scope.id },
							id: function() { return null } 
							}
							});
	};
	
	$scope.edit = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/product', 'edit'), 
					controller: 'EditProductController',
					resolve: {
							id: function() { return id },
							categoryID: function() { return null } 
							 }
							});
	};
	
	$scope.enable = function()
	{
		$scope.massAction('setValue', {isEnabled: 1});
	};

	$scope.set = function(key, value)
	{
		var params = {};
		params[key] = value;
		$scope.massAction('setValue', params);
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

app.controller('EditProductController', function ($scope, $http, $modal, id, categoryID)
{
	$scope.id = id;
	
	// if id != 0 --> edit product
	// if categoryID != 0 --> add new product
	$http.get(Router.createUrl('backend/product', 'get', {id: id, categoryID: categoryID})).
		success(function(data)
		{
			$scope.vals = data;
		});
		
	$http.get(Router.createUrl('backend/product', 'eav', {id: id, categoryID: categoryID})).
		success(function(data)
		{
			$scope.eav = data;
		});

	$scope.save = function(form)
	{
		if (form.$invalid)
		{
			return;
		}
		
		$http.post(Router.createUrl('backend/product', 'update'), $scope.vals).success(function(res)
		{
			success('The product has been saved')();
			$scope.vals = res;
			$scope.id = res.ID;
		});
	}
});

app.controller('ProductPricingController', function ($scope)
{
	$scope.setCurrencies = function(main, other)
	{
		$scope.currencies = [main];
	};
	
	$scope.curr = function(currency)
	{
		console.log(currency);
		return currency;
	}
});

app.controller('ProductMainCategoryController', function ($scope, $rootScope, $http, treeService)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);
	
	$scope.catService = $rootScope.categoryTree;
	$scope.setTree(angular.copy($scope.catService.getTree()));
	$scope.tree.expandID(1);
	
	$scope.$watch('categories', function(cats)
	{
		if (cats && cats.main)
		{
			$scope.tree.selectID(cats.main);
		}
	}, true);
	
	$scope.activate = function(cat)
	{
		$scope.setMain(cat.id);
	};
});

app.controller('ProductCategoriesController', function ($scope, $rootScope, $http, treeService)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);
	$scope.categories = {};
	
	$scope.catService = $rootScope.categoryTree;
	$scope.setTree(angular.copy($scope.catService.getTree()));
	$scope.tree.expandID(1);
	
	$http.get(Router.createUrl('backend/product', 'categories', {id: $scope.id})).
		success(function(data)
		{
			$scope.categories = data;
			$scope.categories.id = $scope.id;
		});
		
	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/product', 'categories'), $scope.categories).success(success('The product categories have been saved'));
	};
	
	$scope.setMain = function(id)
	{
		$scope.categories.main = id;
	};
	
	//$scope.$watch('categories', function() { console.log(JSON.stringify($scope.categories)); }, true);
});

app.controller('ProductImagesController', function ($scope, $http)
{
	$scope.newimage = {};
	
	$http.get(Router.createUrl('backend/product', 'images', {id: $scope.id})).
		success(function(data)
		{
			$scope.images = data;
		});
		
	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/product', 'images'), {id: $scope.id, images: $scope.images}).success(function(images)
		{
			$scope.images = images;
			success('The product images have been saved')();
		});
	};
	
	$scope.remove = function(image)
	{
		$scope.images.splice($scope.images.indexOf(image), 1);
	};
	
	$scope.getPath = function(image)
	{
		if (image.paths && image.paths[3])
		{
			return image.paths[3];
		}
		else
		{
			return image.uploadedPath;
		}
	};
	
	$scope.$watch('newimage', function(newImage)
	{
		if (newImage.path)
		{
			$scope.image = '';
			angular.forEach(newImage.path, function(path)
			{
				$scope.images.push({uploadedPath: path});
			});
			$scope.newimage = {};
		}
	}, true);
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
