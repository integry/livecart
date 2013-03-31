{pageTitle}{t _confirming_email}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	<p>
	{if $subscriber.isEnabled}
		{t _confirm_successful}
	{else}
		{t _confirm_unsuccessful}
	{/if}
	</p>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}