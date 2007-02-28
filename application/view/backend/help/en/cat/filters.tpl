
<p>Filters is a tool designed to make product search easy. Instead of browsing product catalog by categories you can create filters that will define 
specific search criteria and allow users to find products in no time. For instance, a <i>Bluetooth</i> filter in a cell phones catalog
can view all the phones that have a bluetooth feature no matter their place or any other parameters.</p>

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

<h3 id="type">Filter Types</h3>
<p>
You can create four types of filters (which correspond to attributes types): </p>
<ul>
	<li>Number type which can be either a <i>field</i> or a <i>selector</i></li>
	<li>Text type can be only <i>selector</i></li>
	<li>and Date type</li>
</ul>
<p class="note"><strong>Note</strong>: This means that you can't create a filter for <strong>Text field</strong> attribute because you won't be able 
to define a range for filter's values.</p>
</p>

<h3 id="create">Create Filter</h3>
<p>We will guide you through several tutorials and show how to generate filters for your existing attributes. As in examples with 
<a href="{help /cat.attr}">attributes</a> in the previuos section we will use cell phones for illustrating <a href="{self}#carrier">Carrier</a>, 
<a href="{self}#capacity">Battery Capacity</a> and additional <a href="{self}#date">Date</a> filter.</p>

<h4 id="carrier">Creating <i>Carrier</i> filter</h4>
<ul>
	<li>To create filters, select a category from the category tree and click <strong>Filters</strong> tab. </li>
	<img src="image/doc/categories/filters/filters_tab.bmp">
	<p>If you see a message "This category has no non-text attributes", go to the <a href="{help /cat.attr}">Attributes </a> section to create 
	attributes first.</p>
	<li>On the Filters page click <strong>Add new filter</strong>.</li>
	<img src="image/doc/categories/filters/add_new_filter.bmp">
	<p>Add new filter form appears. </p>

	<li>In the <strong>Main</strong> section you have to associate filter with an attribute first. Click <strong>Associate attribute</strong> and select carrier attribute 
	from the list.</li>
	<img src="image/doc/categories/filters/main.bmp">
	<li>The <strong>name</strong> of the filter appears automatically so you can leave it as it is.</li>
	<li>Also you can enter filter's name in <strong>other languages</strong> supported by your system. Click on the language to supply Filter's name.</li>
	
	<li>To create carrier filters click <strong>Filters</strong> next to the Main tab. </li>
	<li>In the Filters section you can generate filters automatically or add them one by one. To generate filters click <strong>Generate filters</strong>.</li>
	<img src="image/doc/categories/filters/filters_.bmp">
	<li>Generated filters appear below representing each attribute's value</li>
	<img src="image/doc/categories/filters/filters_generated.bmp">
	<li>If any of the generated filters doesn't seem to be correct you can <strong>edit</strong> each of them individually. There are three filter's parameters: </li>
	<ul>
		<li>Name - the name of the filter which will be seen in the frontend</li>
		<li>Handle - handle is used to for filter's  <a href="">URL</a></li>
		<li>Value - the value of the attribute which is used as a filtering criterion.</li>
	</ul>
	<li>In addition, you can <strong>rearrange</strong> filters to set the order in which filters should be displayed. To do that simply move
	mouse cursos over existing filter, when "move" icon appears click and hold your mouse button, now you can move your filter up and down to 
	set the appropriate arrangement.</li>
	<img src="image/doc/categories/filters/rearrange.bmp">
	<li>When you are done, click <strong>Save</strong>.</li>
	<img src="image/doc/categories/filters/filters_save.bmp">
	<p>(<strong>Note:</strong> How to create filters <strong>manually</strong> will be discussed in the next example).</p>
</ul>

<h4 id="capacity">Creating <i>Battery Capacity</i> filter</h4>

{helpSeeAlso}
	{see cat.details}
	{see cat.attr}
	{see cat.images}
{/helpSeeAlso}