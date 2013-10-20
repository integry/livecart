<!DOCTYPE html>
<html lang="en" ng-app="LiveCart">
	<head>
		<base href="[[ url("public") ]]/"></base>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.3/angular.min.js"></script>

		<script src="../module/mrfeedback/public/javascript/angular-strap-common.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.6.0/ui-bootstrap-tpls.min.js"></script>
		<script src="http://mgcrea.github.io/angular-strap/vendor/bootstrap-datepicker.js"></script>
		<script src="../module/mrfeedback/public/javascript/angular-strap-datepicker.js"></script>
		<link href="http://mgcrea.github.io/angular-strap/vendor/bootstrap-datepicker.css" rel="stylesheet"></link>

		<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.1/underscore-min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-router/0.2.0/angular-ui-router.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui/0.4.0/angular-ui.min.js"></script>

		<script src="javascript/library/angular/tree.js"></script>
		<script src="javascript/library/angular/tinymce.js"></script>
		<script src="javascript/common.js"></script>
		<script src="javascript/library/angular/directives.js"></script>
		<script src="javascript/library/angular/ng-grid-scroller/ng-grid-scroller.js"></script>
		<script src="javascript/library/angular/ng-grid-scroller/ng-grid-select-all.js"></script>
		
		<script src="javascript/backend/Backend.js"></script>
		<script src="javascript/library/jquery/jquery-ui.js"></script>
		<script src="javascript/library/jquery/plugins.js"></script>
		
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" rel="stylesheet"></link>

		<script src="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.js"></script>
		<link href="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.css" rel="stylesheet"></link>
		<script src="http://code.angularjs.org/1.2.0-rc.3/angular-resource.min.js"></script>
		
		<link href="stylesheet/backend/Backend.css" rel="stylesheet"></link>
		<link href="stylesheet/backend/Eav.css" rel="stylesheet"></link>

		{% block head %}{% endblock %}

		<script type="text/javascript">
			Router.setUrlTemplate("[[ url("controller/action") ]]");
		</script>

		<title>{% block title %}{% endblock %}</title>
	</head>
	
	<body ng-controller="BackendController">
		<div class="row">
			
		<nav class="navbar navbar-default" role="navigation">
		  <div class="navbar-header">
			<a class="navbar-brand" ng-click="setPage('/')">Home</a>
		  </div>

		  <div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav">

			  <li>
			  	<a ng-click="setPage('category')">Products</a>
			  </li>

			  <li class="active">
			  	<a ng-click="setPage('eavField')">Custom Fields</a>
			  </li>
			</ul>
		  </div>
		</nav>
		
		<section ui-view></section>
	</body>
</html>
