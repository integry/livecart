{loadJs form=true}

<div class="userRegister">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _user_registration}</h1>
		
	{include file="user/regForm.tpl"}
	
</div>

{include file="layout/frontend/footer.tpl"}

</div>