<p>Delivery zones - regions that follow the same certain rules of shipping and taxes. This means that you can create different shipping and taxation charges for countries, states, cities or even districts and streets. Once created, delivery zones apply particular rates according to customers' addresses. That is, the closest match of the existing delivery zones ir referenced to the customers address. If no match is found, the <a href="{self}#default">default zone's</a> rates are applied.</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create delivery zone</a></li>
	<li><a href="{self}#default">Default zone</a></li>
	<li><a href="{self}#manage">Manage zones</a></li>
	<li><a href="{self}#delete">Delete zone</a></li>

</ul>
</fieldset>
</div>

<h3 id="create">Create delivery zone</h3>	
<p>To create a new delivery zone you have to define its area / location, shipping rates and taxation. </p>	
<p><strong>Defining location</strong></p>
<ol>
	<li>Under the "Delivery Zones" tree click the "Add" link:</li>
	<img src="image/doc/settings/delivery/add.bmp">
	<li>Enter zone's name and click the "Add" button.</li>
	<li>Group countries by adding them to the left "Country" box. Select a country from the country list on the right and use the "<<" button to add it your list.</li>
	<img src="image/doc/settings/delivery/one_two.bmp">
	<p class="note"><strong>Note</strong>: You can select multiple countries by holding the "ctrl" or "shift" key. Also you can select multiple countries by clicking one of the regions: Africa, Asia, Europe, North America, South America, Oceania or European Union.</p>
	<li>State - for United States you can also group states as countries above.</li>
	<img src="image/doc/settings/delivery/states.png">
	<li>City mask are used to extract city matches. It is common to use an asterisk ("*") for any number of unknown characters. For example, "New*" corresponds to "New York", New Jersey", etc.</li>
	<li>The same way Zip mask is used to filter zip codes. For example, "55*" corresponds to "55344", "55555", etc</li>
	<li>Address mask - for example, "* street" corresponds to "5th Street", etc.</li>
	<p>To edit or remove any of the masks, click on the appropriate icon:</p>
	<img src="image/doc/settings/delivery/icon.bmp">
</ol>

<p><strong>Defining shipping rates</strong></p>
<p>These are the rates that determine various pricing according to order's weight, value or delivery time. Shipping rates can be defined manually or you can use realtime shipping methods such as USPS, FedEx, etc. Realtime shipping methods can be enabled and configured in the Settings -> <a href="{help /settings.configuration}#shipping">Configuration area</a>. To add a custom shipping service:</p>
<ol>
	<li>Click on the "Shipping Rates" tab.</li>
	<img src="image/doc/settings/delivery/shipping_tab.bmp">
	<li>Name - enter the name of the shipping service (for example, "Extra heavy")</li>
	<li>Select "Weight based calculation" or "Subtotal based calculation" as necessary.</li>
	<li>Click the "Add new rate" link and fill in the necessary fields:</li>
	<img src="image/doc/settings/delivery/rates.bmp">
	<ul>
		<li>Weight / Subtotal range - weight or subtotal range of the order.</li>
		<li>Flat charge - a single charge for the whole order.</li>
		<li>Per item charge - a charge that is applied per every item in order.</li>
		<li>Per kg charge - a charge that is applied per weight unit.</li>
    </ul>
	<li>Translate - if applicable, select a language to translate the name of the service.</li>
	<img src="image/doc/translate.bmp">
	<li>Click the "Save" button.</li>
</ol>

<p>If you have several shipping services you can sort them to define the way they are displayed to customers. To change the order, click and hold a shipping service and move it up or down:</p>
<img src="image/doc/settings/delivery/sort.bmp">

<p>To edit or delete existing shipping service, click on the appropriate icon:</p>
<img src="image/doc/settings/delivery/icon2.bmp">
<p><strong>Defining tax rates</strong></p>

<ol>
	<li>Click on the "Tax Rates" tab.</li>
	<img src="image/doc/settings/delivery/tax_tab.bmp">
	<li>Click the "Add new tax rate" link.</li>
	<li>Click a drop-down list to select a tax. (Taxes can be created in the Settings -> <a href="{help /settings.taxes}">Taxes</a> section) </li>
	<li>Enter a tax rate.</li>
	<li>Click "Save".</li>
</ol>
<p class="note"><strong>Note</strong>: If you create more than one tax for a delivery zone, all of them will be included into shipments cost.</p>

<h3 id="default">Default zone</h3>	
<p>The default zone is the main zone which is applied to shipping and taxation if no other delivery zones are applicable (to a certain address). You can manage default zone's shipping and tax rates as in the section above.</p>

<h3 id="manage">Manage zones</h3>	

<p>If you need to edit a delivery zone, select it on the delivery zone tree. The "Countries and States" tab opens. You can manage any zone's parameter as in the <a href="{self}#create">Create a delivery zone</a> section.</p>

	
<h3 id="delete">Delete zone</h3>	
<p>To delete a delivery zone, select the zone and click the "Delete" link:</p>
<img src="image/doc/settings/delivery/delete.bmp">

{helpSeeAlso}
	{see settings.configuration}
	{see settings.taxes}
	{see settings.currencies}
	{see settings.languages}
{/helpSeeAlso}
