
<p>Filters is a tool designed to make product search easy. Instead of browsing product catalog by categories you can create filters that will define 
specific search criteria and allow users to find products in no time. For instance, a filter <i>Bluetooth</i> in a cell phones catalog
will display all the phones that have a  bluetooth feature no matter their place or any other parameters.</p>

<p>It's a common-sence solution that doesn’t force your customer to select the product in pre-set order, instead – the 
customer may filter the products by any property he likes at any point of the process (by adding or removing filters).</p>

<p>(Think of as many filters as possible to create an efficient browsing system as users may be interested in many kinds of attributes your products
have to offer (some may search for products by particular technical details and some may be interested in shape or color)</p>

<p>Let's take a look at a few <strong>examples</strong> to get a better idea how filters work.</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create a filter</a></li>
	<li><a href="{self}#delete">Delete</a></li>
	<li><a href="{self}#sort">Sort</a></li>
</ul>
</fieldset>
</div>

<h3 id="create">Create Filters</h3>
<p>To create filters you have to define attributes first. Go to the <a href="{help /cat.attr}">Attributes</a> section to create attributes. 
You can create four types of filters (which correspond to attributes types): 

<ul>
	<li>Number type which can be either a <i>field</i> or a <i>selector</i></li>
	<li>Text type can be only <i>selector</i></li>
	<li>and Date type</li>
</ul>
<p class="note">This means that <strong>Text field</strong> attribute cannot have a filter as you won't be able to define 
a range of values for it.</p>
</p>

<h4>About filters</h4>

<ul>
	<li>Each filter has two main parameters: </li>
	<ul>
		<li>Name - the name of the filter</li>
		<li>Associated attribute - attribute that will be used to create filter.</li>
	</ul>
	<li>In the <strong>Filters</strong> section you can generate filters automatically or set them manually.</li>
	<ul>
		<li>To generate filters based on the existing attribute's values, simply click <strong>Generate Filters</strong>. If your attribute
		has five values of let's say "colors", then after generating filters you will have five filters for each color.</li>
		<li>To set filters manually:</li>
		<ul>
			<li>Name - the actual name of the filter</li>
			<li>Handle - handle is used to represent filter's <a href="">URL</a></li>
			<li>Value - select or set value of teh filter. Values might be different depending on the attribute's type therefore
			we will discuss all of them later in examples provided.</li>
		</ul>
	</ul>
</ul>

<p>We will guide you through several tutorials and show how to generate filters for your existing attributes. As in examples with 
<a href="{help /cat.attr}">attributes</a> in the previuos section we will use cell phones for illustration.</p>

<h4 id="carrier">Creating <i>Carrier</i> filter</h4>

{helpSeeAlso}
	{see cat.details}
	{see cat.attr}
	{see cat.images}
{/helpSeeAlso}