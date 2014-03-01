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
				<li class="testAdress"><a id="deliveryZone_testAddress" href="#testAddress" onclick="('testAddressForm').show(); return false;">{t _test_address}</a></li>
			</ul>
		</div>

		<div id="testAddressForm" style="display: none;">
			<fieldset>
				<legend>{t _test_which_zone}</legend>
				{form action="backend.deliveryZone/testAddress" method="post" handle=testAddress onsubmit="Backend.DeliveryZone.lookupAddress(this, event);"}

					{input name="ccCVV"}
						{label}{t _zone_type}:{/label}
						<select name="type">
							<option value="2">{t _shipping_rates}</option>
							<option value="1">{t _tax_rates}</option>
						</select>
					{/input}

					[[ partial('backend/user/address_edit.tpl', ['hideName': true]) ]]

					<div>
						<input type="submit" class="submit" value="{t _lookup_zone}" />
						<span class="progressIndicator" style="display: none;"></span>
						{t _or}
						<a href="#cancel" class="cancel" onclick="('testAddressForm').hide(); return false;">{t _cancel}</a>
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
					<a href="[[ url("backend.deliveryZone/countriesAndStates") ]]?id=_id_">{t _countries_and_states}</a>
				</li>
				<li id="tabDeliveryZoneShipping" class="tab inactive hidden">
					<a href="[[ url("backend.shippingService/index") ]]?id=_id_">{t _shipping_rates}</a>
				</li>
				<li id="tabDeliveryZoneTaxes" class="tab inactive hidden">
					<a href="[[ url("backend.taxRate/index") ]]?id=_id_">{t _tax_rates}</a>
				</li>
			</ul>
		</div>
		<div class="sectionContainer maxHeight h--50"></div>
	</div>
</div>

<div id="activeDeliveryZonePath" class="treePath"></div>


<script type="text/javascript">
	Backend.showContainer('deliveryZoneManagerContainer');

	Backend.DeliveryZone.prototype.Messages.confirmZoneDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_zone');
	Backend.DeliveryZone.prototype.Messages.defaultZoneName = Backend.getTranslation('_default_zone');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_address_mask');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmCityDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_city_mask');
	Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmZipDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_zip_mask');
	Backend.DeliveryZone.ShippingService.prototype.Messages.confirmDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_service');
	Backend.DeliveryZone.ShippingRate.prototype.Messages.confirmDelete = Backend.getTranslation('_are_you_sure_you_want_to_delete_this_rate');

	Backend.DeliveryZone.prototype.Links.edit = '[[ url("backend.deliveryZone/edit") ]]?id=_id_';
	Backend.DeliveryZone.prototype.Links.remove = '[[ url("backend.deliveryZone/delete") ]]';
	Backend.DeliveryZone.prototype.Links.save = '[[ url("backend.deliveryZone/save") ]]';
	Backend.DeliveryZone.prototype.Links.create = '[[ url("backend.deliveryZone/create") ]]';
	Backend.DeliveryZone.prototype.Links.saveCountries = '[[ url("backend.deliveryZone/saveCountries") ]]';
	Backend.DeliveryZone.prototype.Links.saveStates = '[[ url("backend.deliveryZone/saveStates") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask = '[[ url("backend.deliveryZone/deleteCityMask") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask = '[[ url("backend.deliveryZone/saveCityMask") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask = '[[ url("backend.deliveryZone/deleteZipMask") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask = '[[ url("backend.deliveryZone/saveZipMask") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask = '[[ url("backend.deliveryZone/deleteAddressMask") ]]';
	Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask = '[[ url("backend.deliveryZone/saveAddressMask") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.remove = '[[ url("backend.shippingService/delete") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.sortServices = '[[ url("backend.shippingService/sort") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.edit = '[[ url("backend.shippingService/edit") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate = '[[ url("backend.shippingRate/delete") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.sortRates = '[[ url("backend.shippingRate/sort") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.editRate = '[[ url("backend.shippingRate/edit") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.update = '[[ url("backend.shippingService/update") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.create = '[[ url("backend.shippingService/create") ]]';
	Backend.DeliveryZone.ShippingService.prototype.Links.validateRates = '[[ url("backend.shippingService/validateRates") ]]';

	var zones = new Backend.DeliveryZone([[zones]]);

</script>


[[ partial("layout/backend/footer.tpl") ]]