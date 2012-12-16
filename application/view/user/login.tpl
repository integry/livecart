{loadJs form=true}

<div class="userLogin">

{include file="layout/frontend/layout.tpl" hideLeft=true}

<div id="content" class="left">

	<h1>{t _login}</h1>

	<div class="returningCustomer">
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

		{capture assign="return"}{link controller="user"}{/capture}
		{include file="user/loginForm.tpl" return=$return}
	</div>

	<div class="newCustomer">
		<h2>{t _new_cust}</h2>

			<label></label>
			{t _not_registered}

		{include file="user/regForm.tpl"}
	</div>

	<div class="clear"></div>

</div>

{literal}
	<script type="text/javascript">
		Event.observe(window, 'load', function() {$('email').focus()});
	</script>
{/literal}

{include file="layout/frontend/footer.tpl"}

</div>