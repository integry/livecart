{loadJs form=true}

<div class="userChangePassword">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _change_pass}</h1>
	
	{include file="user/userMenu.tpl" current="passwordMenu"}
	
    {form action="controller=user action=doChangePassword" method="POST" handle=$form}    
        
    	<p class="required">
    	    <label for="currentpassword">{t _current_pass}:</label>
    	    
    		<fieldset class="error">
    			{textfield type="password" name="currentpassword" class="text"}
    			<div class="errorText hidden{error for="currentpassword"} visible{/error}">{error for="currentpassword"}{$msg}{/error}</div>
    		</fieldset>
    	</p>    

    	<p class="required">
    	    <label for="password">{t _enter_new_pass}:</label>
    	    
    		<fieldset class="error">
    			{textfield type="password" name="password" class="text"}
    			<div class="errorText hidden{error for="password"} visible{/error}">{error for="password"}{$msg}{/error}</div>
    		</fieldset>
    	</p>
    
    	<p class="required">
    	    <label for="confpassword">{t _reenter_new_pass}:</label>
    	    
    		<fieldset class="error">
    			{textfield type="password" name="confpassword" class="text"}
    			<div class="errorText hidden{error for="confpassword"} visible{/error}">{error for="confpassword"}{$msg}{/error}</div>
    		</fieldset>
    	</p>    
    	
    	<p>
            <label></label>
        	<input type="submit" class="submit" value="{tn _complete_pass_change}" />
        	<label class="cancel">
        	   {t _or} <a class="cancel" href="{link controller=user}">{t _cancel}</a>
        	</label>
        </p>
            
    {/form}    
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>