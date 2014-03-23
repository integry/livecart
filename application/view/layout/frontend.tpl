<!DOCTYPE html>
<html lang="en" ng-app="LiveCart" xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">
	<head>
		<base href="[[ url("public") ]]/"></base>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.3/angular.min.js"></script>

		<script src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.6.0/ui-bootstrap-tpls.min.js"></script>

		<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.1/underscore-min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<link href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet"></link>

		<script src="javascript/frontend/Frontend.js"></script>
		<script src="javascript/library/angular/directives.js"></script>
		
		<script src="javascript/library/jquery/plugins.js"></script>
		<link href="stylesheet/library/jquery/jquery-plugins.css" rel="stylesheet"></link>
		
		<script src="javascript/common.js"></script>
		<script src="javascript/Order.js"></script>
		
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" rel="stylesheet"></link>
		
		<link href="stylesheet/frontend/Frontend.css" rel="stylesheet"></link>

		<script src="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.js"></script>
		<link href="//cdnjs.cloudflare.com/ajax/libs/ng-grid/2.0.7/ng-grid.min.css" rel="stylesheet"></link>
		<script src="http://code.angularjs.org/1.2.0-rc.2/angular-resource.min.js"></script>
		
		<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>

		{% block head %}{% endblock %}
		[[ partial("layout/frontend/head.tpl") ]]
		
		<script type="text/javascript">
			Router.setUrlTemplate("[[ url("controller/action") ]]");
		</script>

		<title>{% block title %}{% endblock %}{% if 'index' != volt.dispatcher.getControllerName() %} - [[ config('SITE_NAME') ]]{% endif %}</title>
		
		<meta name="description" content="{% block metadescr %}[[ meta(config('INDEX_META_DESCRIPTION')) ]]{% endblock %}" />
		<meta name="og:description" content="{% block ogmetadescr %}[[ meta(config('INDEX_META_DESCRIPTION')) ]]{% endblock %}" />
	</head>
	<body>
		<div id="container" class="container">
			{% block header %}[[ partial("layout/frontend/header.tpl") ]]{% endblock %}

			<div class="row">
				{% block left %}[[ partial("layout/frontend/leftSide.tpl") ]]{% endblock %}
				{% block right %}[[ partial("layout/frontend/rightSide.tpl") ]]{% endblock %}

				{% block contentstart %}[[ partial("block/content-start.tpl") ]]{% endblock %}
					{% block content %}{% endblock %}
				{% block contentend %}[[ partial("block/content-stop.tpl") ]]{% endblock %}
			</div>

			<div class="row">
				{% block footer %}[[ partial("layout/frontend/footer.tpl") ]]{% endblock %}
			</div>
		</div>
	</body>
</html>
