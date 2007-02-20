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

<p>LiveCart supports various product attribute types which can be text, number or date.</p>
<p>Below you'll find a couple of practical examples on how to choose which attribute type would suit best for describing a particular product property.</p>

<h3 id="create">Create Attribute</h3>
<p>
	To create an attribute you have to fill out a simple form with a few fields. 
	
	Later we will show how they are actually implemented in the <i>Filters</i> section.
	
	Notice that because attributes specify properties of the products within a specific category and they are automatically included in the 
	<i>Add New Product</i> form. Therefore in these example we will demonstrate how attributes are generated in the form.

</p>
<p>
	Here are some examples of a few simple attributes you might find handy. Suppose you have a category stocked with cell phones and you want to create fields for defining/specifying the following properties:
	<a href="{self}#carrier">Carrier</a>, <a href="{self}#features">Phone Features</a>, <a href="{self}#capacity">Batery Capacity</a> and 
	<a href="{self}#bandwidth">Bandwidth</a>. 
</p>

<h4 id="carrier">Creating <i>Carrier</i> attribute</h4>
	<ul>

		<li>Because mobile carrier is a simple text and there is (probably) a finite number of carriers click on the type drop-down menu and 
		select <i>Text selector</i> type from the list. Your chosen type is suitable for creating a predefined list of values which will be 
		displayed as a selection option.</li>
<p>
	<img src="image/doc/categories/attributes/type.bmp">
</p>

		<li>We check <i>Required</i> to make the field mandatory since it is an important feature. </li>
<p>
	<img src="image/doc/categories/attributes/required.bmp">
</p>
		<li><i>Can select multiple values</i> - we leave this checkbox clear to restrict user's ability to associate cell phone with multiple carriers
		(which is not true most of the times).</li> 
<p>
	<img src="image/doc/categories/attributes/multiple_values.bmp">
</p>
		<li>Attribute's <i>title</i> represent its function thus we enter a meaningful name - <strong>Carrier</strong>. </li>
<p>
	<img src="image/doc/categories/attributes/title.bmp">
</p>
		<li>Handle - handle is generated automatically so you don't need to change it.</li>
<p>
	<img src="image/doc/categories/attributes/handle.bmp">
</p>
		<li>In the description field we enter a brief description to describe the term "Carrier".</li> 
<p>
	<img src="image/doc/categories/attributes/description.bmp">
</p>

	<li>International details - if you need to translate attributes in other languages installed in your system, click on the Language to 
	expand additional fields (which include Title and Details).</li>

<p>
	<img src="image/doc/categories/attributes/attributes_international_details.bmp">
</p>


<p>
	The last thing to do is to create Values which will be used as a selection option in the "Add new Product" form. 
	To add values: 
</p>

		<li>click Values tab in the main window -> </li>
		<p><img src="image/doc/categories/attributes/values_tab.bmp"></p>
		<li>when switched to the Value window enter the first Mobile Carrier in the text field provided.</li>
	<p><img src="image/doc/categories/attributes/empty_field.bmp"></p>
		<li>click "Enter more values" for additional fields to appear</li>
	
<p>
	<img src="image/doc/categories/attributes/enter_values.bmp">
</p>

<p>
	Keep in this manner until we have a full list of carriers.
</p>

<p>
	<img src="image/doc/categories/attributes/values.bmp">
</p>

<p>
	Make sure to click <strong>Save</strong> to save changes. 
</p>

<p>
	<img src="image/doc/categories/attributes/save.bmp">
</p>

<p>
	Your new attribute will be automatically generated in the "Add new Product" form and will look similar to this one:
</p>

<p>
	<img src="image/doc/categories/attributes/carrier.bmp">
</p>
	</ul>


<h4 id="features">Creating <i>Phone Features</i> attribute</h4>

<p>
	Cell phones might have a great variety of features therefore it would more convenient to create a list of features than re-enter them
	every time. The following parameters have to be set:
</p>

<p>
	<img src="image/doc/categories/attributes/multi.bmp">
