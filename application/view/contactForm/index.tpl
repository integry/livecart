{pageTitle}{t _contact_us}{/pageTitle}

{loadJs form=true}
{include file="layout/frontend/layout.tpl"}

<div id="content">

<h1>{t _contact_us}</h1>

{form action="controller=contactForm action=send" method="POST" id="contactForm" handle=$form style="float: left;"}
	<p>
		{err for="name"}
			{{label {t _your_name}:}}
			{textfield class="text"}
		{/err}
	</p>

	<div style="display: none;">
		{err for="surname"}
			{{label Your surname:}}
			{textfield class="text"}
		{/err}
	</div>

	<p>
		{err for="email"}
			{{label {t _your_email}:}}
			{textfield class="text"}
		{/err}
	</p>

	<p>
		{err for="msg"}
			{{label {t _your_message}:}}
			{textarea}
		{/err}
	</p>

	<p>
		<label></label>
		<input type="submit" class="submit" value="{t _form_submit}" />
	</p>

{/form}

</div>

{include file="layout/frontend/footer.tpl"}