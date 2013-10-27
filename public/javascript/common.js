/********************************************************************
 * Router / Url manipulator
 ********************************************************************/
Router =
{
	urlTemplate: '',

	setUrlTemplate: function(url)
	{
		url = url.replace(/controller/, '__c__');
		this.urlTemplate = url.replace(/action/, '__a__');
	},

	createUrl: function(controller, action, params)
	{
		var url = this.urlTemplate.replace(/__c__/, controller);
		url = url.replace(/__a__/, action);

		if (params)
		{
			angular.forEach(params, function(value, key)
			{
				url = this.setUrlQueryParam(url, key, value)
			}.bind(this));
		}

		return url;
	},

	setUrlQueryParam: function(url, key, value)
	{
		return url + (url.match(/\?/) ? '&' : '?') + key + '=' + value;
	}
}

app.run(function($rootScope)
{
    $rootScope.getChildScopeForm = function(scope)
    {
    	var formScope = this.getFormScope(scope);
    	if (formScope)
    	{
    		return formScope.form;
		}
	};
	
	$rootScope.getFormScope = function(scope)
    {
    	var child = scope.$$childHead;
    	while (child)
    	{
    		if (child.$$childHead)
    		{
    			var formScope = this.getFormScope(child);
    			if (formScope)
    			{
    				return formScope;
				}
    		}
    		
    		if (child.form)
    		{
    			return child;
			}
			
			child = child.$$nextSibling;
		}
	};
});
