<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		{% block head %}{% endblock %}
		<title>{% block title %}{% endblock %} - My Webpage</title>
		<base href="/livecart2/public/"></base>
	</head>
	<body>
		{% block header %}{% endblock %}
		{% block left %}{% endblock %}
		{% block right %}{% endblock %}

		{% block contentstart %}{% include "block/content-start.tpl" %}{% endblock %}
			{% block content %}{% endblock %}
		{% block contentend %}{% include "block/content-stop.tpl" %}{% endblock %}

		{% block footer %}{% endblock %}
	</body>
</html>