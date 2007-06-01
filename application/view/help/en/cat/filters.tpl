<p>Filters is a tool designed to make product search easy. Instead of forcing your customers to browse the product
 catalog by categories you can create filters that will define specific search criteria and allow users to find 
 products faster. For instance, applying a "Bluetooth" filter in a cell phones catalog
can view all the phones that have a bluetooth feature no matter their place or any other parameters.</p>

<p>It's a common-sence solution that doesn’t force your customer to select the product in pre-set order, instead – the 
customer may filter the products by any property at any point of the process (by adding or removing filters).</p>

<p class="note"><strong>Note</strong>: Filters define search range only within specific categories because 
attributes are assigned to particular categories and filters are directly mapped to attributes. If you are not familiar with term "attribute" plase refer to  <a href="{help /cat.attr}">Attributes</a> section.</p>

<p class="note"><strong>Tip</strong>:(When creating filters think of as many filters as possible to create an efficient browsing system because users may be interested in many kinds of attributes your products
have to offer (some may search for products by particular technical details and some may be interested in shape or color)).</p>

<!--
<p>Let's take a look at a few <strong>examples</strong> to get a better idea how filters work.</p>
-->

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create a filter</a></li>
	<li><a href="{self}#sort">Reorder filters</a></li>
	<li><a href="{self}#edit">Edit and Delete Filters</a></li>

</ul>
</fieldset>
</div>

<h3 id="create">Create Filter</h3>

<p>
You can create four types of filters:</p>
<ul>
	<li>Number type which can be either a <i>field</i> or a <i>selector</i></li>
	<li>Text type can be only <i>selector</i></li>
	<li>and Date type</li>
</ul>
<p class="note"><strong>Note</strong>: This means that you can't create a filter for <strong>Text field</strong> attribute because you won't be able 
to define a range for filter's values.</p>
</p>

<p>We will guide you through several tutorials and show how to generate filters for your existing attributes. As in examples with 
<a href="{help /cat.attr}">attributes</a> in the previuos section we will use cell phones for illustrating <a href="{self}#carrier">Carrier</a>, 
<a href="{self}#capacity">Battery Capacity</a> and additional <a href="{self}#date">Date</a> filter.</p>

<h4 id="carrier">Creating Carrier filter</h4>
<ul>
	<li>To create filters, select a category from the category tree and click on the "Filters" tab.</li>
	<img src="image/doc/categories/filters/filters_tab.bmp">
	<p class="note">If you see a message "No filterable attributes have yet been created for this category", go to the <a href="{help /cat.attr}">Attributes </a> section to create 
	attributes first.</p>
	<li id="form">On the Filters page click "Add new filter".</li>
	<img src="image/doc/categories/filters/add_new_filter.bmp">
	<p>Add new filter form appears. </p>
	<img src="image/doc/categories/filters/carrier_filter.bmp">
	<li>Associated attribute - click the dropown menu and select carrier attribute</li>
	<li>The name of the filter appears automatically so you can leave it as it is.</li>
	<li>Also you can enter filter's name in other languages supported by your system. Click the language to supply Filter's name.</li>
	<li>Click "Save"</li>
	</ul>
	<p>Because carrier attribute has already pre-set values, filtering rules are generated automatically. Filter appears below with a number indicating total filtering rules.</p>
	<img src="image/doc/categories/filters/carrier_filter_4.bmp">
	<p>Created filters instantly appear in the storefront and are available for users to browse your products.</p>
	
<!--<li>Generated rules appear below representing all associated attribute's values</li>
	<img src="image/doc/categories/filters/filters_generated.bmp">
	<li>If any of the generated rules doesn't seem to be correct you can <strong>edit</strong> them individually. There are three rule's parameters: </li>
	<ul>
		<li>Name - the name of the filter (product attribute) which will be seen in the frontend</li>
		<li>Handle - handle is used to for rule's  <a href="">URL</a></li>
		<li>Value - the value of the attribute which is used as a filtering criterion.</li>
	</ul>
	<li>In addition, you can <strong>rearrange</strong> rules to set the order in which they should be displayed. To do that simply move
	mouse cursor over an existing rule, click and hold your mouse button when "move" icon appears, now you can move your rule up and down to 
	set the appropriate arrangement.</li>
	<img src="image/doc/categories/filters/rearrange.bmp">
	<li>When you are done, click <strong>Save</strong>.</li>
	<img src="image/doc/categories/filters/filters_save.bmp">
