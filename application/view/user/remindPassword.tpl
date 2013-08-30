{% extends "layout/frontend.tpl" %}

{% block title %}{t _remind_pass}{{% endblock %}

{% block content %}

	{form action="user/doRemindPassword" method="post"  class="form-horizontal" handle=$form}
		[[ textfld('email', '_your_email') ]]

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return cancel=user}

		<input type="hidden" name="return" value="[[return]]" />

	{/form}

{% endblock %}
