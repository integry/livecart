{% extends "layout/frontend.tpl" %}

{% block title %}{t _remind_pass}{{% endblock %}

{% block content %}

	{form action="controller=user action=doRemindPassword" method="post"  class="form-horizontal" handle=$form}
		{input name="email"}
			{label}{t _your_email}:{/label}
			{textfield}
		{/input}

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return cancel=user}

		<input type="hidden" name="return" value="[[return]]" />

	{/form}

{% endblock %}
