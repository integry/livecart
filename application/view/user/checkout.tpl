{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _order_checkout}</h1>
	
	<h2>{t Returning Customer}</h2>
	
	<p>
        Please log in to complete your purchase.
    </p>
	
	{capture assign="return"}{link controller=checkout action=selectAddress}{/capture}
	{include file="user/loginForm.tpl" return=$return}
		
	<h2>{t New Customer}</h2>

    {form handle=$form action="controller=user action=processCheckoutRegistration" method="POST"}
        
        <h3>{t _contact_info}</h3>               

            <p class="required">
                <label for="firstName">{t _your_first_name}:</label>
                
        		<fieldset class="error">
        			{textfield name="firstName" class="text"}
        			<div class="errorText hidden{error for="firstName"} visible{/error}">{error for="firstName"}{$msg}{/error}</div>
        		</fieldset>
            </p>
            
            <p class="required">
                <label for="lastName">{t _your_last_name}:</label>
                
        		<fieldset class="error">
        			{textfield name="lastName" class="text"}
        			<div class="errorText hidden{error for="lastName"} visible{/error}">{error for="lastName"}{$msg}{/error}</div>
        		</fieldset>
            </p>

            <p>
                <label for="companyName">{t _company_name}:</label>
                
        		<fieldset class="error">
        			{textfield name="companyName" class="text"}
        			<div class="errorText hidden{error for="companyName"} visible{/error}">{error for="companyName"}{$msg}{/error}</div>
        		</fieldset>
            </p>

            <p class="required">
                <label for="email">{t _your_email}:</label>
                
        		<fieldset class="error">
        			{textfield name="email" class="text"}
        			<div class="errorText hidden{error for="email"} visible{/error}">{error for="email"}{$msg}{/error}</div>
        		</fieldset>
            </p>

            <p{if $form|isRequired:"phone"} class="required"{/if}>
                <label for="phone">{t _your_phone}:</label>
        		<fieldset class="error">
        			{textfield name="phone" class="text"}
        			<div class="errorText hidden{error for="phone"} visible{/error}">{error for="phone"}{$msg}{/error}</div>
        		</fieldset>
            </p>

        <h3>{t _billing_address}</h3>

            <p class="required">
                <label for="billing_address1">{t _address}:</label>
        		<fieldset class="error">
                    {textfield name="billing_address1" class="text"}
        			<div class="errorText hidden{error for="billing_address1"} visible{/error}">{error for="billing_address1"}{$msg}{/error}</div>
        		</fieldset>
            </p>

            <p>
                <label for="billing_address_2"></label>
                {textfield name="billing_address_2" class="text"}
            </p>
        
            <p class="required">
                <label for="city">{t _city}</label>
        		<fieldset class="error">
                    {textfield name="billing_city" class="text"}
        			<div class="errorText hidden{error for="billing_city"} visible{/error}">{error for="billing_city"}{$msg}{/error}</div>
        		</fieldset>
            </p>
            
            <p class="required">
                <label for="country">{t _country}</label>
        		<fieldset class="error">
                    {selectfield name="billing_country" id="billing_country" options=$countries}
                    <span class="progressIndicator" style="display: none;"></span>
        			<div class="errorText hidden{error for="billing_country"} visible{/error}">{error for="billing_country"}{$msg}{/error}</div>
        		</fieldset>
            </p>

            <p class="required">
                <label for="billing_state_select">{t _state}</label>
        		<fieldset class="error">
                    {selectfield name="billing_state_select" id="billing_state_select" style="display: none;" options=$states}
                    {textfield name="billing_state_text" class="text"}
        			<div class="errorText hidden{error for="billing_state_select"} visible{/error}">{error for="billing_state_select"}{$msg}{/error}</div>
        		</fieldset>

                {literal}
                <script type="text/javascript">
                {/literal}
                    new User.StateSwitcher($('billing_country'), $('billing_state_select'), $('billing_state_text'),
                            '{link controller=user action=states}');       
                </script>
            </p>
            
            <p class="required">
                <label for="billing_zip">{t _postal_code}</label>
        		<fieldset class="error">
                    {textfield name="billing_zip" class="text"}
        			<div class="errorText hidden{error for="billing_zip"} visible{/error}">{error for="billing_zip"}{$msg}{/error}</div>
        		</fieldset>
            </p>            

        <h3>{t _shipping_address}</h3>
        
            <p>
                {checkbox name="sameAsBilling" checked="checked" class="checkbox"}
                <label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
            </p>
            
            <div id="shippingForm">

                <p class="required">
                    <label for="shipping_address1">{t _address}:</label>
            		<fieldset class="error">
                        {textfield name="shipping_address1" class="text"}
            			<div class="errorText hidden{error for="shipping_address1"} visible{/error}">{error for="shipping_address1"}{$msg}{/error}</div>
            		</fieldset>
                </p>
    
                <p>
                    <label for="shipping_address_2"></label>
                    {textfield name="shipping_address_2" class="text"}
                </p>
            
                <p class="required">
                    <label for="city">{t _city}</label>
            		<fieldset class="error">
                        {textfield name="shipping_city" class="text"}
            			<div class="errorText hidden{error for="shipping_city"} visible{/error}">{error for="shipping_city"}{$msg}{/error}</div>
            		</fieldset>
                </p>
                
                <p class="required">
                    <label for="country">{t _country}</label>
            		<fieldset class="error">
                        {selectfield name="shipping_country" id="shipping_country" options=$countries}
            			<span class="progressIndicator" style="display: none;"></span>
                        <div class="errorText hidden{error for="shipping_country"} visible{/error}">{error for="shipping_country"}{$msg}{/error}</div>
            		</fieldset>
                </p>
    
                <p class="required">
                    <label for="shipping_state_select">{t _state}</label>
            		<fieldset class="error">
                        {selectfield name="shipping_state_select" id="shipping_state_select" style="display: none;" options=$states}
                        {textfield name="shipping_state_text" class="text"}
            			<div class="errorText hidden{error for="shipping_state_select"} visible{/error}">{error for="shipping_state_select"}{$msg}{/error}</div>
            		</fieldset>
    
                    {literal}
                    <script type="text/javascript">
                    {/literal}
                        new User.StateSwitcher($('shipping_country'), $('shipping_state_select'), $('shipping_state_text'),
                                '{link controller=user action=states}');   
                        new User.ShippingFormToggler($('sameAsBilling'), $('shippingForm'));
                    </script>
                </p>     
                
                <p class="required">
                    <label for="shipping_zip">{t _postal_code}</label>
            		<fieldset class="error">
                        {textfield name="shipping_zip" class="text"}
            			<div class="errorText hidden{error for="shipping_zip"} visible{/error}">{error for="shipping_zip"}{$msg}{/error}</div>
            		</fieldset>
                </p>                       
                
            </div>
            
            <p>            
                <input type="submit" class="submit" value="{tn Continue}" />
            </p>
    
    {/form}   

</div>

{include file="layout/frontend/footer.tpl"}