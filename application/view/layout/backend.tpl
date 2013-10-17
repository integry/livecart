<!DOCTYPE html>
<html lang="en" ng-app="LiveCart">
	<head>
		<base href="[[ url("public") ]]/"></base>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.2/angular.min.js"></script>

		<script src="../module/mrfeedback/public/javascript/angular-strap-common.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.6.0/ui-bootstrap-tpls.min.js"></script>
		<script src="http://mgcrea.github.io/angular-strap/vendor/bootstrap-datepicker.js"></script>
		<script src="../module/mrfeedback/public/javascript/angular-strap-datepicker.js"></script>
		<link href="http://mgcrea.github.io/angular-strap/vendor/bootstrap-datepicker.css" rel="stylesheet"></link>

		<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.1/underscore-min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>

		<script src="javascript/library/angular/tinymce.js"></script>
		<script src="javascript/frontend/Frontend.js"></script>
		<script src="javascript/library/angular/directives.js"></script>
		<script src="../module/mrfeedback/public/javascript/frontend/Frontend.js"></script>

		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" rel="stylesheet"></link>
		<link href="upload/css/mrfeedback.css" rel="stylesheet"></link>

		<script src="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.js"></script>
		<link href="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.css" rel="stylesheet"></link>
		<script src="http://code.angularjs.org/1.2.0-rc.2/angular-resource.min.js"></script>

		{% block head %}{% endblock %}

		<script type="text/javascript">
			Router.setUrlTemplate("[[ url("controller/action") ]]");
		</script>

		<title>{% block title %}{% endblock %}</title>
	</head>
	<body>
		<div id="container" class="container">
			{% block header %}{% endblock %}

			<div class="row">
				{% block content %}{% endblock %}
			</div>

			{% block footer %}{% endblock %}
		</div>
	</body>
</html>
