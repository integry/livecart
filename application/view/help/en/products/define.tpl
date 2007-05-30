<p>	
	Product details are devided into several tabs that provide information on particular product's details. To open a particular section, click on a necessary tab.
</p>
<img src="image/doc/products/edit/tabs.bmp">

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#main">Edit Main details</a></li>
	<li><a href="{self}#specification">Specification</a></li>
	<li><a href="{self}#stock_pricing">Stock and Pricing</a></li>
	<li><a href="{self}#images">Edit Images</a></li>
	<li><a href="{self}#related">Related Products</a></li>
	<li><a href="{self}#files">Edit Files</a></li>
	<li><ins><a href="{self}#options">Edit Products Operations</a></ins></li>
	<li><ins><a href="{self}#info">Edit Products Info</a></ins></li>
</ul>
</fieldset>
</div>

<h3 id="main">Main details</h3>

<p></p>
<img src="image/doc/products/edit/main_details.bmp">
<ul>
	<li>Is enabled - when the checkbox is marked, the product is visible in your e-store. It is not displayed 
	otherwise.</li>
	<li>Product name - the name of the product.</li>
	<li>Handle - Handle is used to represent object's {glossary}URL{/glossary}s, so you can change it for search engine optimization purposes, if needed.</li>
	<li>SKU - a stock keeping unit which is a specific number designating one specific product</li>
	<li>Short description - a brief description about the product. Short descirption is displayed in the 
	{glossary}product list{/glossary} page.</li>
	<li>Long description - a detailed description which is displayed in the {glossary}product details{/glossary} 
	page.</li>
	<li>Product type - choose tangible for a physical merchandise and digital otherwise.</li>
	<li>Website address - optional website address (for more details).</li>
	<li>Manufacturer - producer of the product.</li>
	<li>Keywords - words or phrases that will be used to help users to find products.</li>
	<li>Mark as featured product - mark the checkbox to set the product's status to {glossary}featured{/glossary}.</li>
</ul>

If you have more than one language in your system, you can translate the following details:
<img src="image/doc/products/edit/translate1.bmp">
<img src="image/doc/products/edit/translate2.bmp">

<ul>
	<li>Name</li>
	<li>Short description</li>
	<li>Long description</li>
</ul>

<h3 id="specification">Product specification</h3>
<p>Product Specification section includes user defined attributes. You can create attributes in the category's <a href="{help /cat.attr}">attributes section</a>.</p>
<img src="image/doc/products/edit/specifications1.bmp">


<h3 id="stock_pricing">Stock and Pricing</h3>

<img src="image/doc/products/edit/inventory.bmp">
<ul>
	<li>Items in stock - ther number of product items in your warehouse.</li>
</ul>

<img src="image/doc/products/edit/pricing.bmp">
<ul>
	<li>Price or prices of the product according to <a href="{help /currency}">currencies configuration</a> of your system.</li>
</ul>

<img src="image/doc/products/edit/shipping.bmp">
<ul>
	<li>Shipping weight - weight of the product in Metric or English units.</li>
	<li>Minimum order quantity - a minimum number of product items allowed to order at a time.</li>
	<li>Shipping surcharge - an additional shipping charge (usually for oversized items).</li>
	<li>Requires separate shipment - mark the checkbox to require separate shipment for the product.</li>
	<li>Qualifies for free shipping - mark the checkbox to allow free shipping.</li>
	<li>Allow back-ordering - mark the checkbox to allow users to make {glossary}back-order{/glossary}s.</li>
</ul>

<h3 id="images">Images</h3>

<p>Products can have multiple images to represent them better. The first uploaded picture is the 
<strong>Main</strong> image that is displayed in the {glossary}product list{/glossary} page. It is also the first image that is 
displayed in the <a href="{help /products.store}#product_details">product details</a> page. All the other pictures will be displayed as enlargeable thumbnails in 
{glossary}product details{/glossary} page.</p>

<p>To add an image:</p>

<ol>
	<li>Click <strong>Add new image</strong>.</li>
	<li>Click <strong>Browse</strong> to locate imgage on your hard disk drive.</li>
	<li>Click <strong>Upload</strong> to set an image.</li>
</ol>

<img src="image/doc/products/edit/images.bmp">

<p class="note"><strong>Note:</strong> Upon upload, the image will automatically be resized to predefined sizes.
You can set image dimensions (<ins>and other details?</ins>) in the <strong>Settings</strong> section.</p>

<h3 id="related">Related products</h3>

<p>For upselling purposes you can easily add related products that are displayed to customers as additional
products that are some way related. To add related products for a particular product:</p>

<ol>
	<li>Click <strong>Select Products</strong>:</li>
	<p>A new window pop's up.</p>
	<li>From the window select products by clicking on the products name:</li>
	<img src="image/doc/products/edit/products.bmp">
	Close the window when you are done. All the selected products will be displayed in your store's  products detailed page 
	as related items. For example:
	<img src="image/doc/products/edit/recommended.bmp">
</ol>

<p>If you have many related products it might be useful to group them together. To group existing related products:</p>

<ul>
	<li>Click <strong>Add Group</strong>.</li>
	<li>Enter group name and click <strong>Save</strong>.</li>
	<li>To place products into groups, simply drag and drop products into appropriate groups.</li>
<!--
	<img src="image/doc/products/edit/group1.bmp">
-->
</ul>
<p><ins>Note: the name of the related products' group is not displayed anywhere in e-store.</ins></p>

<h3 id="options">Files</h3>
<p>Files are most likely used for digital products such as software, music or any other. To upload files:</p>
<ul>
	<li>Click <strong>Add new file</strong>.</li>
	<img src="image/doc/products/edit/add_file.bmp">
	<li>Click <strong>Browse</strong> to locate your file and click <strong>Upload</strong>.</li>
</ul>

<p>If you have many files you can group them into separate groups. To group files:</p>
<ul>
	<li>Click <strong>Add new group</strong>.</li>
	<li>Then enter goup's name and click <strong>Add</strong>.</li>
	<li>To put products into groups, simply drag and drop files into appropriate groups.</li>
</ul>

<h3 id="operations">Operations</h3>
<p><ins>Not completed interface</ins></p>

<h3 id="info">Info</h3>
<p><ins>Not completed interface</ins></p>


{helpSeeAlso}
	{see products.add}
{/helpSeeAlso}