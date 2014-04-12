app.controller('GalleryController', function ($scope, $http, $resource, $modal)
{
    $scope.resource = $resource('../backend/gallery/:verb/:id', 
    	{id: $scope.id, verb: '@verb'}, 
    	{
    		query:  {method:'POST', isArray: false, params: { verb: 'lists' }},
    		mass:  {method:'POST', params: { verb: 'mass' }}
    	}
    );

	$scope.add = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/gallery', 'edit'), 
					controller: 'EditGalleryController',
					resolve: {
							categoryID: function() { return $scope.id },
							id: function() { return null } 
							},
					windowClass: 'gallery-edit'
							});
	};
	
	$scope.edit = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/gallery', 'edit'), 
					controller: 'EditGalleryController',
					resolve: {
							id: function() { return id },
							categoryID: function() { return null } 
							 },
					windowClass: 'gallery-edit'
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
		d.open(Router.createUrl('backend/gallery', 'add'), 'EditGalleryController');
	};
*/
});

app.controller('GalleryPresentationController', function ($scope, $http)
{
	$http.get(Router.createUrl('backend/gallery', 'presentation', {id: $scope.gallery.ID})).
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

app.controller('EditGalleryController', function ($scope, $http, $modal, id, categoryID)
{
	$scope.id = id;
	
	// if id != 0 --> edit gallery
	// if categoryID != 0 --> add new gallery
	$http.get(Router.createUrl('backend/gallery', 'get', {id: id, categoryID: categoryID})).
		success(function(data)
		{
			$scope.vals = data;
			$scope.vals.isEnabled = data.isEnabled ? 1 : 0;
		});
		
	$http.get(Router.createUrl('backend/gallery', 'eav', {id: id, categoryID: categoryID})).
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
		
		$http.post(Router.createUrl('backend/gallery', 'update'), $scope.vals).success(function(res)
		{
			success('The gallery has been saved')();
			$scope.vals = res;
			$scope.id = res.ID;
		});
	}
});

app.controller('GalleryPricingController', function ($scope)
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

app.controller('GalleryMainCategoryController', function ($scope, $rootScope, $http, treeService)
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

app.controller('GalleryCategoriesController', function ($scope, $rootScope, $http, treeService)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);
	$scope.categories = {};
	
	$scope.catService = $rootScope.categoryTree;
	$scope.setTree(angular.copy($scope.catService.getTree()));
	$scope.tree.expandID(1);
	
	$http.get(Router.createUrl('backend/gallery', 'categories', {id: $scope.id})).
		success(function(data)
		{
			$scope.categories = data;
			$scope.categories.id = $scope.id;
		});
		
	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/gallery', 'categories'), $scope.categories).success(success('The gallery categories have been saved'));
	};
	
	$scope.setMain = function(id)
	{
		$scope.categories.main = id;
	};
	
	//$scope.$watch('categories', function() { console.log(JSON.stringify($scope.categories)); }, true);
});

app.controller('GalleryImagesController', function ($scope, $http)
{
	$scope.newimage = {};
	
	$http.get(Router.createUrl('backend/gallery', 'images', {id: $scope.id})).
		success(function(data)
		{
			$scope.images = data;
		});
		
	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/gallery', 'images'), {id: $scope.id, images: $scope.images}).success(function(images)
		{
			$scope.images = images;
			success('The gallery images have been saved')();
		});
	};
	
	$scope.remove = function(image)
	{
		$scope.images.splice($scope.images.indexOf(image), 1);
	};
	
	$scope.getPath = function(image)
	{
		if (image.paths && image.paths[0])
		{
			return image.paths[0];
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
