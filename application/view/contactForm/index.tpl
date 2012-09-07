{pageTitle}{t _contact_us}{/pageTitle}

{loadJs form=true}
{include file="layout/frontend/layout.tpl"}

<div id="content">

<h1>{t _contact_us}</h1>

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

	<p>
		<label></label>
		<input type="submit" class="submit" value="{t _form_submit}" />
	</p>

{/form}

</div>

{include file="layout/frontend/footer.tpl"}