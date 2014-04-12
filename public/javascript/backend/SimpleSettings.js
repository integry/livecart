app.controller('SimpleSettingsController', function ($scope, $http)
{
	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend/simplesettings', 'save'), $scope.vals).success(success('Settings have been saved'));
	}
});
