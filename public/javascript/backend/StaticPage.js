app.controller('StaticPageController', function ($scope, treeService, $http, $element)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);

	$scope.pages = [];
	$scope.ids = {};

	$scope.activate = function(child)
	{
		if (!$scope.ids[child.id])
		{
			$http.get(Router.createUrl('backend/staticpage', 'edit', {id : child.id})).success(function(data)
			{
				$scope.pages.push(data);
				$scope.ids[data.ID] = true;
				$scope.activeID = data.ID;
			});
		}
		else
		{
			$scope.activeID = child.id;
		}
	};

	$scope.isActive = function(instance)
	{
		/*
		if (instance)
		{
			return instance.ID == $scope.activeID;
		}
		*/
	};

	$scope.update = function(item, params)
	{
		$http.post(Router.createUrl('backend/staticpage', 'move', params), this.instance);
	};

	$scope.add = function()
	{
		if (!$scope.pages.length || $scope.pages[0].ID)
		{
			$scope.pages.splice({id: null, children: []}, 0, 0);
		}

		$scope.activeID = null;
	};

	$scope.remove = function()
	{
		if (confirm($scope.getTranslation('_del_conf')))
		{
			$http.post(Router.createUrl('backend/staticpage', 'delete', {id: $scope.activeID}));
			$scope.tree.remove($scope.activeID);
			$scope.activeID = null;
		}
	};

	$scope.getTabTitle = function(page)
	{
		return page && page.ID ? page.title : $scope.getTranslation('_add_new_title');
	};

	$scope.save = function()
	{
		$http.post(Router.createUrl('backend/staticpage', 'save'), this.vals);
	}
});
