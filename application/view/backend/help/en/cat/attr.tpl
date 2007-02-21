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
	filtering options are set in the <a href="{help cat.filter}">Filters</a> section where you use category's attributes to create filters.</p>
<p class="note">
	<strong>Note:</strong> Attributes have to be set up for each category individually. You can however set higher level (global) attributes to 
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

<p>LiveCart supports various product attribute types which can be text, number or date. Together with a few default attributes LC provides a custom 
based attribute management system where you can define your own attributes and crate product's specifications.</p>
<p>Below you'll find a couple of practical examples on how to choose which attribute type would suit best for describing a particular product property.</p>

<h3 id="create">Create Attribute</h3>
<p>
	To create an attribute you have to fill out a short form. Later we will show how attributes are actually implemented in the <i>Filters</i> 
	section. What is more, attributes are automatically included in the <i>Add New Product</i> form. Therefore in these example we will demonstrate
	how attributes are generated and placed in the product form.<del>order in the form depends on the attribute order?</del><ins>Yes, as well as group order and arrangement. The same is true for individual product pages.</ins>

</p>
<p>
	Suppose you have a category stocked with cell phones and you want to create custom fields for defining/specifying the following properties:
	<a href="{self}#carrier">Carrier</a>, <a href="{self}#features">Phone Features</a>, <a href="{self}#capacity">Batery Capacity</a> and 
	<a href="{self}#bandwidth">Bandwidth</a>. 
</p>

<h4 id="carrier">Creating <i>Carrier</i> attribute</h4>
	<ul>

		<li>Because mobile carrier is a simple text and there is (probably) a finite number of carriers click on the type drop-down menu and 
		select <i>Text selector</i> type from the list. Your chosen type is suitable for creating a predefined list of values which will be 
		displayed as a selection option.</li>
		<img src="image/doc/categories/attributes/type.bmp">
		<li>We check <i>Required</i> to make the field mandatory since it is an important feature. </li>
		<img src="image/doc/categories/attributes/required.bmp">
		<li><i>Can select multiple values</i> - we leave this checkbox clear to <del>restrict user's ability to associate cell phone with multiple carriers
		(which is not true most of the times).</del><ins> only allow assign one carrier to each phone</ins></li> 
		<img src="image/doc/categories/attributes/multiple_values.bmp">
		<li>Attribute's <i>title</i> represent its function thus we enter a meaningful name - <strong>Carrier</strong>. </li>
		<img src="image/doc/categories/attributes/title.bmp">
		<li>Handle - handle is generated automatically.<del> so you don't need to change it.</del> Handle is used to represent the attribute in URL's, so you can change it for SEO purposes, if needed.</li>
		<img src="image/doc/categories/attributes/handle.bmp">
		<li>In the description field we enter a brief description to describe the term "Carrier".</li> 
		<img src="image/doc/categories/attributes/description.bmp">
		<li>International details - if you need to translate attributes in other languages installed in your system, click on the Language to 
		expand additional fields (which include Title and Details).</li>


	<img src="image/doc/categories/attributes/attributes_international_details.bmp">

The last thing to do is to create Values which will be used as a selection option in the "Add new Product" form. To add values: 
	<ul>
		<li>click Values tab in the main window -> </li>
		<img src="image/doc/categories/attributes/values_tab.bmp">

		<li>when switched to the Value window enter the first Mobile Carrier in the text field provided.</li>
		<img src="image/doc/categories/attributes/empty_field.bmp">

		<li>click "Enter more values" for additional fields to appear</li>
		<img src="image/doc/categories/attributes/enter_values.bmp">
	
		<li>Keep in this manner until we have a full list of carriers.</li>
		<img src="image/doc/categories/attributes/values.bmp">

		<li>Make sure to click <strong>Save</strong> to save changes. </li>
		<img src="image/doc/categories/attributes/save.bmp">
	</ul>

	Your new attribute will be automatically generated in the "Add new Product" form and will look similar to this one:
	<img src="image/doc/categories/attributes/carrier.bmp">
	</ul>


<h4 id="features">Creating <i>Phone Features</i> attribute</h4>

<p>
	Cell phones might have a great variety of features therefore it would more convenient to create a list of features than re-enter them
	every time. The following parameters have to be set:
</p>
	<img src="image/doc/categories/attributes/multi.bmp">

	<ul>
		<li>Type - expand type list and choose Text -> Options as your type </li>
		<li>Required - leave the checkbox empty as some phones may don't have additional features (optional field/selection) </li>
		<li>Can select multiple values - mark the checkbox to allow multiple features assigned to a cell phone</li>
		<li>Title - enter here <strong>Features</strong> to represent attribute's purpose/function.</li>
		<li>Handle is generated automatically therefore you may leave the field as it is</li>
		<li>Description - enter a few sentences about features attribute to set its description.</li>

	</ul>

<p>The next thing to do is to create a value list of mobile's features. 

	<ul>
		<li>Click <i>Values</i> tab which appears on the right of the <i>Main</i> tab. </li>
		<img src="image/doc/categories/attributes/values_tab.bmp">
		<li>In the Values section enter all the necessary features one by one followed by the "Add more values" button.</li>
		<img src="image/doc/categories/attributes/enter_values.bmp">
		<li><strong>Save</strong> the attribute when done.</li>
	</ul>

