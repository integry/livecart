{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t Password Sent}</h1>
	
	<p>
	   Your new password has been sent to <strong>{$email}</strong>.
	</p>
	
	<p>
	   <span style="color:red; font-weight: bold;">Note:</span> for security reasons this message is displayed even if the email address was not found in our database.
	</p>
	
</div>

{include file="layout/frontend/footer.tpl"}