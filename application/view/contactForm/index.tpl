{% extends "layout/frontend.tpl" %}

{% block title %}{t _contact_us}{{% endblock %}

{% block content %}

{form action="controller=contactForm action=send" method="POST" id="contactForm" handle=$form style="float: left;"}
	{input name="name"}
		{label}{t _your_name}:{/label}
		{textfield}
	{/input}

	{* anti-spam *}
	<div style="display: none;">
		{input name="surname"}
			{label}{t surname}:{/label}
			{textfield}
		{/input}
	</div>

	{input name="email"}
		{label}{t _your_email}:{/label}
		{textfield}
	{/input}

	{input name="msg"}
		{label}{t _your_message}:{/label}
		{textarea}
	{/input}

	{block FORM-SUBMIT-CONTACT}

	{include file="block/submit.tpl" caption="_form_submit"}

{/form}

{% endblock %}
