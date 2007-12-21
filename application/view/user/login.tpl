{loadJs form=true}

<div class="userLogin">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

	<h1>{t _login}</h1>

	<h2>{t _returning}</h2>

	<p>
		{if $failed}
			<div class="errorMsg failed">
				{t _login_failed}
			</div>
		{else}
			<label></label>
			{t _please_sign_in}
		{/if}
	</p>

	{capture var="return"}{link controller="user"}{/capture}
	{include file="user/loginForm.tpl" return=$return}

	<h2>{t _new_cust}</h2>

		<label></label>
		{t _not_registered}

	{include file="user/regForm.tpl"}

</div>

{literal}
	<script type="text/javascript">
		Event.observe(window, 'load', function() {$('email').focus()});
	</script>
{/literal}

{include file="layout/frontend/footer.tpl"}

</div>