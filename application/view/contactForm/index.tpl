{% extends "layout/frontend.tpl" %}

{% block title %}{t _contact_us}{{% endblock %}

{% block content %}

{form action="contactForm/send" method="POST" id="contactForm" handle=$form style="float: left;"}
	[[ textfld('name', '_your_name') ]]

	{* anti-spam *}
	<div style="display: none;">
		[[ textfld('surname', 'surname') ]]
	</div>

	[[ textfld('email', '_your_email') ]]

	[[ textareafld('msg', '_your_message') ]]

	{block FORM-SUBMIT-CONTACT}

	{include file="block/submit.tpl" caption="_form_submit"}

{/form}

{% endblock %}
