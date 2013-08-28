<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.5/angular.min.js"></script>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" rel="stylesheet"></link>

		{% block head %}{% endblock %}
		<title>{% block title %}{% endblock %}</title>
		<base href="/livecart2/public/"></base>
	</head>
	<body>
		{% block header %}{% include "layout/frontend/header.tpl" %}{% endblock %}
		{% block left %}{% include "layout/frontend/leftSide.tpl" %}{% endblock %}
		{% block right %}{% include "layout/frontend/rightSide.tpl" %}{% endblock %}

		{% block contentstart %}{% include "block/content-start.tpl" %}{% endblock %}
			{% block content %}{% endblock %}
		{% block contentend %}{% include "block/content-stop.tpl" %}{% endblock %}

		{% block footer %}{% endblock %}
	</body>
</html>