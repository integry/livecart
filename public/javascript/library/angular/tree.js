var treeModule = angular.module('tree', []);
treeModule.directive('yaTree', function () {

  return {
    restrict: 'A',
    transclude: 'element',
    priority: 1000,
    terminal: true,
    compile: function (tElement, tAttrs, transclude) {

      var repeatExpr, childExpr, rootExpr, childrenExpr;

      repeatExpr = tAttrs.yaTree.match(/^(.*) in ((?:.*\.)?(.*)) at (.*)$/);
      childExpr = repeatExpr[1];
      rootExpr = repeatExpr[2];
      childrenExpr = repeatExpr[3];
      branchExpr = repeatExpr[4];

      return function link (scope, element, attrs) {

        var rootElement = element[0].parentNode,
            cache = [];

        // Reverse lookup object to avoid re-rendering elements
        function lookup (child) {
          var i = cache.length;
          while (i--) {
            if (cache[i].scope[childExpr] === child) {
              return cache.splice(i, 1)[0];
            }
          }
        }

        scope.$watch(rootExpr, function (root) {

          var currentCache = [];

          // Recurse the data structure
          (function walk (children, parentNode, parentScope, depth) {

            var i = 0,
                n = children.length,
                last = n - 1,
                cursor,
                child,
                cached,
                childScope,
                grandchildren;

            // Iterate the children at the current level
            for (; i < n; ++i) {

              // We will compare the cached element to the element in
              // at the destination index. If it does not match, then
              // the cached element is being moved into this position.
              cursor = parentNode.childNodes[i];

              child = children[i];

              // See if this child has been previously rendered
              // using a reverse lookup by object reference
              cached = lookup(child);

              // If the parentScope no longer matches, we've moved.
              // We'll have to transclude again so that scopes
              // and controllers are properly inherited
              if (cached && cached.parentScope !== parentScope) {
                cache.push(cached);
                cached = null;
              }

              // If it has not, render a new element and prepare its scope
              // We also cache a reference to its branch node which will
              // be used as the parentNode in the next level of recursion
              if (!cached) {
                transclude(parentScope.$new(), function (clone, childScope) {

                  childScope[childExpr] = child;

                  cached = {
                    scope: childScope,
                    parentScope: parentScope,
                    element: clone[0],
                    branch: clone.find(branchExpr)[0]
                  };

                  // This had to happen during transclusion so inherited
                  // controllers, among other things, work properly
                  parentNode.insertBefore(cached.element, cursor);

                });
              } else if (cached.element !== cursor) {
                parentNode.insertBefore(cached.element, cursor);
              }

              // Lets's set some scope values
              childScope = cached.scope;

              // Store the current depth on the scope in case you want
              // to use it (for good or evil, no judgment).
              childScope.$depth = depth;

              // Emulate some ng-repeat values
              childScope.$index = i;
              childScope.$first = (i === 0);
              childScope.$last = (i === last);
              childScope.$middle = !(childScope.$first || childScope.$last);

              // Push the object onto the new cache which will replace
              // the old cache at the end of the walk.
              currentCache.push(cached);

              // If the child has children of its own, recurse 'em.
              grandchildren = child[childrenExpr];
              if (grandchildren && grandchildren.length) {
                walk(grandchildren, cached.branch, childScope, depth + 1);
              }
            }
          })(root, rootElement, scope, 0);

          // Cleanup objects which have been removed.
          // Remove DOM elements and destroy scopes to prevent memory leaks.
          i = cache.length;

          while (i--) {
            cached = cache[i];
            if (cached.scope) {
              cached.scope.$destroy();
            }
            if (cached.element) {
              cached.element.parentNode.removeChild(cached.element);
            }
          }

          // Replace previous cache.
          cache = currentCache;

        }, true);
      };
    }
  };
});

treeModule.directive('uiNestedSortable', ['$parse', function ($parse) {

  'use strict';

  var eventTypes = 'Create Start Sort Change BeforeStop Stop Update Receive Remove Over Out Activate Deactivate'.split(' ');

  return {
    restrict: 'A',
    link: function (scope, element, attrs) {

    var element = jQuery(element);

      var options = attrs.uiNestedSortable ? $parse(attrs.uiNestedSortable)() : {};

      angular.forEach(eventTypes, function (eventType) {

        var attr = attrs['uiNestedSortable' + eventType],
          callback;

        if (attr) {
          callback = $parse(attr);
          options[eventType.charAt(0).toLowerCase() + eventType.substr(1)] = function (event, ui) {
            scope.$apply(function () {

              callback(scope, {
                $event: event,
                $ui: ui
              });
            });
          };
        }

      });

      element.nestedSortable(options);

    }
  };
}]);

treeModule.factory('treeService', function($timeout)
{
	var data = {
		children: []
		};

	var service =
	{
		setTree: function(treeData)
		{
			data = treeData;
		},

		getTree: function()
		{
			return data;
		},

		toggleMinimized: function (child)
		{
			child.minimized = !child.minimized;
		},

		addChild: function (child)
		{
			child.children.push({
			  title: '',
			  children: []
			});
		},

		remove: function (child)
		{
			function walk(target) {
			  var children = target.children,
				i;
			  if (children) {
				i = children.length;
				while (i--) {
				  if (children[i].id == child) {
					return children.splice(i, 1);
				  } else {
					walk(children[i])
				  }
				}
			  }
			}
			walk(data);
		},

		update: function (event, ui)
		{
			var root = event.target,
			  item = angular.element(ui.item),
			  parent = item.parent(),
			  target = (parent[0] === root) ? data : parent.scope().child,
			  child = item.scope().child,
			  index = jQuery(item).index();

			target.children || (target.children = []);

			function walk(target, child) {
			  var children = target.children,
				i;
			  if (children) {
				i = children.length;
				while (i--) {
				  if (children[i] === child) {
					return children.splice(i, 1);
				  } else {
					walk(children[i], child);
				  }
				}
			  }
			}
			walk(data, child);

			target.children.splice(index, 0, child);

			if (this.controller.update)
			{
				this.controller.update(item, this.getUpdateParams(item));
			}
		},

		getUpdateParams: function(item)
		{
			var params = {id: angular.element(item).scope().child.id};
			var parent = item.parent().scope();
			if (parent.child)
			{
				params.parent = parent.child.id;
			}

			var prev = angular.element(item.prev()).scope();
			if (prev && prev.child)
			{
				params.previous = prev.child.id;
			}

			var next = angular.element(item.next()).scope();
			if (next && next.child)
			{
				params.next = next.child.id;
			}

			return params;
		},

		select: function(item)
		{
			this.selectedID = item.id;
			this.controller.activate(item);
		},

		selectID: function(ID)
		{
			this.selectedID = ID;
		},

		initController: function($scope)
		{
			this.controller = $scope;

			$scope.setTree = function(treeData)
			{
				$scope.tree.setTree(treeData);
				$scope.data = $scope.tree.getTree();
			};

			$scope.updatePosition = function(event, ui)
			{
				$scope.tree.update(event, ui);
			};
		}
	};

	return service;
});