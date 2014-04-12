Date.prototype.toJSON = function() {
  function addZ(n) {
    return (n<10? '0' : '') + n;
  }
  return this.getFullYear() + '-' + 
         addZ(this.getMonth() + 1) + '-' + 
         addZ(this.getDate());
};

var backendComponents = angular.module('backendComponents', []);

backendComponents.copyAttrs = function(element, attrs)
{
	angular.forEach(attrs.$attr, function(key)
	{
		element.attr(key, attrs[attrs.$normalize(key)]);
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

backendComponents.directive('tabsetRoute', function($compile, $http, $state, $location)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        transclude: true,
        template: '<ul class="nav nav-tabs" ng-transclude></ul>'
    };
});

backendComponents.directive('tabRoute', function($compile, $http, $state, $location)
{
    return {
        restrict: "E",
        scope: true,
		link: function(scope, element, attrs)
        {
			var newElem = angular.element('<li ng-click="go(\'' + element.attr('route') + '\')"><a>' + element.html() + '</a></li>');
			backendComponents.copyAttrs(newElem, attrs);

			newElem = $compile(newElem)(scope);

			element.replaceWith(newElem);

			scope.go = function(where)
			{
				$state.go(where);
			};
        }
    };
});

backendComponents.directive('myForm', function($compile, $timeout)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        link: function(scope, element, attrs)
        {
			var submit = element.attr('ng-submit');
			if (!submit)
			{
				element.attr('direct-submit', 'true');
				submit = '';
			}
			
			element.attr('ng-submit', 'markSubmitted(); ' + submit);
			element.removeAttr('ng-transclude');
			
            scope.$on('submit', function() 
            {
                setTimeout(function() 
                {
                	if (!element.attr('direct-submit') && element.attr('ng-submit'))
                	{
                    	scope.$apply(element.attr('ng-submit'));
                    }
                    else
                    {
                    	element.submit();
					}
                });
            });
			
			scope.customErrors = {};
				
			scope.checkErrors = function(event, form)
			{
				var errors = _.without(_.values(form.$error), false);
				if (errors.length || (element.find('ng-form.ng-invalid').length > 0))
				{
					if (event)
					{
						event.preventDefault();
					}
					
					return false;
				}
				
				return true;
			};
			
			scope.markSubmitted = function()
			{
				scope.customErrors = {};
				scope.isSubmitted = 1;
				
				$timeout(function()
				{
					var invalid = element.find('.ng-invalid').first();
					if (invalid.length)
					{
						$('html, body').animate({scrollTop: (invalid.closest(':visible').offset().top)},500);
					}
				});
			};

			scope.setCustomErrors = function(errors)
			{
				scope.customErrors = errors;
			};
		},
		template: '<form ng-transclude></form>'
	}
});

backendComponents.directive('submit', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        link: function(scope, element, attrs)
        {
			scope.element = element;

			scope.submit = function(e)
			{
				var form = attrs.tabform ? element.closest('.modal').find('form') : element.closest('form');
				if (!form.length)
				{
					form = element.closest('form');
				}
				
				//form.submit();
				e.preventDefault();
				
				angular.element(form).scope().$broadcast('submit');
			};
		},
        template: '<button type="submit" class="btn btn-primary" ng-click=";submit($event);" ng-transclude></button>'
   		//template: '<input type="submit" class="btn btn-primary" ng-click=";markSubmitted();" ng-transclude></input>'
    };
});

backendComponents.directive('customErrors', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        scope: true,
        link: function(scope, element, attrs)
        {
			var formScope = angular.element(element.closest('form')).scope();
			var field = attrs.field;

			scope.getErrors = function()
			{
				if (formScope.customErrors && formScope.customErrors[field])
				{
					return formScope.customErrors[field];
				}
			}
		},
        template: '<div ng-repeat="message in getErrors()" class="text-danger">{{ message }}</div>'
    };
});

backendComponents.directive('dialog', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: {cancel: '&'},
        template: '<div class="xmodal-dialog" ng-init="setSize()" ng-transclude></div>',
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

				return body.css("max-height", "" + height + "px");
			};

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
        template: '<button type="button" class="btn btn-default" ng-click="$close(0)" ng-transclude></button>'
	};
});

