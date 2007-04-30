{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _login}</h1>
	
	<h2>{t Returning Customer}</h2>
	
	<p>
        Please sign in.
    </p>
	
	{capture assign="return"}{link controller=user}{/capture}
	{include file="user/loginForm.tpl"}
		
	<h2>{t New Customer}</h2>

	Not registered yet?
	
	{include file="user/regForm.tpl"}
	
</div>

{include file="layout/frontend/footer.tpl"}