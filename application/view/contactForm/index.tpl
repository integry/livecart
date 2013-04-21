{pageTitle}{t _contact_us}{/pageTitle}
{loadJs form=true}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

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

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}