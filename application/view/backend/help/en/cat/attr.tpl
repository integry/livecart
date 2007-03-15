<p>
	Attributes allow you to describe product information more precisely as instead of providing all product specifications as free-form text, you can
	define a fixed set of attributes for each product category and have designated fields for entering each parameter of a product. It makes it 
	easier for customers to compare common products and by using attributes you can create product filters, which make it easy for a customer to 
	find the products that match his/her requirements quickly.
</p>
<p>
	Attributes allow grouping (filtering) products by their specific properties.
</p>

<p>
	For instance, if a customer is shopping for a new laptop and is particularly looking for an AMD processor and you have created a "Processor Type"
	attribute, the customer will be able to sort the whole list of laptops to display only AMD processor powered laptops with a single click. This 
	way a customer will have an ability to select certain kind of processor without a need to browse through all the available types 
	(thus restricting his/her search to specific needs). Of course you can (and you probably should) create as many attributes as possible 
	(for instance, for size, capacity, price and so on) which will allow users to customize their search in the most effective way. Note that actual
	filtering options are set in the <a href="{help /cat.filters}">Filters</a> section where you use category's attributes to create filters.</p>
<p class="note">
	<strong>Note:</strong> Attributes have to be set up for each category individually. You can however set higher level (global) attributes for
	higher level categories when necessary. For example, if the Computers category has two subcategories for Laptops and Desktops, you can define
	the common attributes like processor type, speed, memory, etc. for the Computers category and Laptop/Desktop specific attributes (for example,
	battery life) to the respective categories. You can also define truly global attributes, which will be available for all categories by defining
	them for the root category.
</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
	<li><a href="{self}#create">Create a new attribute</a></li>
	<li><a href="{self}#group">Group attributes</a></li>
	<li><a href="{self}#edit">Edit attribute</a></li>
	<li><a href="{self}#delete">Delete attribute</a></li>
	<li><a href="{self}#sort">Change attribute order</a></li>
</ul>
</fieldset>
</div>

<h3>Attribute Types</h3>

<p>LC provides a custom based attribute management system which allows you to create and manage attributes in relatively simple manner.
LiveCart supports three base product attribute types which can be <strong>text, number</strong> or <strong>date</strong>. Using these base types you 
can create variuos types of attributes. Below you'll find a couple of practical examples on how to choose which attribute type would suit best for describing a particular product property.
</p>

<h3 id="create">Create Attribute</h3>
<p>
	(To create an attribute you have to define attributes type, name and its values if necessary. 
	Because attributes are automatically included in  the <i>Add New Product</i> form, we will demonstrate in our examples how attributes are generated and placed in the product form.)
	Suppose you have a category stocked with cell phones and you want to create custom fields for specifying the following properties:
	<a href="{self}#carrier">Carrier ( select option )</a>, <a href="{self}#features">Phone Features ( select checkboxes)</a>
	and <a href="{self}#capacity">Batery Capacity ( field option )</a>.

