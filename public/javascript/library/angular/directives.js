var backendComponents = angular.module('backendComponents', []);
backendComponents.copyAttrs = function(element, attrs)
{
	angular.forEach(attrs.$attr, function(key)
	{
		element.attr(key, attrs[key]);
	});
}

/* taken from https://gist.github.com/eliotsykes/5394631 */
backendComponents.directive('ngfocus', ['$parse', function($parse) {
  return function(scope, element, attr) {
    var fn = $parse(attr['ngfocus']);
    element.bind('focus', function(event) {
      scope.$apply(function() {
        fn(scope, {$event:event});
      });
    });
  }
}]);

backendComponents.directive('ngblur', ['$parse', function($parse) {
  return function(scope, element, attr) {
    var fn = $parse(attr['ngblur']);
    element.bind('blur', function(event) {
      scope.$apply(function() {
        fn(scope, {$event:event});
      });
    });
  }
}]);

backendComponents.directive('number', function() {
  return {
    require: 'ngModel',
    link: function (scope, element, attr, ngModelCtrl)
    {
		function fromUser(text)
		{
			var text = (text || '').toString();
			var transformedInput = (attr.number == 'float') ? text.replace(/[^0-9\.]/g, '') : text.replace(/[^0-9]/g, '');

			if(transformedInput !== text)
			{
				ngModelCtrl.$setViewValue(transformedInput);
				ngModelCtrl.$render();
			}

			return transformedInput;
		}
		ngModelCtrl.$parsers.push(fromUser);
    }
  };
});

backendComponents.directive('money', function() {
  return {
    require: 'ngModel',
    link: function (scope, element, attr, ngModelCtrl)
    {
		function fromUser(text)
		{
			var value = (text || '').toString().replace(/^0+/, '');
			if(!value) return;

			value = value.replace(',' , '.');

			// only keep the last comma
			parts = value.split('.');

			value = '';
			for (k = 0; k < parts.length; k++)
			{
				value += parts[k] + ((k == (parts.length - 2)) && (parts.length > 1) ? '.' : '');
			}

			// split digits and decimal part
			parts = value.split('.');

			// leading comma (for example: .5 converted to 0.5)
			if ('' == parts[0] && 2 == parts.length)
			{
				parts[0] = '0';
			}

			//next remove all characters save 0 though 9
			//in both elms of the array
			dollars = parts[0].replace(/^-?[^0-9]-/gi, '');

			if ('' != dollars && '-' != dollars)
			{
				dollars = parseInt(dollars);

				if(!dollars) dollars = 0;
			}

			if (2 == parts.length)
			{
				cents = parts[1].replace(/[^0-9]/gi, '');
				dollars += '.' + cents;
			}

			transformedInput = dollars;

			if(transformedInput !== text)
			{
				ngModelCtrl.$setViewValue(transformedInput);
				ngModelCtrl.$render();
			}

			return transformedInput;
		}
		ngModelCtrl.$parsers.push(fromUser);
    }
  };
});

