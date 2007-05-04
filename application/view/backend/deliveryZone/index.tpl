{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/SectionExpander.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="backend/DeliveryZone.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="backend/DeliveryZone.css"}

{pageTitle help="deliveryZone"}{t _livecart_delivery_zones}{/pageTitle}
{include file="layout/backend/header.tpl"}

<script type="text/javascript">
    Backend.DeliveryZone.countryGroups = {$countryGroups};
</script>

<div id="deliveryZoneWrapper" class="maxHeight h--50">
	<div id="deliveryZoneBrowserWithControlls">
    	<div id="deliveryZoneBrowser" class="treeBrowser"></div>
        <div id="deliveryZoneBrowserControls">
            <input type="text" name="name" id="newZoneInput" />
            <input type="button" class="button" value="{t _add}" id="newZoneInputButton"  />
            <br />
            <a id="deliveryZone_delete" href="#delete">{t _delete}</a>
        </div>
	</div>
    
    <div id="deliveryZoneManagerContainer" class="managerContainer">
    	<div class="tabContainer">
    		<ul class="tabList tabs">
    			<li id="tabDeliveryZoneCountry" class="tab active">
    				<a href="{link controller=backend.deliveryZone action=countriesAndStates id=_id_}">{t Countries and States}</a>
    				<span class="tabHelp">deliveryZone.countriesAndStates</span>
    			</li>
    			
    			<li id="tabDeliveryZoneShipping" class="tab inactive">
    				<a href="{link controller=backend.shippingService action=index id=_id_}">{t Shipping Rates}</a>
    				<span class="tabHelp">deliveryZone.shippingRates</span>
    			</li>
    			
    			<li id="tabDeliveryZoneTaxes" class="tab inactive">
    				<a href="{link controller=backend.deliveryZone action=taxRates id=_id_}">{t Tax Rates}</a>
    				<span class="tabHelp">deliveryZone.taxRates</span>
    			</li>
			</ul>
    	</div>
    	<div class="sectionContainer maxHeight h--50"></div>
    </div>
</div>

<div id="activeDeliveryZonePath"></div>

{literal}
<script type="text/javascript">
    Backend.DeliveryZone.prototype.Messages.confirmZoneDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_zone}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_address_mask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmCityDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_city_mask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmZipDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_zip_mask}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Messages.confirmRateDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_rate}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Messages.confirmServiceDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_service}{literal}';
    Backend.DeliveryZone.ShippingRate.prototype.Messages.save = '{/literal}{t _save}{literal}';
    Backend.DeliveryZone.ShippingRate.prototype.Messages.add = '{/literal}{t _add}{literal}';
    
    
    Backend.DeliveryZone.prototype.Links.edit = '{/literal}{link controller=backend.deliveryZone action=edit}?id=_id_{literal}';
    Backend.DeliveryZone.prototype.Links.remove = '{/literal}{link controller=backend.deliveryZone action=delete}{literal}';
    Backend.DeliveryZone.prototype.Links.save = '{/literal}{link controller=backend.deliveryZone action=save}{literal}';
    Backend.DeliveryZone.prototype.Links.saveCountries = '{/literal}{link controller=backend.deliveryZone action=saveCountries}{literal}';
    Backend.DeliveryZone.prototype.Links.saveStates = '{/literal}{link controller=backend.deliveryZone action=saveStates}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask = '{/literal}{link controller=backend.deliveryZone action=deleteCityMask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask = '{/literal}{link controller=backend.deliveryZone action=saveCityMask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask = '{/literal}{link controller=backend.deliveryZone action=deleteZipMask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask = '{/literal}{link controller=backend.deliveryZone action=saveZipMask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask = '{/literal}{link controller=backend.deliveryZone action=deleteAddressMask}{literal}';
    Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask = '{/literal}{link controller=backend.deliveryZone action=saveAddressMask}{literal}';
	Backend.DeliveryZone.ShippingService.prototype.Links.deleteService = '{/literal}{link controller=backend.shippingService action=delete}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Links.sortServices = '{/literal}{link controller=backend.shippingService action=sort}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Links.editService = '{/literal}{link controller=backend.shippingService action=edit}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate = '{/literal}{link controller=backend.shippingRate action=delete}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Links.sortRates = '{/literal}{link controller=backend.shippingRate action=sort}{literal}';
    Backend.DeliveryZone.ShippingService.prototype.Links.editRate = '{/literal}{link controller=backend.shippingRate action=edit}{literal}';
    
    
    var zones = new Backend.DeliveryZone({/literal}{$zones}{literal});

</script>
{/literal}

{include file="layout/backend/footer.tpl"}