<h4 id="carrier">Creating <i>Carrier</i> attribute</h4>
<p>To create an attribute follow these steps:</p>
	<ul>	
		<li>Select a category in the category tree and click <strong>Attributes</strong> tab.</li>
		<img src="image/doc/categories/attributes/attributes_tab.bmp">
		<li>In the attributes section click <strong>Add new attributes</strong>:</li>
		<img src="image/doc/categories/attributes/add_new_attribute_reference.bmp">
		<p><i>Add new attribute</i> form appears. Fill out the following fields as described below:</p>
		<img src="image/doc/categories/attributes/add_attribute_form2.bmp">
		<li><strong>Type</strong> - because mobile carrier is a simple text and there is (probably) a finite number of carriers click on the type drop-down menu and 
		select <i>Text selector</i> type from the list. Your chosen type is suitable for creating a predefined list of values which will be 
		displayed as a selection option.</li>
		<li>We check <strong>Required</strong> to make the field mandatory since it is an important feature. </li>
		<li><strong>Can select multiple values</strong> - we leave this checkbox clear to allow assigning only one carrier to each phone</li> 
		<li><ins>Field displayed in the product's preview info</ins></li>
		<li><ins>Field displayed in the product's detailed page</ins></li>
		<li>Attribute's <strong>title</strong> represent its function thus we enter a meaningful name - <strong>Carrier</strong>. </li>
		<li><strong>Handle</strong> - handle is generated automatically. Handle is used to represent the attribute in {glossary}URL{/glossary}'s, so you can change it for 
		{glossary}SEO{/glossary} purposes, if needed.</li>
		<li>Prefix and Suffix - we leave these fields empty because carriers don't have any of them.</li> 
		<li>In the description field we enter a brief description to describe the term "Carrier".</li> 
		<hr \><br \>
		<h2>Temp</h2>
		<li>Select a category in the category tree and click <strong>Attributes</strong> tab.</li>
		<img src="image/doc/categories/attributes/attributes_tab.bmp">
		<li>In the attributes section click <strong>Add new attributes</strong>:</li>
		<img src="image/doc/categories/attributes/add_new_attribute_reference.bmp">
		<p><i>Add new attribute</i> form appears. Fill out the following fields as described below:</p>
		<img src="image/doc/categories/attributes/type.bmp">
		<li><strong>Type</strong> - because mobile carrier is a simple text and there is (probably) a finite number of carriers click on the type drop-down menu and 
		select <i>Text selector</i> type from the list. Your chosen type is suitable for creating a predefined list of values which will be 
		displayed as a selection option.</li>
		<br \>
		<img src="image/doc/categories/attributes/checkboxes.bmp">
		<li>We check <strong>Required</strong> to make the field mandatory since it is an important feature. </li>
		<li><strong>Can select multiple values</strong> - we leave this checkbox clear to allow assigning only one carrier to each phone</li> 
		<li>Field displayed in the product's preview info</li>
		<li>Field displayed in the product's detailed page</li>
		<br \>
		<img src="image/doc/categories/attributes/textfields.bmp">
		<li>Attribute's <strong>title</strong> represent its function thus we enter a meaningful name - <strong>Carrier</strong>. </li>
		<li><strong>Handle</strong> - handle is generated automatically. Handle is used to represent the attribute in {glossary}URL{/glossary}'s, so you can change it for 
		{glossary}SEO{/glossary} purposes, if needed.</li>
		<li>Prefix and Suffix - we leave these fields empty as none of them is applicable.</li> 
		<hr \>
		<li>International details - if you need to translate attributes in other languages installed in your system, click on the Language to 
		expand additional fields (which include Title and Details).</li>


	<img src="image/doc/categories/attributes/attributes_international_details.bmp">

The last thing to do is to create Values which will be used as a selection option in the "Add new Product" form. To add values: 
	<ul>
		<li>Click Values tab in the main window -> </li>
		<img src="image/doc/categories/attributes/values_tab.bmp">

		<li>When switched to the Value window enter the first Mobile Carrier in the text field provided.</li>
		<img src="image/doc/categories/attributes/empty_field.bmp">

		<li>As soon as you start entering the value, additional empty field appears below.</li>
		<img src="image/doc/categories/attributes/enter_values.bmp">
	
		<li>Keep in this manner until we have a full list of carriers.</li>
		<img src="image/doc/categories/attributes/values.bmp">

		<li>Click the <strong>Save</strong> button to return to the Attributes page. </li>
		<img src="image/doc/categories/attributes/save.bmp">
	</ul>

	Your new attribute is automatically generated in the "Add new Product" form and <ins>will</ins> look similar to this one:
	<img src="image/doc/categories/attributes/carrier.bmp">
	</ul>

<p class="note"><strong>Note</strong>: In the list there is also <strong>other</strong> value which is designed to supplement values list
in case a new value is introduced.</p>

<h4 id="features">Creating <i>Phone Features</i> attribute</h4>
<p>
	Cell phones usually have a great variety of features therefore it would more convenient to create a list of features than re-enter them
	every time. To create an attribute of features, open <strong>Add new attribute form</strong> <a href="{self}#carrier"><small>(remind me how)
	</small></a> 
</p>
	<p><img src="image/doc/categories/attributes/multi.bmp"></p>
	<p>The following parameters have to be set in the form:</p>

	<ul>
		<li>Type - expand type list and choose Text -> Options as your type </li>
		<li>Required - leave the checkbox empty as some phones may don't have additional features (optional field/selection) </li>
		<li>Can select multiple values - mark the checkbox to allow multiple features assigned to a cell phone</li>
		<li>Field is displayed in the product overview page - </li>
		<li>Field is displayed in the product's detail page - </li>
		<li>Title - enter here <strong>Features</strong> to represent attribute's purpose/function.</li>
		<li>Handle is generated automatically therefore you may leave the field as it is</li>
		<li>Description - enter a few sentences about features attribute to set its description.</li>

	</ul>

<p>The next thing to do is to create a value list of mobile's features. 

	<ul>
		<li>Click <i>Values</i> tab which appears on the right of the <i>Main</i> tab. </li>
		<img src="image/doc/categories/attributes/values_tab.bmp">
		<li>In the Values section enter all the necessary features one by one.</li>
		<img src="image/doc/categories/attributes/enter_values.bmp">
		<li><strong>Save</strong> the attribute when done.</li>
	</ul>

