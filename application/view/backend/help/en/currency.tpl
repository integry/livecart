<p>With LC you can localize your e-store and provide the product and shipping pricing in visitor's home currency. That is, you can set multiple currencies and allow users to switch between currencies to view the prices of products and conclude payments in the currency they prefer. To accomplish that you have to define which currencies your store will support.</p> 	

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#add">Add new Currency</a></li>
	<li><a href="{self}#set base">Set the Base Currency</a></li>
	<li><a href="{self}#adjust rates">Adjust Exchange Rates</a></li>
	<li><a href="{self}#adjust rates automatically">Set Automatic Exchange Rates Adjust</a></li>
	<li><a href="{self}#enable disable">Enable / Disable Currency</a></li>
	<li><a href="{self}#sort">Sort Currencies</a></li>
	<li><a href="{self}#delete">Delete Currency</a></li>				
</ul>
</fieldset>
</div>

<h3 id="add">Add Currency</h3>
<p>To add a new currency simply click <strong>Add currency</strong> and choose a currency from the pulldown menu followed by (the) <strong>ADD</strong> button.</p>

<h3 id="set base">Set the Base Currency</h3>
<p>The base currency is used as the main currency of your system. All the other currency rates are calculated in respect with the base currency.    <br /><br /><ins>enter product prices in other currencies</ins> <br /> <ins>psychological pricing(for example, set products price to 9.95 even if it's real price would otherwise be 10.05).</ins> <br /><br />If you wish to change the base currency click the <strong>Set as base currency</strong> link under the currency name.</p>
<p class="note">Please note that once you change the base currency all prices have to be recalculated and re-set for all the products.</p>
<img src="image/doc/currency/set_default_currency.bmp">

<h3 id="adjust">Adjust Exchange Rates</h3>
<p>To adjust exchange rates of your (active) currencies click on the <strong>Adjust Exchange Rates</strong> tab to proceed to <a href="{self}#tab_adjust_rates">manual exchange rates</a> setting page.</p>

<h3 id="adjust automaticaly">Automaticaly Adjust Exchange Rates</h3>
<p>Click <a href="{self}#tab_options">Options</a> tab to open automatic currency updater section.</p>

<h3 id="enable disable">Enable or Disable Currencies</h3>
<p>All the currencies are disabled by default, to enable a currency click it's <strong>checkbox</strong> on the left of the currency name. Clear the mark to deactivate it.</p>

<h3 id="sort">Sort Currencies</h3>
<p>Sorting currencies determines the order in which the active currencies will be displayed to your customers in the currency switching menu. To set the appropriate currency order, click on the currency and drag it up or down.</p>
<img src="image/doc/currency/sort_currencies.bmp">

<h3 id="delete">Delete</h3>
<p>To delete a currency, move your mouse over the currency and click <strong>delete</strong> icon on the left.</p>
<img src="image/doc/currency/delete.bmp">

<h3 id="adjust rates">Tab -> Adjust Exchange Rates</h3>
<p>To set the currency exchange rates simply enter currency's value in the appropriate field. For instance, if one United Kingdom Pound is worth 1.96475 US Dollars, enter this value and click <strong>Save</strong> to set the rates. </p>

<img src="image/doc/currency/adjust_exchange_rates_blink.bmp">

<p class="note"><strong>Note</strong>: rates always have to be calculated in relation to the base currency.</p>
<p>See <a href="{self}#adjust rates automatically"> Automatic Adjust</a> for keeping your rates up-to-date.</p>

<h3 id="options">Tab -> Options</h3>
<p>To keep your currency exchange rates up-to-date you have to enable automatic currency updaters. To do that:
<ul>
	<li>Check/click <i>Update currency exchange rates automatically using currency data feeds</i></li>
	<li>Select an update frequency</li>
	<li>Choose which currencies should be updated and select an update engine to use (read about update engines)</li>
</ul>
</p>

<ins>TODO: complete when the updater functionality has been finished</ins>