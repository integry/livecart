{loadJs form=true}

<div class="userLogin">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _login}</h1>
	
	<h2>{t Returning Customer}</h2>
	
	<p>
        {if $failed}           
            <div class="errorMsg" style="margin: 0;">
                {t Login failed. Please make sure that your e-mail and password are entered correctly.}
            </div>                
        {else}
            <label></label>
            {t Please sign in}
        {/if}
    </p>
	
	{capture assign="return"}{link controller=user}{/capture}
	{include file="user/loginForm.tpl"}
		
	<h2>{t New Customer}</h2>

        <label></label>
    	Not registered yet?
	
	{include file="user/regForm.tpl"}
	
</div>

{include file="layout/frontend/footer.tpl"}

</div>