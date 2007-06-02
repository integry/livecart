<div id="countriesAndStatesMsg_{$categoryId}"></div>

{form id="countriesAndStates_$zoneID" handle=$form action="controller=backend.deliveryZone action=save id=$zoneID" method="post" role="delivery.update"}
    <label>{t _name}</label>
    <fieldset class="error">
		{textfield name="name_`$defaultLanguageCode`" class="observed countriesAndStates_name"}
		<span class="errorText hidden"> </span>
    </fieldset>
        
    {language}
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name_`$lang.ID`" class="observed"}
			<span class="errorText hidden"> </span>
		</fieldset>
    {/language}    
    
    <label>{t _country}</label>
    <fieldset class="error">
        {selectfield name="activeCountries" size="15" class="countriesAndStates_activeCountries" multiple="multiple" options=$selectedCountries}
        <input type="button" value="&lt;&lt;" class="submit countriesAndStates_addCountry" style="{denied role='delivery.update'}display: none{/denied}" />
        <input type="button" value="&gt;&gt;" class="submit countriesAndStates_removeCountry" style="{denied role='delivery.update'}display: none{/denied}" />
        {selectfield name="inactiveCountries" size="15" options=$countries class="countriesAndStates_inactiveCountries" multiple="multiple"}
        
        <span class="errorText hidden"> </span>
        
        <div class="countriesAndStates_regionsAndUnions" style="{denied role='delivery.update'}display: none{/denied}">
            {foreach key="groupName" item="group" from=$countryGroups}
                <div><a href="#{$groupName}">{translate text=$groupName}</a></div>
            {/foreach}
        </div>
    </fieldset>

    <label>{t _state}</label>
    <fieldset class="error">
        {selectfield name="activeStates" size="15" options=$selectedStates class="countriesAndStates_activeStates" multiple="multiple"}
        <input type="button" value="&lt;&lt;" class="submit countriesAndStates_addState" style="{denied role='delivery.update'}display: none{/denied}" />
        <input type="button" value="&gt;&gt;" class="submit countriesAndStates_removeState" style="{denied role='delivery.update'}display: none{/denied}" />
        {selectfield name="inactiveStates" size="15" options=$states class="countriesAndStates_inactiveStates" multiple="multiple"}
		<span class="errorText hidden"> </span>
    </fieldset>
    <fieldset class="countriesAndStates_cityMasks error">
    	<label>{t _city_mask}</label>
        <fieldset>
            <ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_cityMasksList" id="countriesAndStates_{$zoneID}_cityMasks">
                {foreach from=$cityMasks item="mask"}
                    <li id="countriesAndStates_{$zoneID}_cityMasks_{$mask.ID}">
                        {include file="backend/deliveryZone/mask.tpl" mask=$mask}
                    </li>
                {/foreach}
            </ul>
            <script type="text/jscript">
                {literal}
                    ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_cityMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksCity, function() {});
                {/literal}
            </script>

            <fieldset class="error" style="{denied role='delivery.update'}display: none{/denied}">
                {textfield name="mask_new" class="countriesAndStates_newMask"}<input class="button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
                <span class="errorText hidden"> </span>
            </fieldset>
        </fieldset>
    </fieldset>
    
    <fieldset class="countriesAndStates_zipMasks error">
    	<label>{t _zip_mask}</label>
        <fieldset>
            <ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_zipMasksList" id="countriesAndStates_{$zoneID}_zipMasks">
                {foreach from=$zipMasks item="mask"}
                    <li id="countriesAndStates_{$zoneID}_zipMasks_{$mask.ID}">
                        {include file="backend/deliveryZone/mask.tpl" mask=$mask}
                    </li>
                {/foreach}
            </ul>
            <script type="text/jscript">
                {literal}
                    ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_zipMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksZip, function() {});
                {/literal}
            </script>
            <fieldset class="error" style="{denied role='delivery.update'}display: none{/denied}">
                {textfield name="mask_new" class="countriesAndStates_newMask"}<input class="button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
                <span class="errorText hidden"> </span>
            </fieldset>
        </fieldset>
    </fieldset>
    
    <fieldset class="countriesAndStates_addressMasks error">
    	<label>{t _address_mask}</label>
        <fieldset>
            <ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_addressMasksList" id="countriesAndStates_{$zoneID}_addressMasks">
                {foreach from=$addressMasks item="mask"}
                    <li id="countriesAndStates_{$zoneID}_addressMasks_{$mask.ID}">
                        {include file="backend/deliveryZone/mask.tpl" mask=$mask}
                    </li>
                {/foreach}
            </ul>
            <script type="text/jscript">
                {literal}
                    ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_addressMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksAddress, function() {});
                {/literal}
            </script>
            <fieldset class="error" style="{denied role='delivery.update'}display: none{/denied}">
                {textfield name="mask_new" class="countriesAndStates_newMask"}<input class="button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
                <span class="errorText hidden"> </span>
            </fieldset>
        </fieldset>
    </fieldset>

{/form}



<script type="text/javascript">
    Backend.DeliveryZone.CountriesAndStates.prototype.getInstance('countriesAndStates_{$zoneID}', {$zoneID});
</script>