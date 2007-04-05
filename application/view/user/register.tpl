{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _user_registration}</h1>

    {form handle=$form action="controller=user action=processRegistration" method="POST"}
        
        <h2>{t _contact_info}</h2>               

            <p class="required">
                <label for="name">{t _your_name}:</label>
                {textfield name="name" class="text"}
            </p>
                
            <p class="required">
                <label for="email">{t _your_email}:</label>
                {textfield name="email" class="text"}
            </p>

            <p{if "REQUIRE_PHONE"|config} class="required"{/if}>
                <label for="phone">{t _your_phone}:</label>
                {textfield name="phone" class="text"}
            </p>
                
        <h2>{t _billing_address}</h2>

            <p class="required">
                <label for="billing_address_1">{t _address}:</label>
                {textfield name="billing_address_1" class="text"}
            </p>

            <p>
                <label for="billing_address_2"></label>
                {textfield name="billing_address_2" class="text"}
            </p>
        
            <p class="required">
                <label for="city">{t _city}</label>
                {textfield name="billing_city" class="text"}
            </p>
            
            <p class="required">
                <label for="country">{t _country}</label>
                {selectfield name="billing_country" id="billing_country" options=$countries value="DEF_COUNTRY"|config}
            </p>

            <p class="required">
                <label for="state">{t _state}</label>
                {selectfield name="billing_state_select" id="billing_state_select" style="display: none;"}
                {textfield name="billing_state_text" class="text" value="DEF_STATE"|config}
                {literal}
                <script type="text/javascript">
                {/literal}
                    new User.StateSwitcher($('billing_country'), $('billing_state_select'), $('billing_state_text'),
                            '{link controller=user action=states}');       
                </script>
            </p>

        <h2>{t _shipping_address}</h2>
        
            <p>
                {checkbox name="sameAsBilling" id="sameAsBilling" checked="checked" class="checkbox"}
                <label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
            </p>
    
    {/form}        

</div>

{include file="layout/frontend/footer.tpl"}