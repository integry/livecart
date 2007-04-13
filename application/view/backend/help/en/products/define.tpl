<p>	
	The product detail form is devided into eight sections. Field specification details are provided below.
	If you are adding a new product -> the form is divided into ...
	If you are edditing products -> ...
</p>

<!--
You can edit details by choosing appropriate tab indicating details group (main, pricing etc.)
<img src="image/doc/products/edit/tabs.bmp">
-->

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#main">Edit Main details</a></li>
	<li><a href="{self}#specification">Specification</a></li>
	<li><a href="{self}#pricing_shipping">Pricing and Shipping</a></li>
	<li><a href="{self}#inventory">Edit Inventory</a></li>
	<li><i><a href="{self}#images">Edit Images</a></i></li>
	<li><i><a href="{self}#related">Related Products</a></i></li>
	<li><i><a href="{self}#options">Edit Options</a></i></li>
	<li><i><a href="{self}#files">Edit Files</a></i></li>
	<li><a href="{self}#save">Save changes</a></li>
</ul>
</fieldset>
</div>

<h3 id="main">Main details</h3>

<p></p>
<img src="image/doc/products/edit/main_details.bmp">
<ul>
	<li>Is enabled - mark the checkbox to make the product visible in your e-store.</li>
	<li>Product name - the name of the product.</li>
	<li>{glossary}Handle{/glossary}</li>
	<li>{glossary}SKU{/glossary}</li>
	<li>Short description - a brief description about the product. Short descirption is displayed in the 
	{glossary}product list{/glossary} page.</li>
	<li>Long description - a detailed description which is displayed in the {glossary}product details{/glossary} 
	page.</li>
	<li>Product type - choose tangible for a physical merchandise and digital otherwise.</li>
	<li>Website address - optional website address for more details.</li>
	<li>Manufacturer - producer of the product.</li>
	<li>Keywords - words or phrases that will be used to help users to find products.</li>
	<li>Mark as bestseller - mark the checkbox to set the product's status to {glossary}bestseller{/glossary}.</li>
</ul>
If you have more than one language in your system, you can translate the following details:
<img src="image/doc/products/edit/translate.bmp">
<ul>
	<li>Name</li>
	<li>Short description</li>
	<li>Long description</li>
</ul>

<h3 id="specification">Product specification</h3>
<p></p>
<img src="image/doc/products/edit/specification.bmp">
<p>Product specification section includes user defined attributes. See <a href="{help /cat.attr}">attributes</a> 
for more information.</p>

<h3 id="pricing_shipping">Prices and Shipping</h3>
<p>This section is used to supply prices of your product.</p>
<img src="image/doc/products/edit/pricing.bmp">
Go to <a href="{help /currency}">currencies section</a> to configure currencies of your system.
<br \>
<br \>
<img src="image/doc/products/edit/shipping.bmp">
<ul>
	<li>Shipping weight - enter weight of the product.</li>
	<li>Minimum order quantity - a minimum number of product items allowed to order at a time.</li>
	<li>Minimum surcharge - an extra / additional charge.</li>
	<li>Requires separate shipment - mark the checkbox to require separate shipment for the product.</li>
	<li>Qualifies for free shipping - mark the checkbox to allow free shipping.</li>
	<li>Allow back-ordering - mark the checkbox to allow users to make {glossary}back-order{/glossary}s.</li>
</ul>



<h3 id="inventory">Inventory</h3>

<p></p>
<img src="image/doc/products/edit/inventory.bmp">
<ul>
	<li>Items in stock - ther number of product items in your warehouse, etc.</li>
</ul>


<h3 id="images">Images</h3>

<p></p>
<img src="image/doc/products/edit/images.bmp">
<ul>
	<li></li>
</ul>


<h3 id="related">Related products</h3>

<p></p>
<img src="image/doc/products/edit/related_products.bmp">
<ul>
	<li></li>
</ul>


<h3 id="options">Options</h3>

<p></p>
<img src="image/doc/products/edit/options.bmp">
<ul>
	<li></li>
</ul>


<h3 id="options">Files</h3>

<p></p>
<img src="image/doc/products/edit/files.bmp">
<ul>
	<li></li>
</ul>

<h3 id="save">Save</h3>

<p>If you are adding a new product you can choose to save only the main details and add another product immediately
 or choose to continue with more details:
 </p>
<img src="image/doc/products/edit/save.bmp">



{helpSeeAlso}
	{see products.add}
{/helpSeeAlso}