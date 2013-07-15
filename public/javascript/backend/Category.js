app.controller('CategoryController', function ($scope, treeService, $http, $element, $dialog)
{
	$scope.tree = treeService;
	$scope.tree.initController($scope);

	$scope.categories = [];
	$scope.ids = {};

	$scope.activate = function(child)
	{
		if (!$scope.ids[child.id])
		{
			$scope.categories.push(child);
			$scope.ids[child.id] = true;
		}

		$scope.activeID = child.id;
	};

	$scope.expandRoot = function(child)
	{
		$scope.tree.select($scope.tree.findByID(1));
		$scope.tree.expandID(1);
	};

	$scope.update = function(item, params)
	{
		$http.post(Router.createUrl('backend.category', 'move', params), this.instance);
	};

	$scope.add = function(parent)
	{
		var d = $dialog.dialog({dialogFade: false, resolve: {tree: function(){ return $scope.tree; }, parent: function(){ return parent; } }});
		d.open(Router.createUrl('backend.category', 'add'), 'AddCategoryController');
	};

	$scope.remove = function()
	{
		if (confirm(Backend.getTranslation('_confirm_remove_category')))
		{
			$http.post(Router.createUrl('backend.category', 'remove', {id: $scope.activeID}));

			var tree = $scope.tree;
			var active = tree.findByID($scope.activeID);
			tree.select(tree.getParent(active));
			tree.remove(active);
		}
	};
});

app.controller('CategoryFormController', function ($scope, $http)
{
	$http.get(Router.createUrl('backend.category', 'category', {id: $scope.category.id})).
		success(function(data, status, headers, config)
		{
			$scope.category = data;
		});

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend.category', 'update'), $scope.category);
	}
});

app.controller('AddCategoryController', ['$scope', '$http', 'dialog', 'parent', 'tree', function($scope, $http, dialog, parent, tree)
{
    $scope.category = {parent: parent},
    $scope.tree = tree,

    $scope.submit = function(form)
    {
    	if (form.$valid)
    	{
        	$http.post(Router.createUrl('backend.category', 'create'), $scope.category).
        	success(function(data)
        	{
        		dialog.close();
        		$scope.tree.append(data, $scope.category.parent);
        		$scope.tree.expandID($scope.category.parent);
        		$scope.tree.select($scope.tree.findByID(data.id));
			});
		}
    };

    $scope.cancel = function()
    {
        dialog.close();
    };
}]);

/*
Backend.Category.PopupSelector = Class.create();
Backend.Category.PopupSelector.prototype =
{
	onAccept: null,
	onCancel: null,

	initialize: function(onAccept, onCancel, categoryID)
	{
		this.onAccept = onAccept;
		this.onCancel = onCancel;

		if (!Backend.Category.links.popup)
		{
			Backend.Category.links.popup = Backend.Router.createUrl('backend.category', 'popup');
		}

		var w = window.open(Backend.Category.links.popup + (categoryID ? '#cat_' + categoryID : ''), 'selectCategory', 'width=260, height=450');

		this.window = w;

		window.popupOnload =
			function()
			{
				Event.observe(w.document.getElementById('select'), 'click', function()
					{
						var cat = w.Backend.Category;
						var pathAsText = cat.getTreeInst().get_path(cat.getNode(cat.getSelectedId())).join(' > ');

						var res = true;

						if (this.onAccept)
						{
							res = this.onAccept(cat.getSelectedId(), pathAsText, {}, w);
						}

						if (res)
						{
							w.close();
						}

					}.bindAsEventListener(this) );

				Event.observe(w.document.getElementById('cancel'), 'click', function(e)
					{
						var res = true;

						if (this.onCancel)
						{
							res = this.onCancel(this);
						}

						if (res)
						{
							w.close();
						}

						e.preventDefault();
					}.bindAsEventListener(this) );
			}.bind(this);

		// close the popup automatically if closing/reloading page
		Event.observe(window, 'unload', function()
		{
			w.close();
		});
	}
}
*/