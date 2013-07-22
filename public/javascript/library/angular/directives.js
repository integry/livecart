var backendComponents = angular.module('backendComponents', []);
backendComponents.copyAttrs = function(element, attrs)
{
	angular.forEach(attrs.$attr, function(key)
	{
		element.attr(key, attrs[key]);
	});
}

backendComponents.directive('tabRoute', function($compile, $http, $location)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,

        link: function(scope, element, attrs)
        {
			var newElem = angular.element('<tab heading="{{heading}}"><ng-include src="\'http://localhost' + element.attr('template') + '\'"></ng-include></tab>').attr(attrs.$attr);;
			backendComponents.copyAttrs(newElem, attrs);

			newElem.attr('select', 'activate()');

			newElem = $compile(newElem)(scope);

			element.replaceWith(newElem);

			scope.activate = function()
			{
				$location.path(newElem.attr('route'));
				var content = jQuery(jQuery(newElem).closest('.tabbable').find('.tab-content .tab-pane.active')[jQuery(newElem).index()]);
				if (content.is(':empty'))
				{
					$http({method: 'GET', url: newElem.attr('template')}).
					 success(function(data, status, headers, config) {
						var html = content.html(data);
						$compile(html)(scope);
						});
				}
			};
        }
    };
});

backendComponents.directive('submit', function($compile)
{
    return {
        restrict: "E",
        transclude: true,
        link: function(scope, element, attrs)
        {
			scope.markSubmitted = function()
			{
				var form = attrs.tabform ?  element.closest('.modal').find('.' + attrs.tabform) : element.closest('form');
				var formScope = angular.element(form).scope();
				formScope.isSubmitted = 1;
			}
		},
        template: '<button type="submit" class="btn btn-primary" ng-click=";markSubmitted();" ng-transclude></button>'
    };
});

backendComponents.directive('dialog', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: {cancel: '&'},
        template: '<div class="modal-dialog" ng-init="setSize();"><div class="modal-content" ng-transclude></div></div>',
        controller: function($scope, $element, $attrs)
        {
			$scope.close = function()
			{
				$scope.cancel();
			};

			this.close = $scope.close;

			$scope.setFullHeight = function()
			{
				var windowHeight = jQuery(window).height();
				var dialogHeight = $element.height();
				if ((this.previousHeight == windowHeight) && (this.previousDialogHeight == dialogHeight))
				{
					return;
				}

				var body, bodypaddings, header, headerheight, footer, height, modalheight;
				modal = $element;
				header = jQuery(".modal-header", modal);
				body = jQuery(".modal-body", modal);
				footer = jQuery(".modal-header", modal);
				modalheight = parseInt(modal.css("height"));
				headerheight = parseInt(header.css("height")) + parseInt(header.css("padding-top")) + parseInt(header.css("padding-bottom"));
				footerheight = parseInt(footer.css("height")) + parseInt(footer.css("padding-top")) + parseInt(footer.css("padding-bottom"));
				bodypaddings = parseInt(body.css("padding-top")) + parseInt(body.css("padding-bottom"));
				height = windowHeight - headerheight - footerheight - bodypaddings - 50;

				this.previousHeight = windowHeight;
				this.previousDialogHeight = dialogHeight;

				$scope.center();

				return body.css("max-height", "" + height + "px");
			};

			$scope.center = function()
			{
				$element.css({
					'margin-left': function ()
					{
						return (jQuery(window).width() - jQuery(this).width()) / 2;
					}
					/*
					,
					'margin-top': function ()
					{
						console.log(jQuery(window).height(), jQuery(this).height());
						return (jQuery(window).height() - jQuery(this).height()) / 2;
					}
					*/
				});
			}

			if ($attrs.fullheight)
			{
				window.setInterval(function()
				{
					$scope.setFullHeight();
				}, 100);

				jQuery(window).resize(function()
				{
					$scope.setFullHeight();
				});
			}

			$scope.center();
		}
    };
});

