<div id="countriesAndStatesMsg_{$categoryId}"></div>

{form id="countriesAndStates_$zoneID" handle=$form action="controller=backend.deliveryZone action=save id=$zoneID" method="post" role="delivery.update"}
	<label>{t _name}</label>
	<fieldset class="error">
		{textfield name="name" class="observed countriesAndStates_name"}
		<span class="progressIndicator" style="display: none;"></span>
		<span class="errorText hidden"> </span>
	</fieldset>


	<fieldset class="checkbox error">
		{checkbox name="isEnabled" id="countriesAndStates_isEnabled_`$zoneID`" class="checkbox observed"}
		<span class="progressIndicator" style="display: none;"></span>
		<label class="checkbox" for="countriesAndStates_isEnabled_{$zoneID}">{t _is_enabled}</label>
	</fieldset>

	<fieldset class="checkbox error">
		{checkbox name="isFreeShipping" id="countriesAndStates_isFreeShipping_`$zoneID`" class="checkbox observed"}
		<span class="progressIndicator" style="display: none;"></span>
		<label class="checkbox" for="countriesAndStates_isFreeShipping_{$zoneID}">{t _is_free_shipping}</label>
	</fieldset>

	<fieldset class="checkbox error">
		{checkbox name="isRealTimeDisabled" id="countriesAndStates_isRealTimeDisabled_`$zoneID`" class="checkbox observed"}
		<span class="progressIndicator" style="display: none;"></span>
		<label class="checkbox" for="countriesAndStates_isRealTimeDisabled_{$zoneID}">{t _is_real_time_disabled}</label>
	</fieldset>


	<label>{t _country}</label>
	<fieldset class="error">
		{selectfield name="activeCountries" size="15" class="countriesAndStates_activeCountries" multiple="multiple" options=$selectedCountries}
		<input type="button" value="&lt;&lt;" class="submit countriesAndStates_addCountry" {denied role='delivery.update'}style="display: none"{/denied} />
		<input type="button" value="&gt;&gt;" class="submit countriesAndStates_removeCountry" {denied role='delivery.update'}style="display: none"{/denied} />
		{selectfield name="inactiveCountries" size="15" options=$countries class="countriesAndStates_inactiveCountries" multiple="multiple"}

		<span class="errorText hidden"> </span>

		<div class="countriesAndStates_regionsAndUnions" {denied role='delivery.update'}style="display: none"{/denied}>
			{foreach key="groupName" item="group" from=$countryGroups}
				<div><a href="#{$groupName}">{translate text=$groupName}</a>&nbsp;&nbsp;</div>
			{/foreach}
		</div>
	</fieldset>

	<label>{t _state}</label>
	<fieldset class="error">
		<div style="float: left;">
			{selectfield name="activeStates" size="17" options=$selectedStates class="countriesAndStates_activeStates" multiple="multiple"}
			<input type="button" value="&lt;&lt;" class="submit countriesAndStates_addState" {denied role='delivery.update'}style="display: none"{/denied} />
			<input type="button" value="&gt;&gt;" class="submit countriesAndStates_removeState" {denied role='delivery.update'}style="display: none"{/denied} />
		</div>
		<div style="float: left; padding-left: 5px;">
			<p>
				{selectfield name="stateListCountry" class="stateListCountry" options=$allCountries}
				<span class="progressIndicator" style="display: none;"></span>
			</p>
			{selectfield name="inactiveStates" size="15" options=$states class="countriesAndStates_inactiveStates" multiple="multiple"}
			<span class="errorText hidden"> </span>
		</div>
	</fieldset>

	<fieldset class="countriesAndStates_cityMasks error">
		<label>{t _city_mask}</label>
		<fieldset>
			<script type="text/jscript">
				{literal}
					ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_cityMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksCity, function() {});
				{/literal}
			</script>
			<fieldset class="error" {denied role='delivery.update'}style="display: none"{/denied}>
				<div class="countriesAndStates_maskForm" style="display: none">
					{textfield name="mask_new" class="countriesAndStates_newMask"}
					<input class="submit button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
					{t _or}
					<a href="#cancel" class="cancel countriesAndStates_cancelNewMask">{t _cancel}</a>
					<div class="countriesAndStates_exampleMask">{t _example}: Vilnius</div>
					<span class="errorText hidden"> </span>
				</div>
				<a href="#showMask" class="menu countriesAndStates_showNewMaskForm">{t _add_new_mask}</a>
			</fieldset>
			<ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_cityMasksList" id="countriesAndStates_{$zoneID}_cityMasks">
				{foreach from=$cityMasks item="mask"}
					<li id="countriesAndStates_{$zoneID}_cityMasks_{$mask.ID}">
						{include file="backend/deliveryZone/mask.tpl" mask=$mask}
					</li>
					<script type="text/javascript">
						Backend.DeliveryZone.CountriesAndStates.prototype.bindExistingMask("countriesAndStates_{$zoneID}_cityMasks_{$mask.ID}");
					</script>
				{/foreach}
			</ul>
		</fieldset>
	</fieldset>

	<fieldset class="countriesAndStates_zipMasks error">
		<label>{t _zip_mask}</label>
		<fieldset>
			<script type="text/jscript">
				{literal}
					ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_zipMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksZip, function() {});
				{/literal}
			</script>
			<fieldset class="error" {denied role='delivery.update'}style="display: none"{/denied}>
				<div class="countriesAndStates_maskForm" style="display: none">
					{textfield name="mask_new" class="countriesAndStates_newMask"}
					<input class="submit button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
					{t _or}
					<a href="#cancel" class="cancel countriesAndStates_cancelNewMask">{t _cancel}</a>
					<div class="countriesAndStates_exampleMask">{t _example}: 902?? or 902* or 90210</div>
					<span class="errorText hidden"> </span>
				</div>
				<a href="#showMask" class="menu countriesAndStates_showNewMaskForm">{t _add_new_mask}</a>
			</fieldset>
			<ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_zipMasksList" id="countriesAndStates_{$zoneID}_zipMasks">
				{foreach from=$zipMasks item="mask"}
					<li id="countriesAndStates_{$zoneID}_zipMasks_{$mask.ID}">
						{include file="backend/deliveryZone/mask.tpl" mask=$mask}
					</li>
					<script type="text/javascript">
						Backend.DeliveryZone.CountriesAndStates.prototype.bindExistingMask("countriesAndStates_{$zoneID}_zipMasks_{$mask.ID}");
					</script>
				{/foreach}
			</ul>
		</fieldset>
	</fieldset>

	<fieldset class="countriesAndStates_addressMasks error">
		<label>{t _address_mask}</label>
		<fieldset>
			<script type="text/jscript">
				{literal}
					ActiveList.prototype.getInstance("countriesAndStates_{/literal}{$zoneID}{literal}_addressMasks", Backend.DeliveryZone.CountriesAndStates.prototype.CallbacksAddress, function() {});
				{/literal}
			</script>
			<fieldset class="error" {denied role='delivery.update'}style="display: none"{/denied}>
				<div class="countriesAndStates_maskForm" style="display: none">
					{textfield name="mask_new" class="countriesAndStates_newMask"}
					<input class="submit button countriesAndStates_newMaskButton" type="button"  value="{t _add_mask}" />
					{t _or}
					<a href="#cancel" class="cancel countriesAndStates_cancelNewMask">{t _cancel}</a>
					<div class="countriesAndStates_exampleMask">{t _example}: Vytenio *</div>
					<span class="errorText hidden"> </span>
				</div>
				<a href="#showMask" class="menu countriesAndStates_showNewMaskForm">{t _add_new_mask}</a>
			</fieldset>
			<ul class="activeList {allowed role='delivery.update'}activeList_add_delete activeList_add_edit{/allowed} countriesAndStates_addressMasksList" id="countriesAndStates_{$zoneID}_addressMasks">
				{foreach from=$addressMasks item="mask"}
					<li id="countriesAndStates_{$zoneID}_addressMasks_{$mask.ID}">
						{include file="backend/deliveryZone/mask.tpl" mask=$mask}
					</li>
					<script type="text/javascript">
						Backend.DeliveryZone.CountriesAndStates.prototype.bindExistingMask("countriesAndStates_{$zoneID}_addressMasks_{$mask.ID}");
					</script>
				{/foreach}
			</ul>
		</fieldset>
	</fieldset>

{/form}

<script type="text/javascript">
	Backend.DeliveryZone.CountriesAndStates.prototype.getInstance('countriesAndStates_{$zoneID}', {$zoneID});
</script>