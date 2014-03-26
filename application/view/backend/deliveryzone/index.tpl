<div ng-controller="DeliveryZoneController" ng-init="setZones([[ json(zones) ]]); setCountries([[ json(countries) ]]); expandRoot();">

	<a class="btn btn-primary" ng-click="add()">
		<span class="glyphicon glyphicon-plus-sign"></span>
		{t _create_zone}
	</a>
	
	<br /><br />

	<div ng-repeat="(key, section) in zones">
		<h2 ng-show="key > 0">{{ section.title }}</h2>
		<div ng-repeat="zone in section.children">
			<a class="btn btn-default" ng-click="open(zone.ID)">{{ zone.name }}</a>
		</div>
	</div>
		

				{# 
				<li class="testAdress"><a id="deliveryZone_testAddress" href="#testAddress" onclick="('testAddressForm').show(); return false;">{t _test_address}</a></li>
				
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

				#}
</div>

{#
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
#}
