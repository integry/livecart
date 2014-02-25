app.controller('EavController', function ($scope, treeService, $http, $element, $state)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);

	$scope.categories = [];
	$scope.ids = {};

	$scope.activate = function(child)
	{
		$state.go('eavField.type', {type: child.id});
		
		$scope.activeID = child.id;
	};
});

app.controller('EavFieldController', function ($scope, $resource, $modal)
{
    $scope.resource = $resource('../backend/eavField/:verb/:id', 
    	{id: $scope.type, verb: '@verb'}, 
    	{
    		query:  {method:'GET', isArray: false, params: { verb: 'lists' }},
    		mass:  {method:'POST', params: { verb: 'mass' }}
    	}
    );

	$scope.edit = function(id)
	{
		$modal.open({templateUrl: Router.createUrl('backend/eavField', 'edit', {type: $scope.type}), 
					controller: 'EavFieldEditController',
					resolve: {
							type: function() { return $scope.type },
							id: function() { return id } }
							});
	};

	$scope.add = function()
	{
		$modal.open({templateUrl: Router.createUrl('backend/eavField', 'add', {type: $scope.type}), 
					controller: 'EavFieldEditController',
					resolve: {
							type: function() { return $scope.type },
							id: function() { return null } }
							});
	};
});

app.controller('EavFieldEditController', function ($scope, $resource, $modal, type, id)
{
	$scope.form = null;
	$scope.eavType = type;
    var resource = $resource('../backend/eavField/:verb/:id', 
    	{id: id, verb: '@verb'}, 
    	{ 
    		get: { method: 'GET', params: { verb: 'get' }},
    		save: { method: 'POST', params: { verb: 'save' }} 
    	}
    );
    
	$scope.vals = resource.get(function(vals)
	{
		vals.eavType = type;
	});

	$scope.isSelect = function()
	{
		return $scope.vals && (($scope.vals.type == 1) || ($scope.vals.type == 5));
	};

	$scope.isNumber = function()
	{
		return $scope.vals && (($scope.vals.type == 1) || ($scope.vals.type == 2));
	};
	
	$scope.addRemoveValues = function()
	{
		if (!$scope.vals.values)
		{
			$scope.vals.values = [];
		}
		
		var filtered = _.filter($scope.vals.values, function(value) { return value.value != ''; });
		
		if (filtered.length == $scope.vals.values.length - 1)
		{
			return;
		}
		
		$scope.vals.values = filtered;
		$scope.vals.values.push({value: ''});
	};
	
	$scope.$watch('vals.values', function()
	{
		$scope.addRemoveValues();
	});
	
	$scope.save = function()
	{
		if (!$scope.getChildScopeForm($scope).$invalid)
		{
			resource.save($scope.vals, success('The field has been saved'));
		}
	};
	
	$scope.sortAZ = function()
	{
		$scope.vals.values = _.sortBy($scope.vals.values, 'value');
	};
});
