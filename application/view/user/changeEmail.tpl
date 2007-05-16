{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _change_email}</h1>
	
	{include file="user/userMenu.tpl" current="emailMenu"}
	
    {form action="controller=user action=doChangeEmail" method="POST" handle=$form}    
        
    	<p>
    	    <label>{t _current_email}:</label>
            <label class="currentEmail">{$user.email}</label>
    	</p>    

    	<p class="required">
    	    <label for="email">{t _new_email}:</label>
    	    
    		<fieldset class="error">
    			{textfield name="email" class="text"}
    			<div class="errorText hidden{error for="email"} visible{/error}">{error for="email"}{$msg}{/error}</div>
    		</fieldset>
    	</p>    

    	<p>
            <label></label>
        	<input type="submit" class="submit" value="{tn _complete_email_change}" />
        	<label class="cancel">
        	   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
        	</label>
        </p>            
        
    {/form}    
    
</div>

{include file="layout/frontend/footer.tpl"}