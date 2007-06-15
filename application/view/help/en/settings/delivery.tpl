<p>Delivery zones - regions that follow the same certain rules of shipping and taxes. This means that you can create different shipping and taxation charges for countries, states, cities or even districts and streets.</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create delivery zone</a></li>
	<li><a href="{self}#default">Manage default zone</a></li>
	<li><a href="{self}#delete">Delete zone</a></li>

</ul>
</fieldset>
</div>

<h3 id="create">Create delivery zone</h3>	
<p>To add a new delivery zone you have to define location, shipping rates and taxation.</p>	
<p><strong>Defining location</strong></p>
<ul>
	<li>Click add new zone.</li>
	<li>Enter zone's name.</li>
	<li>Group countries by adding them to the left "Country" box. Select a country from the country list and use the "<<" button to add it your list.</li>
	<img src="image/doc/settings/delivery/one_two.bmp">
	<p class="note"><strong>Note</strong>: You can also select multiple countries by holding the "ctrl" or "shift" key.</p>
	<p>Choosing africa, asia, europe, (...)</p>
	<li>States - for United States you can also group states as countries above.</li>
	<li>City mask defines particular cities if necessary. You can also use the "*" character for any number of unknown characters. For example, "New*" corresponds to "New York", New Jersey", etc</li>
	<li>Zip mask is used to filter zip codes. For example, "55*" corresponds to "55344", "55555", etc</li>
	<li>Address mask - for example, "* street" corresponds to "5th Street", etc</li>
</ul>
<p><strong>Defining shipping rates</strong></p>
<p>These are the rates that determine various pricing according to order's weight, value or delivery time. Shipping rates can be defined manually or you can use realtime shipping methods such as USPS, FedEx, etc. Realtime shipping methods can be configured in Settings -> Configuration area. To add a custom shipping service:</p>
<ul>
	<li>Click on the "Shipping Rates" tab.</li>
	<img src="image/doc/settings/delivery/shipping_tab.bmp">
	<li>Name - enter the name of the shipping service (for example, "Extra heavy")</li>
	<li>Select "Weight based calculation" or "Subtotal based calculation" as necessary.</li>
	<li>Click the "Add new rate" link and complete the necessary fields to create a rate.</li>
	<img src="image/doc/settings/delivery/rates.bmp">
	<ul>
		<li>Weight / Subtotal range - weight or subtotal range of the order.</li>
		<li>Flat charge - a single charge for the whole order.</li>
		<li>Per item charge - a charge that is applied per every item in order.</li>
		<li>Per kg charge - a charge that is applied per weight unit</li>
    </ul>
	<li>Translate - if applicable, select a language to translate the name of the service.</li>
	<li>Click the "Save" button.</li>
</ul>

<p><strong>Defining tax rates</strong></p>

<ul>
	<li>Click on the "Tax Rates" tab.</li>
	<img src="image/doc/settings/delivery/tax_tab.bmp">
	<li>Click the "Add new tax rate" link.</li>
	<li>Click a drop-down list to select a tax. (Taxes can be created in the Settings -> Taxes section) </li>
	<li>Enter a tax rate.</li>
	<li>Click "Save".</li>
</ul>

<h3 id="default">Manage default zone</h3>	
<p>Default zone is the main zone which is applied to shipping and taxation if no defined zones are available / applicable. You can manage default zone's shipping and tax rates as in the section above.</p>

<h3 id="delete">Delete zone</h3>	
<p>To delete a delivery zone, select the zone and click the "Delete" button.</p>



