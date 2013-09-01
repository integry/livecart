[[ partial("macro/form.tpl") ]]
<!DOCTYPE html>
<html lang="en" ng-app="LiveCart">
	<head>
		<base href="/livecart2/public/"></base>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.5/angular.min.js"></script>
		<script src="javascript/frontend/Frontend.js"></script>
		<script src="javascript/library/angular/directives.js"></script>

		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" rel="stylesheet"></link>
		<link href="upload/css/mrfeedback.css" rel="stylesheet"></link>

		{% block head %}{% endblock %}
		<title>{% block title %}{% endblock %}</title>
	</head>
	<body>
		<div id="container" class="container">
			{% block header %}[[ partial("layout/frontend/header.tpl") ]]{% endblock %}
			{% block left %}[[ partial("layout/frontend/leftSide.tpl") ]]{% endblock %}
			{% block right %}[[ partial("layout/frontend/rightSide.tpl") ]]{% endblock %}

			{% block contentstart %}[[ partial("block/content-start.tpl") ]]{% endblock %}
				{% block content %}{% endblock %}
			{% block contentend %}[[ partial("block/content-stop.tpl") ]]{% endblock %}

			{% block footer %}[[ partial("layout/frontend/footer.tpl") ]]{% endblock %}
		</div>
	</body>
</html>