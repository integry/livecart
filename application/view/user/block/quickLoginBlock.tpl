<div class="box quickLogin">
	<div class="title">{t _login}</div>
	<div class="content">
		[[ partial("user/loginForm.tpl") ]]
		<div class="quickLoginRegister">
			<a href="[[ url("user/register") ]]">{t _not_registered}</a>
		</div>
	</div>
</div>