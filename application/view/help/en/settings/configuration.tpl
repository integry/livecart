<p>Configuration settings are devided into several groups. You can access particular settings by clicking a link below.</p> 	

<div class="tasks">
<fieldset>
<legend>Things discussed here</legend>
<ul>	
	<li><a href="{self}#main">Main settings</a></li>
	<li><a href="{self}#general">General Configuration</a></li>
	<li><a href="{self}#products">Products</a></li>
	<li><a href="{self}#images">Images</a></li>
	<li><a href="{self}#product_filters">Product Filters</a></li>
	<li><a href="{self}#inventory">Inventory</a></li>
	<li><a href="{self}#checkout">Checkout</a></li>
	<li><a href="{self}#purchase">Purchase</a></li>
	<li><a href="{self}#shipping">Shipping</a></li>
	<li><a href="{self}#email">Email</a></li>
	<li><a href="{self}#customers">Customers</a></li>
	<li><a href="{self}#payment_methods">Payment Methods</a></li>
	<li><a href="{self}#enabled_countries">Enabled Countries</a></li>
	<li><a href="{self}#backend">Backend administration</a></li>
</ul>
</fieldset>
</div>

<h3 id="main">Main Settings</h3>
<ul>
	<li>Store name - the store name is displayed in your storefront and is seen by your customers.</li>
	<li>Translate - click on the language tab to translate the store name (if applicable).</li>
</ul>


<h3 id="general">General Configuration</h3>

<p>General configuration allows you to manage the following parameters:</p>
<img src="image/doc/settings/general.bmp"/>

<ul>
	<li>Number of products per category page - the number of products displayed in a {glossary}product list{/glossary} page.</li>
	<li>Allow sort parameters - mark the parameters by which users will be able to sort products.</li>
	<li>Default product sort order - click the drop-down list and select the dafault sorting parameter.</li>
	<li>Display the number of products per category - enables the number of products displayed with a category.</li>
	<li>Display the number of products per filter - enables the number of products displayed with a filter.</li>
	<li>Display product thumbnail image in product list - enables small images of products in a product list page.</li>
</ul>

<h3 id="products">Products</h3>
<ul>
	<li>Unit measurement system - click the drop-down list and select one of the unit measurement systems to use (for product details). Metric system has units such as "meter", "liter" while English units are "inch", "gallon" etc.</li>
</ul>	



<h3 id="images">Images</h3>

<p>You can set image sizes for products and categories. According to preset image sizes, all the uploaded images will be resized accordingly. You can preset four types of images: small, thumbnail, medium and large. You can also set image quality which affects the size of the image (the better the quality the bigger the size).</p>
<ul>
	<li>Small images are tiny winy images which are displayed in a {glossary}product details{/glossary} page.</li>
	<li>Thumbnail images are displayed in a {glossary}product list{/glossary} page.</li>
	<li>Medium images are displayed when customers click on the small image in the product details page.</li>
	<li><ins>Large images are used (...)</ins></li>
</ul>

<h3 id="product_filters">Filters</h3>

<ul>
	<li>Max number of filter criterias to display without expanding - defines how many filtering options are available per one filter without expanding all possible criteria.</li>
	<li>Price filters - there is only one price filter which is used for all categories. Fill out the following fields to define filter's criteria:</li>
	<img src="image/doc/settings/criterion.bmp"/>
	<ul>
		<li>Filter Name - the name of the criterion (for example, "100 to 199")</li>
		<li>Price From - the bottom line of the price.</li>
		<li>Price To - the max price value.</li>
	</ul>
	<li>Translate - click on the language tab to enter filter's criteria names if applicable.</li>
</ul>
<p class="note"><strong>Note</strong>: Fractional parts should be also defined as there is no rounding applied to such prices as "2.49" or "9.99". Thus criteria have to be set up appropriately: "0 to 2.50" and "2.51 to 9.99" or similar.</p>

<h3 id="inventory">Inventory</h3>

<ul>
	<li>Disable inventory tracking - mark the checkbox to allow ordering products that are out of stock. Clear the checkbox to prevent customers from ordering products that are out of stock ("Add to cart" link is not displayed)</li>
	<li>Disable products that are out of stock - mark the checkbox to hide products that are out of stock (won't be displayed at all).</li>
</ul>

<h3 id="checkout">Checkout</h3>

<ul>
	<li>Require Card Verification Code (CVV) to be entered - the CVV code is required at the end of the checkout process.</li>
</ul>

<h3 id="purchase">Purchase</h3>

<ul>
	<li>Minimum order total - the minimum total price per order.</li>
	<li>Maximum order total - the maximum value of one order.</li>
	<li>Maximum quantity of products per order - the limitation of products per one order.</li>
</ul>

<h3 id="shipping">Shipping methods</h3>

<p>Here you can set-up and manage realtime shipping methods  such as USPS, FedEx, etc. which use postage calculation methods.</p>

<h4 id="usps">Setting up USPS</h3>

<p>Main settings:</p>
<ul>	
	<li>Enable USPS as shipping method - the shipping method will be available in the checkout process.</li>
</ul>

<p>USPS Shipping API Access</p>
<ul>	
	<li>USPS API username - obtained from USPS (www.usps.gov.com)</li>
	<li>USPS API URL - (...)</li>
</ul>

<p>Domestic Shipping Options</p>
<ul>	
	<li>Enabled mailing services - </li>
	<li>Priority mailing service - </li>
	<li>Package is machinable - a parcel that usualy a regular shipping package.</li>
</ul>

<p>International Shipping Options</p>
<ul>	
	<li>Enabled international mailing services - </li>
</ul>

<h3 id="email">Email</h3>

<p>Emails can be used to reference various notifications.</p>

<ul>	
	<li>Main email</li>
	<li>Store Administration Notifications</li>
	<li>Customer Notifications</li>
</ul>

<h3 id="customers">Customers</h3>

<ul>	
	<li>Require customers to enter phone number on registration - mark the checkbox to activate.</li>
</ul>

<h3 id="payment_methods">Payment Methods</h3>
<p>Here you can manage payment methods which are used to accept payments from customers.</p>

<ul>	
	<li>Enable credit card payments - </li>
	<li>Only authorize payments without capturing them automatically - </li>
	<li>Credit card handler - </li>
</ul>


<h4 id="paypal">Setting up Paypal Direct</h3>

<ul>	
	<li>Username - </li>
	<li>Password - </li>
	<li>Signature - </li>
	<li>Enabled credit card types - </li>
</ul>


<h3 id="enabled_countries">Enabled Countries</h3>

<p>Enabled countries are available for setting delivery zones, etc <strong>(?)</strong>. Mark checkboxes next to the countries you want to enable.</p>

<h3 id="backend">Backend Administration</h3>

<ul>	
	<li>Generate SKU automatically - forces SKU to be created automatically.</li>
	<li>Default product type - click a drop-down list to select a default product type.</li>
	<li>Allow to edit store backend language files - mark the checkbox to allow editing backend language files.</li>
	<li>Allow to edit store backend template files - mark the checkbox to allow editing backend template files.</li>
</ul>



{helpSeeAlso}
	{see settings.delivery}
	{see settings.taxes}
	{see settings.currencies}
	{see settings.languages}
{/helpSeeAlso}