<div class="box quickLogin">
	<div class="title">{t _login}</div>
	<div class="content">
		{include file="user/loginForm.tpl"}
		<div class="quickLoginRegister">
			<a href="{link controller=user action=register}">{t _not_registered}</a>
		</div>
	</div>
</div>