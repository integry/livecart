var backendComponents = angular.module('backendComponents', []);

backendComponents.directive('tabRoute', function($compile, $http, $location)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,

        link: function(scope, element, attrs)
        {
			var newElem = angular.element('<tab><ng-include src="\'http://localhost' + element.attr('template') + '\'"></ng-include></tab>').attr(attrs.$attr);;

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
			var formScope = angular.element(element.closest('form')).scope();
			scope.markSubmitted = function()
			{
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
        controller: function($scope, $element, $attrs)
        {
			$scope.close = function()
			{
				$scope.cancel();
			};
			this.close = $scope.close;
		},

        template: '<div class="modal-dialog"><div class="modal-content" ng-transclude></div></div>'
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