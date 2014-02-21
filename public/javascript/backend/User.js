app.controller('UserGroupController', function ($scope, $state, $rootScope, treeService, $http, $element, $modal)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);
	
	$rootScope.categoryTree = $scope.tree;

	$scope.activate = function(child)
	{
		$state.go('user.view.users', {id: child.id});
		$scope.activeID = child.id;
	};

	$scope.expandRoot = function(child)
	{
		//$scope.tree.select($scope.tree.findByID(1));
		//$scope.tree.expandID(1);
	};

	$scope.add = function(parent)
	{
		$modal.open({templateUrl: Router.createUrl('backend/usergroup', 'add'), 
					controller: 'AddUserGroupController',
					resolve: {
							tree: function() { return $scope.tree }
							}});
	};

	$scope.remove = function()
	{
		if (confirm($scope.getTranslation('_confirm_remove_group')))
		{
			$http.post(Router.createUrl('backend/usergroup', 'remove', {id: $scope.activeID}));

			var tree = $scope.tree;
			var active = tree.findByID($scope.activeID);
			tree.select(tree.getParent(active));
			tree.remove(active);
		}
	};
});

app.controller('UserController', function ($scope, $http, $resource, $modal)
{
    $scope.resource = $resource('../backend/usergroup/:verb/:id', 
    	{id: $scope.id, verb: '@verb'}, 
    	{
    		query:  {method:'POST', isArray: false, params: { verb: 'lists' }},
    		mass:  {method:'POST', params: { verb: 'mass' }}
    	}
    );

	$scope.edit = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/user', 'edit'), 
					controller: 'EditUserController',
					resolve: {
							id: function() { return id } }
							});
	};
	
	$scope.enable = function()
	{
		$scope.massAction('setValue', {isEnabled: 1});
	};
	
	$scope.disable = function()
	{
		$scope.massAction('setValue', {isEnabled: 0});
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

app.controller('EditUserController', function ($scope, $http, $modal, id)
{
	$scope.id = id;
	
	$http.get(Router.createUrl('backend/user', 'get', {id: id})).
		success(function(data)
		{
			$scope.vals = data;
		});
		
	$http.get(Router.createUrl('backend/user', 'eav', {id: id})).
		success(function(data)
		{
			$scope.eav = data;
		});

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/user', 'update'), $scope.vals).success(success('User account has been saved'));
	}
});
