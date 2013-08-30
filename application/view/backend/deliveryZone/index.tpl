[[ partial("backend/eav/includes.tpl") ]]
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="backend/DeliveryZone.js"}
{includeJs file="backend/User.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="backend/DeliveryZone.css"}

{pageTitle help="settings.delivery"}{t _livecart_delivery_zones}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<script type="text/javascript">
	Backend.DeliveryZone.countryGroups = [[countryGroups]];
</script>

<div id="deliveryZoneWrapper" class="maxHeight h--50">
	<div id="deliveryZoneBrowserWithControlls" class="treeContainer">
		<div id="deliveryZoneBrowser" class="treeBrowser"></div>
		<div id="deliveryZoneBrowserControls">
			<ul class="verticalMenu">
				<li class="addTreeNode"><a id="newZoneInputButton" href="#add" {denied role='delivery.create'}style="display: none"{/denied}>{t _add_new_delivery_zone}</a></li>
				<li class="removeTreeNode"><a id="deliveryZone_delete" href="#delete" {denied role='delivery.remove'}style="display: none"{/denied}>{t _remove}</a></li>
				<li class="testAdress"><a id="deliveryZone_testAddress" href="#testAddress" onclick="$('testAddressForm').show(); return false;">{t _test_address}</a></li>
			</ul>
		</div>

		<div id="testAddressForm" style="display: none;">
			<fieldset>
				<legend>{t _test_which_zone}</legend>
				{form action="backend.deliveryZone/testAddress" method="post" handle=$testAddress onsubmit="Backend.DeliveryZone.lookupAddress(this, event);"}

					{input name="ccCVV"}
						{label}{t _zone_type}:{/label}
						<select name="type">
							<option value="2">{t _shipping_rates}</option>
							<option value="1">{t _tax_rates}</option>
						</select>
					{/input}

					{include file="backend/user/address_edit.tpl" hideName=true}

					<div>
						<input type="submit" class="submit" value="{tn _lookup_zone}" />
						<span class="progressIndicator" style="display: none;"></span>
						{t _or}
						<a href="#cancel" class="cancel" onclick="$('testAddressForm').hide(); return false;">{t _cancel}</a>
					</div>

					<div id="zoneLookupResult" style="display: none;">
						{t _matched_zone}: <span></span>
					</div>
				{/form}
			</fieldset>
		</div>
	</div>

	<div id="deliveryZoneManagerContainer" class="treeManagerContainer">
		<div class="tabContainer">
			<ul class="tabList tabs">
				<li id="tabDeliveryZoneCountry" class="tab active">
					<span class="progressIndicator" style="display: none;"></span>
					<a href="{link controller="backend.deliveryZone" action=countriesAndStates}?id=_id_">{t _countries_and_states}</a>
				</li>
				<li id="tabDeliveryZoneShipping" class="tab inactive hidden">
					<a href="{link controller="backend.shippingService" action=index}?id=_id_">{t _shipping_rates}</a>
				</li>
				<li id="tabDeliveryZoneTaxes" class="tab inactive hidden">
					<a href="{link controller="backend.taxRate" action=index}?id=_id_">{t _tax_rates}</a>
				</li>
			</ul>
		</div>
		<div class="sectionContainer maxHeight h--50"></div>
	</div>
</div>

<div id="activeDeliveryZonePath" class="treePath"></div>

{literal}
<script type="text/javascript">
	Backend.showContainer('deliveryZoneManagerContainer');

	Backend.DeliveryZone.prototype.Messages.confirmZoneDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_zone');
	Backend.DeliveryZone.prototype.Messages.defaultZoneName = Backend.getTranslation('_default_zone');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_address_mask');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmCityDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_city_mask');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmZipDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_zip_mask');
	Backend.DeliveryZone.ShippingService.prototype.Messages.confirmDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_service');
	Backend.DeliveryZone.ShippingRate.prototype.Messages.confirmDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_rate');

	Backend.DeliveryZone.prototype.Links.edit = '{/literal}{link controller="backend.deliveryZone" action=edit}?id=_id_{literal}';
	Backend.DeliveryZone.prototype.Links.remove = '{/literal}{link controller="backend.deliveryZone" action=delete}{literal}';
	Backend.DeliveryZone.prototype.Links.save = '{/literal}{link controller="backend.deliveryZone" action=save}{literal}';
	Backend.DeliveryZone.prototype.Links.create = '{/literal}{link controller="backend.deliveryZone" action=create}{literal}';
	Backend.DeliveryZone.prototype.Links.saveCountries = '{/literal}{link controller="backend.deliveryZone" action=saveCountries}{literal}';
	Backend.DeliveryZone.prototype.Links.saveStates = '{/literal}{link controller="backend.deliveryZone" action=saveStates}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask = '{/literal}{link controller="backend.deliveryZone" action=deleteCityMask}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask = '{/literal}{link controller="backend.deliveryZone" action=saveCityMask}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask = '{/literal}{link controller="backend.deliveryZone" action=deleteZipMask}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask = '{/literal}{link controller="backend.deliveryZone" action=saveZipMask}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask = '{/literal}{link controller="backend.deliveryZone" action=deleteAddressMask}{literal}';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask = '{/literal}{link controller="backend.deliveryZone" action=saveAddressMask}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.remove = '{/literal}{link controller="backend.shippingService" action=delete}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.sortServices = '{/literal}{link controller="backend.shippingService" action=sort}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.edit = '{/literal}{link controller="backend.shippingService" action=edit}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate = '{/literal}{link controller="backend.shippingRate" action=delete}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.sortRates = '{/literal}{link controller="backend.shippingRate" action=sort}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.editRate = '{/literal}{link controller="backend.shippingRate" action=edit}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.update = '{/literal}{link controller="backend.shippingService" action=update}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.create = '{/literal}{link controller="backend.shippingService" action=create}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.validateRates = '{/literal}{link controller="backend.shippingService" action=validateRates}{literal}';

	var zones = new Backend.DeliveryZone({/literal}[[zones]]{literal});

</script>
{/literal}

[[ partial("layout/backend/footer.tpl") ]]