</p>
	
	<p><ins>Also it should be mentioned that values can be reordered by dragging and dropping them</ins></p>

	The representation of your generated field could be similar to this:

	<img src="image/doc/categories/attributes/features.bmp">

<h4 id="capacity">Creating <i>Batery Capacity</i> attribute</h4>

<p>
	Battery capacity is expressed in numeric(al) format of mAh, therefore you should consider choosing a <strong>Number</strong> type. When it comes
	to field or selector it is up to you to decide whether you want to create a single field for entering a value or to create a pre-defined list of possible capacity values. 
	We choose a field type in the following example.
</p>

<ul>
	<li>Type - select Nember Field.</li>
	<li>Required - click the checkbox to make the field required attribute</li>
	<img src="image/doc/categories/attributes/required.bmp">
	<li>Title - enter <strong>Battery Capacity</strong> to represent attribute's purpose/function.</li>
	<img src="image/doc/categories/attributes/capacity_title.bmp">
	<li>Handle is generated automatically so we leave the field unaltered</li>
	<img src="image/doc/categories/attributes/capacity_handle.bmp">
	<li>In the description field provide brief information about the field similar to this</li>
	<img src="image/doc/categories/attributes/capacity_description.bmp">
</ul>	

<p class="note">Don't forget to click <strong>Save</strong> at the end.</p>
<br \>
<p>
	Generated attribute will provide a field for entering battery capacity:
</p>
	<img src="image/doc/categories/attributes/capacity_generated.bmp">
	
<h4 id="bandwidth">Creating <i>Bandwidth</i> attribute</h4>

<ins>There is also UMTS and CDMA, so this may not be a good example for number selector. On the other hand I cannot figure out what would be a good example and whether a strictly number multiple value selector is needed at all. Also Bandwidth doesn't seem to be a correct name for this attribute.</ins>

<p>
	Complete the following steps to create a bandwidth attribute.
</p>


	<img src="image/doc/categories/attributes/bandwidth.bmp">


<ul>
	<li>Type - select Number Options to create a list of possible bandwidth values.</li>
	<li>Required - mark the checkbox to set the attribute required</li>
	<li>Can select multiple values - mark the checkbox to allow multiple bandwidth selection </li>
	<li>Title - enter "Bandwidth"</li>
	<li>Handle is generated automatically thus you don't need to change it</li>
	<li>Description - enter description of Bandwidth.</li>

</ul>

To set values of the bandwidth options, go to Values section by clicking <strong>Values</strong> tab 

	<img src="image/doc/categories/attributes/values_tab.bmp">

Enter necessary values one by one in the Value field followed by the <strong>Add more values</strong> button.

	<img src="image/doc/categories/attributes/bandwidth_values.bmp">

Make sure to click <strong>Save</strong> to save changes when you're done. 

	<img src="image/doc/categories/attributes/save.bmp">

Your attribute will be placed in the <i>Add new Product</i> form:

	<img src="image/doc/categories/attributes/bandwidth_generated.bmp">

<h3 id="group">Group attributes</h3>

<p>LC allows you to group your attributes into logical chunks called "groups". To introduce a new group:</p>
	<ul>
		<li>Click <storng>Add new group</strong></li>
		<p><img src="image/doc/categories/attributes/add_new_group.bmp"></p>
		<li>Enter group title (to identify group)</li>
		<p><img src="image/doc/categories/attributes/group_title.bmp"></p>
		<li>Supply international translation if necessary (applicable)</li>
	</ul>
<p>Attribute groups appear as a rectangle which can contain as many attributes as neccesary. To place attributes into groups, simply drag attributes
into appropriate groups.</p>

<img src="image/doc/categories/attributes/groups_main.bmp">
<p>
	In the image above you see two groups named "Cell Phones" and "Dimensions" and one attribute "Color" what doesn't belong to any group. 
	To change group's name or delete a group click on one of the icons on the left:
</p>
<img src="image/doc/categories/attributes/group_edit.bmp">

<p class="note"><strong>Notice</strong>: deleting a group will cause all of its attributes to be deleted as well. Thus you have to <strong>ungroup
</strong> attributes that you want to keep.</p>


<h3 id="edit">Edit attribute</h3>

<p>To edit attribute: select an attribute from the list and click on its "pen" icon on the left:</p>

<img src="image/doc/categories/attributes/edit_attribute.bmp">

<p>Attribute's management form appears with its specification details. To edit any of attribute's fields or values, simply alter existing parameters/values
and save changes afterwards. For detailed description of fields, refer to <a href="{self}#create">Create new attribute</a> section.</p>

<h3 id="delete">Delete attribute</h3>

<p>To delete attribute: select an attribute from the list and click on its "delete" icon on the left:</p>

<img src="image/doc/categories/attributes/delete_attribute.bmp">

<h3 id="sort">Change attribute order</h3>

<p>(In LC sorting of attributes is implemented via drag&drop feature). To change the order of attributes, click on attributes empty space and then drag it
up or down.</p>

{helpSeeAlso}
	{see cat.details}
	{see cat.filters}
	{see cat.images}
{/helpSeeAlso}