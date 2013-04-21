{loadJs form=true}
{pageTitle}{t _change_pass}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="passwordMenu"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doChangePassword" method="POST" handle=$form}

		{input name="currentpassword"}
			{label}{t _current_pass}:{/label}
			{textfield type="password"}
		{/input}

		{input name="password"}
			{label}{t _enter_new_pass}:{/label}
			{textfield type="password"}
		{/input}

		{input name="confpassword"}
			{label}{t _reenter_new_pass}:{/label}
			{textfield type="password"}
		{/input}

		{include file="block/submit.tpl" caption="_complete_pass_change" cancel=user}

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}