backendComponents.directive('dialogHeader', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        require: '^dialog',
        template: '<div class="modal-header"><button type="button" class="close" ng-click="$close(0)" aria-hidden="true">&times;</button><h4 class="modal-title" ng-transclude></h4></div>'
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

backendComponents.directive('grid', function($compile, $modal)
{
    return {
        restrict: "E",
        replace: true,
        link: function(scope, element, attrs)
		{
			scope.columnDefs = [];
			
			scope.pagingOptions = {
										pageSizes: [15],
										pageSize: 15,
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
			
			scope.setColumnDefs = function(defs)
			{
				scope.columnDefs = defs;
			};

			scope.setItemCount = function(count)
			{
				scope.totalServerItems = count;
			};
			
			scope.setPrimaryKey = function(key)
			{
				scope.gridOptions.primaryKey = key;
			};
			
			var getSelectedIDs = function()
			{
				if (scope.allSelected)
				{
					var ids = [];
					_.each(scope.unselectedItems, function(val, key) 
					{
						if (val) 
						{
							ids.push(key);
						}
					});
				}
				else
				{
					var ids = _.pluck(scope.gridOptions.selectedItems, scope.gridOptions.primaryKey);
				}

				return ids;
			};
			
			scope.massAction = function(action, params)
			{
				var ids = getSelectedIDs();
				if (!scope.isMassAllowed())
				{
					return;
				}
				
				var queryParams = {action: action, ids: ids, allSelected: scope.allSelected};
				if (params)
				{
					for (var key in params) { queryParams[key] = params[key]; }
				}
				
				scope.resource.mass(queryParams, function()
				{
					scope.refresh();
				});
			};
			
			scope.search = function()
			{
				var tpl = '<dialog><dialog-header>Search</dialog-header><dialog-body>' + 
				'<div class="row" ng-repeat="cond in searchConditions"><select ng-model="cond.field" ng-change="cond.type = getType(cond.field)" ng-options="o.field as o.displayName for o in gridScope.columnDefs" class="col-lg-6"></select>' +
					'<span ng-switch="getType(cond.field)" class="col-lg-6">' + 
						'<input ng-switch-when="text" type="text" ng-model="cond.value"  class="col-lg-12" />' +
						'<select ng-switch-when="bool" ng-model="cond.value" class="col-lg-12"><option value="0">No</option><option value="1">Yes</option></select>' +
						'<span ng-switch-when="date"><date-field ng-model="cond.value.from" placeholder="From" class="col-lg-6"></date-field><date-field ng-model="cond.value.to" placeholder="To" class="col-lg-6"></date-field></span>' +
						'<input ng-switch-default type="text" ng-model="cond.value"  class="col-lg-12" />' +
					'</span></div>' +
				'</dialog-body><dialog-footer><a class="btn btn-warning" ng-click="$close(0)">Cancel</a><a class="btn btn-primary" ng-click="confirmConditions()">Search</a></dialog-footer></dialog>';
				$modal.open({template: tpl, 
					controller: function($scope, gridScope)
					{
						$scope.gridScope = gridScope;
						$scope.searchConditions = gridScope.searchConditions;
						
						$scope.confirmConditions = function()
						{
							gridScope.searchConditions = $scope.searchConditions;
							gridScope.refresh();
							$scope.$close(0);
						};
						
						$scope.getType = function(field)
						{
							if (!field)
							{
								return;
							}
							
							var column = _.filter(gridScope.columnDefs, function(value) { return value.field == field; }).pop();
							return column.type;
						};
						
						$scope.$watch('searchConditions', function()
						{
							// rootScope.addEmptyRow(gridScope.searchConditions, 'field', {value: '', field: ''});
							var filtered = _.filter($scope.searchConditions, function(value) { return value.field != '' });
							if (filtered.length == $scope.searchConditions.length - 1)
							{
								return;
							}
							
							$scope.searchConditions = filtered;
							$scope.searchConditions.push({value: '', field: ''});
						}, true);
					},
					resolve: {	gridScope: function() { return scope } }
				});
			};
			
			scope.searchConditions = [{value: '', field: ''}];
			
			scope.isMassAllowed = function()
			{
				return scope.allSelected || (getSelectedIDs().length > 0);
			};
			
			scope.remove = function()
			{
				scope.massAction('delete');
			};

			var actionsButtonTemplate = element.find('actions').html();
			var menu = element.find('menu').html();
			
			var newElem = angular.element('<div></div>');
			backendComponents.copyAttrs(element, attrs);

			newElem.attr('ng-grid', 'gridOptions');
			newElem.append(menu);

			newElem = $compile(newElem)(scope);
			element.replaceWith(newElem);

			if (actionsButtonTemplate)
			{
				scope.$on('ngGridEventColumns', function (ev, columns)
				{
					window.setTimeout(function()
					{
						if (ev.targetScope.columnDefs.length && (_.pluck(ev.targetScope.columnDefs, 'field').indexOf('actions') == -1))
						{
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
			var newElem = angular.element('<button class="btn btn-primary btn-xs">' + element.html() + '</button>');
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

backendComponents.directive('dragIcon', function($compile)
{
    return {
        restrict: "E",
        replace: true,
        transclude: true,
        scope: false,
        template: '<span class="glyphicon glyphicon-move drag-icon"></span>'
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

backendComponents.directive('eavFormatted', function($compile)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        transclude: true,
        template: '<div class="form-group field_eav field_eav_{{ config.ID }} field_eav_{{ config.handle }}"><label class="control-label">{{ config.name }}</label><div ng-transclude></div></div>',
        link: function(scope, element, attrs)
		{
			var config = JSON.parse(attrs.config);
			scope.config = config;
			delete attrs.config;
			element.removeAttr('ng-transclude');
		}
	}
});

backendComponents.directive('imageField', function($compile, $timeout)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        template: '	<div>' +

		'<input type="file" ng-model-instant onchange="angular.element(this).scope().setFiles(this)" />' +
		'<div class="row" ng-show="getImage()">' +
			'<div class="col-lg-12">' +
				'<img ng-src="{{ getImage() }}" style="max-height: 300px;" class="img-responsive" />' +
				'<a class="glyphicon glyphicon-remove-circle" style="color: red; margin-left: 10px;" ng-show="image" ng-click="removeImage()"></a>' +
			'</div>' +
		'</div><div ng-show="progressVisible" class="uploadProgress">Uploading...</div>' +

		'<div ng-show="imageError">' +
			'<div class="alert alert-danger">{{ imageError }}</div>' +
		'</div>' +
		'</div>',
	
        link: function(scope, element, attrs)
		{
			scope.model = attrs.ngModel;
			scope.size = attrs.size;
			
			if (!attrs.single)
			{
				element.find('input').attr('multiple', 'multiple');
			}
			
			var unwatch = scope.$watch(attrs.ngModel, function(newVal)
			{
				if (!newVal) { return }
				scope.image = newVal;
				unwatch();
			});
			
			scope.getImage = function()
			{
				return scope.image;
			};
			
			scope.removeImage = function()
			{
				scope.image = null;
				
				$timeout(function()
				{
					scope.$apply(scope.model + ' = null');
					scope.$apply();
				});
			};
			
			scope.setImage = function(response)
			{
				response = JSON.parse(response);
				
				if (response.error)
				{
					scope.image = '';
					scope.imageError = response.error;
					var paths = null;
				}
				else
				{
					scope.image = response[0].image;
					scope.imageError = '';
					var paths = _.pluck(response, 'image');
				}
				
				if (attrs.single && paths)
				{
					paths = paths[0];
				}
				
				scope.$apply(scope.model + ' = ' + JSON.stringify(paths));
				
				scope.$apply();
			}
		},
		
		controller: function($scope, $element, $attrs)
		{
			$scope.setSize = function(size)
			{
				$scope.size = size;
			};
			
			$scope.setFiles = function(element) 
			{
				$scope.$apply(function($scope) {
				  // Turn the FileList object into an Array
					$scope.files = []
					for (var i = 0; i < element.files.length; i++) {
					  $scope.files.push(element.files[i])
					}
				  $scope.progressVisible = false
				});
				
				$scope.uploadFile();
			};

			$scope.uploadFile = function() {
				var fd = new FormData()
				for (var i in $scope.files) {
					fd.append("uploadedFile[]", $scope.files[i])
				}
				
				fd.append('size', $attrs.size);
				
				var xhr = new XMLHttpRequest()
				xhr.upload.addEventListener("progress", uploadProgress, false)
				xhr.addEventListener("load", uploadComplete, false)
				xhr.addEventListener("error", uploadFailed, false)
				xhr.addEventListener("abort", uploadCanceled, false)
				xhr.open("POST", "../upload")
				$scope.progressVisible = true
				xhr.send(fd)
			}

			function uploadProgress(evt) {
				$scope.$apply(function(){
					if (evt.lengthComputable) {
						$scope.progress = Math.round(evt.loaded * 100 / evt.total)
					} else {
						$scope.progress = 'unable to compute'
					}
				})
			}

			function uploadComplete(evt) {
				/* This event is raised when the server send back a response */
				$scope.setImage(evt.target.responseText);
				$scope.$apply(function(){
					$scope.progressVisible = false
				});
			}

			function uploadFailed(evt) {
				alert("There was an error attempting to upload the file.")
			}

			function uploadCanceled(evt) {
				$scope.$apply(function(){
					$scope.progressVisible = false
				})
			}
		}
	}
});

backendComponents.directive('dateField', function($compile, $timeout)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        transclude: true,
        template: '<input type="text" datepicker-popup="dd-MMMM-yyyy" is-open="opened" datepicker-options="dateOptions" readonly="readonly" ng-transclude />',
        link: function(scope, element, attrs)
		{
			scope.today = function() 
			{
				if (!scope.todayInst)
				{
					scope.todayInst = new Date();
				}
				
				return scope.todayInst;
			};

			scope.showWeeks = true;
			
			scope.toggleWeeks = function () 
			{
				scope.showWeeks = ! scope.showWeeks;
			};
			
			element.addClass('datepicker');
		}
	}
});

backendComponents.directive('eavFields', function($compile, $timeout)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        template: '<div ng-repeat="field in eavConfig"><eav-formatted config="{{field}}"><eav-field plain="{{plain}}" config="{{field}}"></eav-field></eav-formatted></div>',
        link: function(scope, element, attrs)
		{
			var filterFunc = function(field)
			{
				if (!attrs.filter)
				{
					return true;
				}
				else
				{
					return field[attrs.filter];
				}
			}
			
			var filter = function(fields)
			{
				return _.filter(fields, filterFunc);
			};
			
			scope.eavConfig = filter(scope[attrs.config]);
			
			scope.plain = attrs.plain;
			
			scope.$watch(attrs.config, function(newVal)
			{
				scope.eavConfig = filter(newVal);
			});
		}
	}
});

backendComponents.directive('eavField', function($compile, $timeout)
{
    return {
        restrict: "E",
        scope: true,
        replace: true,
        link: function(scope, element, attrs)
		{
			if (attrs.config)
			{
				var config = attrs.config;
			}
			else
			{
				var config = element.closest('.field_eav').attr('config');
			}
			
			config = JSON.parse(config);
			
			if ("5" == config.type)
			{
				scope.options = config.values;
				var html = '<select ng-options="o.ID as o.value for o in options" ng-click="opts()"></select>';
				var input = 'select';
			}
			else if ("3" == config.type)
			{
				var html = '<input type="text" />';
				var input = 'input';
			}
			else if ("4" == config.type)
			{
				var html = '<textarea' + (attrs.plain ? '' :' ui-my-tinymce') + '></textarea>';
				var input = 'textarea';
			}
			else if ("6" == config.type)
			{
				//var html = '<input type="text" datepicker-popup="dd-MMMM-yyyy" is-open="opened" min="minDate" datepicker-options="dateOptions" date-disabled="disabled(date, mode)" />';
				var html = '<div><date-field></date-field></div>';
				//<span class="glyphicon glyphicon-calendar"></span></button>

				scope.toggleMin = function() {
				scope.minDate = ( scope.minDate ) ? null : new Date();
				};
				scope.toggleMin();

				scope.open = function() {
				$timeout(function() {
				  scope.opened = true;
				});
				};

				scope.dateOptions = {
				'year-format': "'yy'",
				'starting-day': 1
				};
				
				var input = 'date-field';
			}
			
			if (html)
			{
				var fieldName = 'eav_' + config.ID;
				if (config.isRequired)
				{
					html = html + '<div class="text-danger" ng-show="isSubmitted && eavform.' + fieldName + '.$error.required">Lūdzu aizpildiet šo lauku</div>';
				}
				
				html = '<div><ng-form name="eavform">' + html + element.html() + '</ng-form></div>';
				
				var newElem = angular.element(html);
				
				var attrElem = input ? newElem.find(input) : newElem;
				
				attrElem.attr('ng-model', 'vals.eav.' + config.ID);
				attrElem.addClass('form-control');
				attrElem.attr('placeholder', config.description);
				attrElem.attr('name', fieldName);
				
				if (config.isRequired)
				{
					attrElem.attr('ng-required', true);
				}
				
				if ("5" == config.type)
				{
					if (config.isMultiValue)
					{
						attrElem.addClass('multiselect');
						attrElem.attr('multiple', 'multiple');
						attrElem.attr('multiselect', 'true');
					}
					else if (config.description)
					{
						attrElem.prepend('<option class="default" default value="">' + config.description + '</option>');
					}
				}
				
				delete attrs.$attr.config;
				if (input == 'date-field')
				{
					backendComponents.copyAttrs(attrElem, attrs);
				}

				newElem = $compile(newElem)(scope);
				element.replaceWith(newElem);
				
				//var form = angular.element(newElem.closest('form'));
				//$compile(form)(form.scope());
			}
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

backendComponents.directive('multiselect', function($timeout) 
{
    return function(scope, element, attributes) 
    {
		$(element).multiselect({
			includeSelectAllOption: true,
			buttonText: function(options, select)
			{
				if (options.length == 0) 
				{
					return select.attr('placeholder') + ' <b class="caret"></b>';
				}
				else if (options.length > 3) 
				{
					return options.length + ' selected <b class="caret"></b>';
				}
				else 
				{
					var selected = '';
					options.each(function() {
					selected += $(this).text() + ', ';
					});
					return selected.substr(0, selected.length -2) + ' <b class="caret"></b>';
				}
			}, 
			
			 // Replicate the native functionality on the elements so
			// that angular can handle the changes for us.
			onChange: function (optionElement, checked) 
			{
				optionElement.removeAttr('selected');
				 
				if (checked) 
				{
					optionElement.attr('selected', 'selected');
				}

				element.change();
			},
			
			buttonContainer: '<div class="form-control multisel" />', 
			maxHeight: 200});
			
			 // Watch for any changes to the length of our select element
			scope.$watch(function () {	return element[0].length; }, 
				function () 
				{
					element.multiselect('rebuild');
				});
			 
			// Watch for any changes from outside the directive and refresh
			scope.$watch(attributes.ngModel, 
				function () 
				{
					$timeout(function()
					{
						element.multiselect('refresh');
					});
				}, true);
     }
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

backendComponents.directive('defaultValues', function () {
    return {
        link: function (scope, elm, attrs, ctrl)
        {
			if (window[attrs.defaultValues])
			{
				var parent = scope;
				var lastParent = scope;

				while (parent.vals) { lastParent = parent; parent = parent.$parent; }
				lastParent.vals = window[attrs.defaultValues];
			}
        }
    };
});

backendComponents.directive('passwordMatch', function () {
    return {
        require: 'ngModel',
        link: function (scope, elm, attrs, ctrl)
        {
			ctrl.$parsers.unshift(function (viewValue) {
				ctrl.$setValidity('passwordmatch', viewValue == scope.vals[attrs.passwordMatch]);
				return viewValue;
			});
            scope.$watch('vals.' + attrs.passwordMatch, function(value) {
				ctrl.$setValidity('passwordmatch', ctrl.$viewValue == value);
            });
        }
    };
});

backendComponents.directive('filterNumber', function(){
   return {
     require: 'ngModel',
     link: function(scope, element, attrs, modelCtrl)
     {
		modelCtrl.$parsers.push(function (inputValue)
		{
			if (!inputValue)
			{
				return '';
			}

			if (!inputValue.replace)
			{
				return inputValue;
			}

			var transformedInput = parseFloat(inputValue.replace(/,/g,'').replace(/^[^-0-9]*/,''));

			if (transformedInput != inputValue)
			{
				modelCtrl.$setViewValue(transformedInput);
				modelCtrl.$render();
			}

			return transformedInput;
		});
     }
   };
});

backendComponents.directive('tabsetLazy', function () {
  return {
    restrict: 'E',
    replace: true,
    transclude: true,
    controller: function($scope) {
      $scope.templateUrl = '';
      var tabs = $scope.tabs = [];
      var controller = this;

      this.selectTab = function (tab) {
        angular.forEach(tabs, function (tab) {
          tab.selected = false;
        });
        tab.selected = true;
      };

      this.setTabTemplate = function (templateUrl) {
        $scope.templateUrl = templateUrl;
      }

      this.addTab = function (tab) {
        if (tabs.length == 0) {
          controller.selectTab(tab);
        }
        tabs.push(tab);
      };
    },
    template:
      '<div class="row"><div class="col-lg-12">' +
        '<div class="row">' +
          '<div class="nav nav-tabs" ng-transclude></div>' +
        '</div>' +
        '<div class="row tab-content">' +
          '<ng-include src="templateUrl">' +
        '</ng-include></div>' +
      '</div></div>'
  };
})
.directive('tabLazy', function () {
  return {
    restrict: 'E',
    replace: true,
    require: '^tabsetLazy',
    scope: {
      title: '@',
      templateUrl: '@'
    },
    link: function(scope, element, attrs, tabsetLazyController) {
      tabsetLazyController.addTab(scope);

      scope.select = function () {
        tabsetLazyController.selectTab(scope);
      }

      scope.$watch('selected', function () {
        if (scope.selected) {
          tabsetLazyController.setTabTemplate(scope.templateUrl);
        }
      });
    },
    template:
      '<li ng-class="{active: selected}">' +
        '<a href="" ng-click="select()">{{ title }}</a>' +
      '</li>'
  };
});

backendComponents.directive('prodOption', function($compile, $timeout)
{
    return {
        restrict: "E",
        scope: false,
        replace: true,
        link: function(scope, element, attrs)
		{
			if ("1" == scope.option.type)
			{
				var html = '<select ng-options="o.ID as o.name for o in option.choices"></select>';
				var input = 'select';
			}
			/*
			else if ("3" == config.type)
			{
				var html = '<input type="text" />';
				var input = 'input';
			}
			else if ("4" == config.type)
			{
				var html = '<textarea' + (attrs.plain ? '' :' ui-my-tinymce') + '></textarea>';
				var input = 'textarea';
			}
			else if ("6" == config.type)
			{
				//var html = '<input type="text" datepicker-popup="dd-MMMM-yyyy" is-open="opened" min="minDate" datepicker-options="dateOptions" date-disabled="disabled(date, mode)" />';
				var html = '<div><date-field></date-field></div>';
				//<span class="glyphicon glyphicon-calendar"></span></button>

				scope.toggleMin = function() {
				scope.minDate = ( scope.minDate ) ? null : new Date();
				};
				scope.toggleMin();

				scope.open = function() {
				$timeout(function() {
				  scope.opened = true;
				});
				};

				scope.dateOptions = {
				'year-format': "'yy'",
				'starting-day': 1
				};
				
				var input = 'date-field';
			}
			*/
			
			if (html)
			{
				var fieldName = 'opt_' + scope.option.ID;
				if (scope.option.isRequired)
				{
					html = html + '<div class="text-danger" ng-show="isSubmitted && eavform.' + fieldName + '.$error.required">Lūdzu aizpildiet šo lauku</div>';
				}
				
				html = '<div><ng-form name="optform">' + html + element.html() + '</ng-form></div>';
				
				var newElem = angular.element(html);
				
				var attrElem = input ? newElem.find(input) : newElem;
				
				attrElem.attr('ng-model', 'item.options.' + scope.option.ID + '.choiceID');
				attrElem.addClass('form-control');
				attrElem.attr('placeholder', scope.option.description);
				attrElem.attr('name', fieldName);
				
				if (scope.option.isRequired)
				{
					attrElem.attr('ng-required', true);
				}
				
				/*
				delete attrs.$attr.config;
				if (input == 'date-field')
				{
					backendComponents.copyAttrs(attrElem, attrs);
				}
				*/

				newElem = $compile(newElem)(scope);
				element.replaceWith(newElem);
				
				//var form = angular.element(newElem.closest('form'));
				//$compile(form)(form.scope());
			}
    	}
    };
});