backendComponents.directive('dialogCancel', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        require: '^dialog',
        template: '<button type="button" class="btn btn-default" ng-click="close()" ng-transclude></button>',
		link: function(scope, element, attrs, dialogCtrl)
		{
			scope.close = function()
			{
				dialogCtrl.close();
			};
    	}
	};
});

backendComponents.directive('dialogHeader', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        require: '^dialog',
        template: '<div class="modal-header"><button type="button" class="close" ng-click="close()" aria-hidden="true">&times;</button><h4 class="modal-title" ng-transclude></h4></div>',
        link: function(scope, element, attrs, dialogCtrl)
		{
			scope.close = function()
			{
				dialogCtrl.close();
			};
    	}
    };
});

backendComponents.directive('dialogBody', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: false,
        template: '<div class="modal-body" ng-transclude></div>'
    };
});

backendComponents.directive('dialogFooter', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: true,
        template: '<div class="modal-footer" ng-transclude></div>'
    };
});

backendComponents.directive('grid', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        link: function(scope, element, attrs)
		{
			scope.pagingOptions = {
										pageSizes: [10],
										pageSize: 10,
										currentPage: 1
									};

			scope.gridOptions = {	data: 'data',
									columnDefs: 'columnDefs',
									useExternalSorting: true,
									useExternalFiltering: true,
									enableColumnReordering: true,
									enablePaging: true,
									showColumnMenu: true,
									showFilter: true,
									showFooter: true,
									showSelectionCheckbox: true,
									totalServerItems: 'totalServerItems',
									pagingOptions: scope.pagingOptions,
									selectedItems: [],
									plugins: [
										new ngGridScroller({resource: scope.resource, scope: scope}),
										new ngGridSelectAll({scope: scope})
										]
								 };

			if (attrs.primarykey)
			{
				scope.gridOptions.primaryKey = attrs.primarykey;
			}

			var actionsButtonTemplate = element.find('actions').html();

			var newElem = angular.element('<div></div>');
			backendComponents.copyAttrs(element, attrs);

			newElem.attr('ng-grid', 'gridOptions');

			newElem = $compile(newElem)(scope);
			element.replaceWith(newElem);

			if (actionsButtonTemplate)
			{
				scope.$on('ngGridEventColumns', function (ev, columns)
				{
					window.setTimeout(function()
					{
						if (ev.targetScope.columnDefs && !ev.targetScope.isActionColumnAdded)
						{
							ev.targetScope.isActionColumnAdded = true;
							ev.targetScope.columnDefs.unshift({field: 'actions', displayName: '', visible: true, cellTemplate: '<div class="editColumn">' + actionsButtonTemplate + '</div>', width: 'auto'});
							ev.targetScope.$apply();
						}
					}, 0);
				});
			}
    	}
    };
});

/*

@todo: transclude fails in Angular 1.1.5

backendComponents.directive('editButton', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: false,
        template: '<button class="btn btn-primary btn-mini" ng-click="edit()" ng-transclude></button>',
        priority: 1
    };
});
*/

backendComponents.directive('editButton', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        scope: false,
        priority: 1,
        link: function(scope, element, attrs)
		{
			var newElem = angular.element('<button class="btn btn-primary btn-mini">' + element.html() + '</button>');
			backendComponents.copyAttrs(element, attrs);
			newElem.attr('ng-click', '; $event.stopPropagation(); startEditor(row);');

			newElem = $compile(newElem)(scope);
			element.replaceWith(newElem);

			scope.startEditor = function(row)
			{
				scope.edit(row.selectionProvider.pKeyParser(row.entity));
			}
    	}
    };
});

backendComponents.directive('fieldset', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: false,
        template: '<div class="panel" ng-transclude></div>'
    };
});

backendComponents.directive('legend', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: false,
        template: '<div class="panel-heading" ng-transclude></div>'
    };
});