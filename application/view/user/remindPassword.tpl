{% extends "layout/frontend.tpl" %}

{% title %}{t _remind_pass}{% endblock %}
{% block left %}{% endblock %}
{% block right %}{% endblock %}

{% block content %}

	[[ form("user/doRemindPassword", ["method": "POST"]) ]]>

		[[ textfld('email', '_your_email', ['type': 'email']) ]]

		[[ partial('block/submit.tpl', ['caption': "_continue"]) ]]

	</form>

{% endblock %}
