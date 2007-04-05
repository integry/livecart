{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _order_checkout}</h1>
	
	<h2>New Customer</h2>

        {if "ANONYMOUS_CHECKOUT"|config}

            <p>
                Would you like to register in our store before completing your order?
            </p>
        
            <p>
                <a href="{link controller=user action="register"}">Yes, I would like to register.</a>
            <p>
            
            <p>
                <a href="{link controller=checkout action="selectAddress"}">
                    No, I would like to complete my purchase without registering.
                </a>
            </p>

        {else}
        
        
        {/if}

	<h2>Returning Customer</h2>
	
	<p>
        Please log in to complete your purchase.
    </p>
	
	<form action="{link controller=user action=processLogin}" method="POST" />
        <p>
	       <label for="email">{t Your e-mail address}:</label>
           <input type="text" class="text" id="email" name="email" />
        </p>
        <p>
            <label for="password">{t Your password}:</label>
            <input type="password" class="text" id="password" name="password" />
            <a href="{link controller=user action="remindPassword"}" class="forgottenPassword">
                {t _remind_password}
            </a>            
        </p>	
        
        <input type="hidden" name="return" value="{link controller=checkout action=selectAddress}" />
        <input type="submit" class="submit" value="{tn Login}" />
	</form>	

</div>

{include file="layout/frontend/footer.tpl"}