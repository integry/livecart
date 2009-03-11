{pageTitle}{t _reg_confirm}{/pageTitle}
{include file="layout/frontend/layout.tpl"}

<div id="content">

	<h1>{t _reg_confirm}</h1>

	{if $success}
		<p>{t _reg_confirm_success}</p>
		<p>{t _reg_next_steps}:</p>
		<ul>
			<li><a href="{link controller=checkout action=pay}">{t _reg_next_steps_checkout}</a></li>
			<li><a href="{link controller=user}">{t _reg_next_steps_account}</a></li>
		</ul>
	{else}
		<p>{t _reg_confirm_failure}</p>
	{/if}

</div>

{include file="layout/frontend/footer.tpl"}