-->

<h4 id="capacity">Creating Battery Capacity filter</h4>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>

<p>Complete the following steps to create the battery capacity filter:</p>

<ol>
	<li>Go to Filters section and open "Add new Filter" form <a href="{self}#form"><small>(remind me how)</small></a></li>
	<li>Choose "Battery Capacity" from the dropdown menu to accociate an attribute.</li>
	<img src="image/doc/categories/filters/filter_battery_capacity.bmp">
	<li>Because "Battery Capacity" attribute doesn't have any values set initially, you have to define ranges that will specify filtering criteria.
	Let's say that battery capacity might range from 200 to 2000 mAh, therefore we create rules by providing the following parameters:</li>
	<img src="image/doc/categories/filters/filter_200_500.bmp">
	<ul>
		<li>Name - the name of the criteria represents criteria's details thus we choose "200 - 500"</li>
		<li>Range - range defines the filtering scope of the attribute's values. Thus enter "200" and "499" accordingly.</li>
	</ul>
	<li>Continue in a similar manner to create a satisfactory list of intervals.</li>
	<img src="image/doc/categories/filters/filters.bmp">
	<li id="sort">Arrangement of filter's criteria can be important because it defines how filtering options are displayed in storefront. To change criteria's order click on criteria's empty space and drag
	it up or down:</li>
	<img src="image/doc/categories/filters/sort.bmp">
	<li>Make sure to click the "Save" button.</li>
</ol>

<!--
<ul>
	<li>To <strong>delete</strong> a criteria, click the "Delete" icon:</li>
	<img src="image/doc/categories/filters/delete.bmp">
</ul>
-->

<h4 id="date">Creating Date filter</h4>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>
<p>To create a date filter you have to have an attributes wich has a "Date" type. To create a date filter: </p>

<ul>
	<li>Open add new filter form.<a href="{self}#form"><small>(remind me how)</small></a></li>
	<img src="image/doc/categories/filters/date_attribute.bmp">
	<li>Associate date attribute from the attribute's list.</li>
	<li>Enter filter's name or leave the current name.</li>
	<li>To set filtering rules:</li>
	<ul>
		<li>Enter the name of the period you want to define.</li>
		<li>Set "Date between" by specifying fields "from" and "to". To set date click the "calendar" icon next to the appropriate field: </li>
		<img src="image/doc/categories/filters/date_form_hand.bmp">
		<p>Date can be changed by choosing alternate date from the calendar:</p>
		<img src="image/doc/categories/filters/calendar.bmp">

	</ul>
	<li>You can sort values to set criterias' arrangement <a href="{self}#sort"><small>
	(remind me how)</small></a></li>
	<li>Click the "Save" button to return to filters page.</li>
</ul>

<h3 id="sort">Changing Filter Order</h3>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>

<p>The way filters are displayed in your frontend is determined by their arrangement.</p>

<ul>
	<li>Move mouse cursor over the filter. Drag and drop the filter to set the appropriate order.</li>
	<img src="image/doc/categories/filters/rearrange.bmp">
</ul>

<h3 id="edit">Edit and Delete Filters</h3>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>

<ul>
	<li>In order to <strong>edit</strong> a filter, hover mouse pointer over the existing filters and click the "Edit" icon.</li>
	<img src="image/doc/categories/filters/filter_edit.bmp">
	<p>Filter's form opens. You can edit any of the filter's property as in the add new filter section. <a href="{self}#carrier"><small>(Remind me how).</small></a></p>
	<li>If you want to <strong>Delete</strong> a filter click the "Delete" icon next to the "Edit".</li>
	<img src="image/doc/categories/filters/filter_delete.bmp">
</ul>

{helpSeeAlso}
	{see cat.details}
	{see cat.attr}
	{see cat.images}
{/helpSeeAlso}