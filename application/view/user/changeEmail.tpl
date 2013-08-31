{% extends "layout/frontend.tpl" %}

{% title %}{t _change_email}{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "emailMenu"]) ]]
{% block content %}

	{form action="user/doChangeEmail" method="POST" handle=$form class="form-horizontal"}

		{input}
			{label}{t _current_email}:{/label}
			<div class="controls">[[user.email]]</div>
		{/input}

		[[ textfld('email', '_new_email') ]]

		[[ partial('block/submit.tpl', ['caption': "_complete_email_change", 'cancel': "user"]) ]]

	{/form}

{% endblock %}