</p>
	<ul>
		<li>Type - expand type list and choose Text -> Options as your type </li>
		<li>Required - leave the checkbox empty as some phones may don't have additional features (optional field/selection) </li>
		
		<li>Can select multiple values - mark the checkbox to allow multiple features assigned to a cell phone</li>
		
		<li>Title - enter here <strong>Features</strong> to represent attribute's purpose/function.</li>
		
		<li>Handle is generated automatically therefore you may leave the field as it is</li>
		
		<li>Description - enter a few sentences about features attribute to set its description.</li>
	</ul>

	The next thing to do is to create a value list of mobile's features. 
	<br \>
	<ul>
		<li>Click <i>Values</i> tab which appears on the right of the <i>Main</i> tab. </li>
		<p><img src="image/doc/categories/attributes/values_tab.bmp"></p>
		<li>In the Values section enter all the necessary features one by one followed by the "Add more values" button.</li>
		<p><img src="image/doc/categories/attributes/enter_values.bmp"></p>
		<li><strong>Save</strong> the attribute when done.</li>
	</ul>
	The representation of your generated field could be similar to this:
<p>
	<img src="image/doc/categories/attributes/features.bmp">
</p>


<h4 id="capacity">Creating <i>Batery Capacity</i> attribute</h4>

<p>
	Battery capacity is expressed in numeric(al) format of mAh, therefore you should consider choosing a <strong>Number</strong> type. When it comes
	to field or selector it is up to you to decide whether you want to create a single field for entering a value or to create a pre-defined list of possible capacity values. 
	We choose a field type in the following example.
</p>

<ul>
	<li>Type - select Nember Field.</li>
	<li>Required - click the checkbox to make the field required attribute</li>
	<p><img src="image/doc/categories/attributes/required.bmp"></p>
	<li>Title - enter <strong>Battery Capacity</strong> to represent attribute's purpose/function.</li>
	<p><img src="image/doc/categories/attributes/capacity_title.bmp"></p>
	<li>Handle is generated automatically so we leave the field unaltered</li>
	<p><img src="image/doc/categories/attributes/capacity_handle.bmp"></p>
	<li>In the description field provide brief information about the field similar to this</li>
	<p><img src="image/doc/categories/attributes/capacity_description.bmp"></p>
</ul>	

<p class="note">Don't forget to click <strong>Save</strong> at the end.</p>
<br \>
<p>
	Generated attribute will provide a field for entering battery capacity:
</p>

<p>
	<img src="image/doc/categories/attributes/capacity_generated.bmp">
</p>


	
<h4 id="bandwidth">Creating <i>Bandwidth</i> attribute</h4>

<p>
	Complete the following steps to create a bandwidth attribute.
</p>

<p>
	<img src="image/doc/categories/attributes/bandwidth.bmp">
</p>

<ul>
	<li>Type - select Number Options to create a list of possible bandwidth values.</li>
	<li>Required - mark the checkbox to set the attribute required</li>
	<li>Can select multiple values - mark the checkbox to allow multiple bandwidth selection </li>
	<li>Title - enter "Bandwidth"</li>
	<li>Handle is generated automatically thus you don't need to change it</li>
	<li>Description - enter description of Bandwidth.</li>

</ul>

To set values of the bandwidth options, go to Values section by clicking <strong>Values</strong> tab 

	<p><img src="image/doc/categories/attributes/values_tab.bmp"></p>

Enter necessary values one by one in the Value field followed by the <strong>Add more values</strong> button.

	<p><img src="image/doc/categories/attributes/bandwidth_values.bmp"></p>

Make sure to click <strong>Save</strong> to save changes when you're done. 

	<p><img src="image/doc/categories/attributes/save.bmp"></p>

Your attribute will be placed in the <i>Add new Product</i> form:

	<p><img src="image/doc/categories/attributes/bandwidth_generated.bmp"></p>

<h3 id="group">Group attributes</h3>

<p>
</p>

<h3 id="edit">Edit attribute</h3>

<p>
</p>

<h3 id="delete">Delete attribute</h3>

<p>
</p>

<h3 id="sort">Change attribute order</h3>

<p>
</p>

{helpSeeAlso}
	{see cat.details}
	{see cat.filters}
	{see cat.images}
{/helpSeeAlso}