</p>

	The representation of your generated field could be similar to this:

	<img src="image/doc/categories/attributes/features.bmp">

<p class="note"><strong>Note</strong>: to create a corresponding attribute for a numeric selection choose <strong>Number Selector</strong> type followed
by necessary changes. A numeric attribute looks similar to this one:
<img src="image/doc/categories/attributes/bandwidth_generated.bmp">
<strong>Other</strong> value can be used to add more values. Click <strong>other</strong> to add a new value.
</p>

<h4 id="capacity">Creating <i>Batery Capacity</i> attribute</h4>

<p>
	Battery capacity is expressed in numeric(al) format of mAh, therefore you should consider choosing a <strong>Number</strong> type. When it comes
	to field or selector it is up to you to decide whether you want to create a single field for entering a value or to create a pre-defined list of possible capacity values. 
	We choose a field type in the following example.
</p>

<ul>
	<li>Open <strong>Add new attribute form</strong> <a href="{self}#carrier"><small>(remind me how)</small></a></li>
	<li>Type - select Nember Field.</li>
	<li>Required - click the checkbox to make the field required attribute</li>
	<img src="image/doc/categories/attributes/required.bmp">
	<li>Field is displayed in the product overview page - </li>
	<li>Field is displayed in the product's detail page - </li>
	<li>Title - enter <strong>Battery Capacity</strong> to represent attribute's purpose/function.</li>
	<img src="image/doc/categories/attributes/capacity_title.bmp">
	<li>Handle is generated automatically so we leave the field unaltered</li>
	<img src="image/doc/categories/attributes/capacity_handle.bmp">
	<li>Prefix - leave the field empty.</li>			
	<li>Suffix - enter "mAh" as battery capacity is defined by this symbol.</li>
	<li>In the description field provide brief information about the field similar to this</li>
	<img src="image/doc/categories/attributes/capacity_description.bmp">
</ul>	

<p>Don't forget to click <strong>Save</strong> at the end.</p>
<br \>
<p>
	Generated attribute will provide a field for entering battery capacity:

	<img src="image/doc/categories/attributes/capacity_generated_mah.bmp">
</p>
	
<p class="note"><strong>Note</strong>: to create analogous attribute for Text Field simply change type to <strong>Text Field</strong>.</p>


<h3 id="group">Group attributes</h3>

LC allows you to group your attributes into logical chunks called "groups". To introduce a new group:
	<ul>
		<li>Click <strong>Add new group</strong></li>
		<img src="image/doc/categories/attributes/add_new_group.bmp">
		<li>Enter group title (to identify group)</li>
		<img src="image/doc/categories/attributes/group_title.bmp">
		<li>Supply international translation if necessary (applicable)</li>
	</ul>
Attribute groups appear as a rectangle which can contain as many attributes as neccesary. To place attributes into groups, simply drag attributes
into appropriate groups.

<img src="image/doc/categories/attributes/groups_main.bmp">

	In the image above you see two groups named "Cell Phones" and "Dimensions" and one attribute "Color" what doesn't belong to any group. 
	To change group's name or delete a group click on one of the icons on the left:

<img src="image/doc/categories/attributes/group_edit.bmp">

<p class="note"><strong>Notice</strong>: deleting a group will cause all of its attributes to be deleted as well. Thus you have to <strong>ungroup
</strong> attributes that you want to keep.</p>


<h3 id="edit">Edit attribute</h3>
<p align="right"><a href="{self}#top"><small>Top</small></a></p>

To edit attribute: select an attribute from the list and click on its "pen" icon on the left:

<img src="image/doc/categories/attributes/edit_attribute.bmp">

Attribute's management form appears with its specification details. To edit any of attribute's fields or values, simply alter existing parameters/values
and save changes afterwards. For detailed description of fields, refer to <a href="{self}#create">Create new attribute</a> section.

<h3 id="delete">Delete attribute</h3>

To delete attribute: select an attribute from the list and click on its "delete" icon on the left:

<img src="image/doc/categories/attributes/delete_attribute.bmp">

<h3 id="sort">Change attribute order</h3>

(In LC sorting of attributes is implemented via drag&drop feature). To change the order of attributes (or attribute groups), click on attributes 
empty space and then drag it up or down.
<p class="note"> <strong>Notice</strong> that the order of attributes in the <i>Add New Product</i> form is determined by the arrangement
of attribute groups as well as separate attributes.
</p>
{helpSeeAlso}
	{see cat.details}
	{see cat.filters}
	{see cat.images}
{/helpSeeAlso}