backendComponents.directive('tabRoute', function($compile, $http, $location)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,

        link: function(scope, element, attrs)
        {
			var newElem = angular.element('<tab heading="{{heading}}"><ng-include src="\'' + element.attr('template') + '\'"></ng-include></tab>').attr(attrs.$attr);;
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

backendComponents.directive('weightInput', function($compile, $timeout)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: true,
        template: '<div class="unitConverter"><input type="number" number="true" ng-change="recalculate()" ng-model="values[type].hi" /><span>{{captions[type].hi}}</span><input number="true" type="number" ng-change="recalculate()" ng-model="values[type].lo" maxlength="{{multipliers[type][3]}}" /><span>{{captions[type].lo}}</span><input type="hidden" ng-bind="kg" ng-transclude /><a ng-click="toggleType()">{{captions[type].sw}}</a></div>',
        link: function(scope, element, attrs)
		{
			scope.type = attrs.type;
			scope.captions = {};
			scope.captions.english = {hi: attrs.enHi, lo: attrs.enLo, sw: attrs.enSw};
			scope.captions.metric = {hi: attrs.mHi, lo: attrs.mLo, sw: attrs.mSw};
			scope.values = {english: {hi: 0, lo: 0}, metric: {hi: 0, lo: 0}};
			scope.multipliers = {english: [0.45359237, 0.0283495231, 1, 2], metric: [1, 0.001, 0, 3]};
			scope.model = attrs.ngModel;
			scope.kg = scope.$eval(scope.model);

			scope.toggleType = function()
			{
				scope.type = scope.type == 'english' ? 'metric' : 'english';
			};

			scope.recalculate = function()
			{
				var values = scope.values[scope.type];
				var multi = scope.multipliers[scope.type];
				scope.kg = values.hi * multi[0] + values.lo * multi[1];
			}

			scope.$watch(scope.model, function(kg)
			{
				scope.kg = kg;
			});

			scope.$watch('kg', function(kg)
			{
				if (typeof kg === "undefined")
				{
					return;
				}

				angular.forEach(['english', 'metric'], function(type)
				{
					var multi = scope.multipliers[type];
					var hi = Math.floor(kg / multi[0]);
					var lo = Math.round((kg - (hi * multi[0])) / multi[1], multi[2]);
					scope.values[type] = {hi: hi, lo: lo};
					$timeout(function()
					{
						scope.$apply(scope.model + ' = ' + kg);
					}, 0, true);
				});
			});
    	}
    };
});

backendComponents.directive('eavSelect', function($compile)
{
    return {
        restrict: "E",
        link: function(scope, element, attrs)
		{
			var newValue = attrs['new'];
			if (!newValue)
			{
				return;
			}

			element.find('select').append('<option value="other">' + newValue + '</option>').change(function()
			{
				element.find('.newOptionValue').toggle(jQuery(this).val() == 'other').find('input').focus();
			});
    	}
    };
});

backendComponents.directive('eavMultiselect', function($parse)
{
    return {
        restrict: "E",
        scope: true,
        link: function($scope, $element, $attrs)
        {
			$scope.selectedValues = [];
			$scope.sortOrder = null;
			$scope.filter = '';
			$scope.newValues = [{value: ''}];
		},

        controller: function($scope, $element, $attrs)
        {
			$scope.model = $attrs.ngModel;

			if (!($parse($scope.model)($scope)))
			{
				var allModel = $scope.model.substr(0, $scope.model.lastIndexOf('.'));
				var attributeID = $scope.model.substr($scope.model.lastIndexOf('.') + 1, $scope.model.length);
				var allAttributes = $parse(allModel)($scope);
				allAttributes[attributeID] = {valueIDs: []};
				$parse(allModel).assign($scope, allAttributes);
			}

			$scope.values = [];
			_.each(JSON.parse($attrs.values), function(value, key)
			{
				if (key)
				{
					$scope.values.push({key: key, value: value, checked: $parse($scope.model)($scope).valueIDs.indexOf(key) > -1});
				}
			});

			$scope.selectAll = function()
			{
				_.each($scope.values, function(value)
				{
					value.checked = true;
				});
			};

			$scope.deselectAll = function()
			{
				_.each($scope.values, function(value)
				{
					value.checked = false;
				});
			};

			$scope.sort = function()
			{
				$scope.sortOrder = function()
				{
					return 'value';
				};
			};

			$scope.handleNewValues = function()
			{
				$scope.newValues = _.filter($scope.newValues, function(val) { return val.value != '' });
				$scope.newValues.push({value: ''});
			};

			$scope.$watch('values', function(value)
			{
				if (value)
				{
					var values = _.pluck(_.filter($scope.values, function(value) { return value.checked == true; }), 'key');
					$parse($scope.model + '.valueIDs').assign($scope, values);
				}
			}, true);

			$scope.$watch('newValues', function(value)
			{
				if (value)
				{
					var values = _.pluck($scope.newValues, 'value');
					$parse($scope.model + '.newValues').assign($scope, values);
				}
			}, true);
		}
    };
});
