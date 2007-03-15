<p>Filters is a tool designed to make product search easy. Instead of browsing product catalog by categories you can create filters that will define 
specific search criteria and allow users to find products in no time. For instance, a <i>Bluetooth</i> filter in a cell phones catalog
can view all the phones that have a bluetooth feature no matter their place or any other parameters.</p>

<p>It's a common-sence solution that doesn’t force your customer to select the product in pre-set order, instead – the 
customer may filter the products by any property he likes at any point of the process (by adding or removing filters).</p>

<p>(Think of as many filters as possible to create an efficient browsing system as users may be interested in many kinds of attributes your products
have to offer (some may search for products by particular technical details and some may be interested in shape or color)</p>

<p class="note">(Because attributes are assigned to particular categories, the same way filters define search range only within specific categories)</p>

<p>Let's take a look at a few <strong>examples</strong> to get a better idea how filters work.</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create a filter</a></li>
	<li><a href="{self}#sort">Arrange Filter order</a></li>
	<li><a href="{self}#edit">Edit and Delete Filters</a></li>

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
	<img src="image/doc/categories/filters/main.bmp">
	<li>In the <strong>Main</strong> section you have to associate filter with an attribute first. Click <strong>Associate attribute</strong> and select carrier attribute 
	from the list.</li>
	<li>The <strong>name</strong> of the filter appears automatically so you can leave it as it is.</li>
	<li>Also you can enter filter's name in <strong>other languages</strong> supported by your system. Click on the language to supply Filter's name.</li>
	<img src="image/doc/categories/filters/filters_name_international.bmp">
	<li>Click <strong>Save</strong></li>
	<p class="note"><strong>Note</strong>: Because carrier attribute has already pre-set values, filtering 
	rules are generated automatically.</p>
	</ul>
	<ins>Preview filter function would be a great help</ins>
	
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

<h4 id="capacity">Creating <i>Battery Capacity</i> filter</h4>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>

<p>Complete the following steps to create the battery capacity filter:</p>

<ol>
	<li>Go to Filters section and open New Filter form <a href="{self}#carrier"><small>(remind me how)</small></a></li>
	<li>Fill out the Main section as follows:</li>
	<img src="image/doc/categories/filters/main.bmp">
	<li>Name - </li>
	<li>Associated attribute - click on the attribute list and select Battery Capacity.</li>
	<li>Other Languages - to enter filter's names in other languages click on the language to view additional fields.</li>
	<br \>
	<li>As "Battery Capacity" attribute doesn't have any values set initially, you have to define ranges that will specify filtering criteria.
	Let's say that battery capacity might range from 200 to 5000 mAh, therefore we create the following rules:</li>
	<img src="image/doc/categories/filters/filter_criteria.bmp">
	<ul>
		<li>Name - the name of the criteria represents criteria's details thus we choose "200-500"</li>
		<li>{glossary}Handle{/glossary}</li>
		<li>Range - range defines filtering scope of attribute's values. Thus enter "200" and "500" accordingly.</li>
	</ul>
	<p>When you start creating the first rule, an empty rule field opens below.</p>	
	<li>Continue in a similar manner to create a satisfactory list of intervals.</li>
	<img src="image/doc/categories/filters/criteria_list.bmp">
	<li>To <strong>edit</strong> any of the criteria's parameters make changes to necessary fields.</li>
	<li><strong>Arrangement</strong> of filter's criteria can be important. To change criteria's order click on criteria's empty space and drag
	it up or down:</li>
	<img src="image/doc/categories/filters/criteria_sort.bmp">
	<li>To <strong>delete</strong> a criteria, click "delete" icon:</li>
	<img src="image/doc/categories/filters/criteria_delete.bmp">
	<li>Make sure to click <strong>Save</strong> changes at the end.</li>
	<img src="image/doc/categories/filters/filters_save.bmp">
</ol>

<h4 id="date">Creating <i>Date</i> filter</h4>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>
<p>To create a date filter you have to have an attributes wich has a Date type. To create a date filter: </p>

<ul>
	<li>Open add new filter form.<a href="{self}#carrier"><small>(remind me how)</small></a></li>
	<img src="image/doc/categories/filters/date_attribute.bmp">
	<li>Associate date attribute from the  attribute's list.</li>
	<li>Name - enter Filter's name or leave the current name / automatic value.</li>
	<li>Other languages - if you have more than one language in your system, click on a language to enter 
	"name" in other language.</li>
	<br \>
	<li>To set filtering rules:</li>
	<img src="image/doc/categories/filters/date_form.bmp">
	<ul>
		<li>Name - enter the name of the period you want to define</li>
		<li>{glossary}Handle{/glossary}</li>
		<li>Value / Range - "from" and "to" fields are set automatically, to set / change date click on "calendar" icon next to the appropriate
		field: </li>
		<img src="image/doc/categories/filters/date_form_hand.bmp">
		<p>Date can be changed by choosing alternate date from the calendar:</p>
		<img src="image/doc/categories/filters/calendar.bmp">

	</ul>
	<li>Follow the steps as above to create more rules.</li>
	<li>You can <strong>sort</strong> values to set criterias' arrangement <a href="{self}#carrier"><small>(remind me how)</small></a></li>
	<li>Click <strong>Save</strong> button to return to filters page.</li>
	<img src="image/doc/categories/filters/filters_save.bmp">
</ul>

<h3 id="sort">Changing Filter Order</h3>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>
<p>When you have more than a few filters the order of your filters can be very important. The way you arrange your filters defines how they will
be displayed in your e-store.</p>

<ul>
	<li>To change the filter order go to the filters section. <a href="{self}#carrier"><small>(Remind me how).</small></a></li>
	<li>Move mouse cursor over the filter, click and hold button when "move" icon appears. You can drag and drop filter to set the appropriate order.</li>
	<img src="image/doc/categories/filters/rearrange.bmp">
	<li><strong>Save</strong> your changes afterwards.</li>
	<img src="image/doc/categories/filters/filters_save.bmp">
</ul>

<h3 id="edit">Edit and Delete Filters</h3>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>
<ul>
	<li>Go to the Filters section <a href="{self}#carrier"><small>(Remind me how).</small></a></li>
	<li>In order to edit a filter, hover mouse pointer over the existing filters and click "Edit" icon next to the filter (name) you want to manage.</li>
	<img src="image/doc/categories/filters/filters_edit.bmp">
	<p>Filter's form opens.</p>
	<li>You can edit any of the filter's property as in the add new filter section. 
	<a href="{self}#carrier"><small>(Remind me how).</small></a> <strong>Save</strong> the changes you have made. (You can edit existing data
	or add new values to the filter)</li>
	<br \>
	<li>If you want to Delete a filter click "Delete" next to the "edit" icon.</li>
	<img src="image/doc/categories/filters/filters_delete.bmp">
</ul>

{helpSeeAlso}
	{see cat.details}
	{see cat.attr}
	{see cat.images}
{/helpSeeAlso}