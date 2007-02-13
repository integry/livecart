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

<h3 id="create">Create Attribute</h3>
<p>
	Here are some examples of a few simple attributes you might find handy<ins>...</ins> Later we will show how they are actually implemented in the <i>Filters</i> section.
	Notice that because attributes specify properties of the products within a specific category they are automatically included in the 
	<i>Add New Product</i> form. (Therefore in these example we will demonstrate how ...)
	Suppose you have a category stocked with cell phomes and you want to create fields for defining/specifying the following properties:
	<a href="{self}#carrier">Carrier</a>, <a href="{self}#features">Phone Features</a>, <a href="{self}#capacity">Batery Capacity</a> and 
	<a href="{self}#bandwidth">Bandwidth</a>. 
</p>

<h4 >Creating attribute</h4>

<p>
	Standard form <ins>...</ins>
</p>

<h4 id="carrier">Creating <i>Carrier</i> attribute</h4>

<p>
	Attributes are defined by their <i>type</i> which can be text, date or numbers. Because mobile carrier is a simple text and there is (probably)
	a finite number of carriers we select <i>Text selector</i> as our type. 
	<ul>
		<li>We check <i>Required</i> to make the field mandatory since it is an important feature. </li>
		<li><i>Can select multiple values</i> - we leave this checkbox clear to restrict user's ability to associate cell phone with multiple carriers
		(which is not true most of the times).</li> 
		<li>Attribute's <i>title</i> represent its function thus be we enter a meaningful name - <strong>Carrier</strong>. </li>
		<li>Handle <ins>...</ins></li>
		<li>Description <ins>...</ins></li> 
	</ul>
	The last thing to do is to create Values which will be used as a selection option in the "Add new Product" form. To add values, click Values 
	tab in the main window and supply values one by one in the field provided. To do that , simply use a text field named Values followed by 
	"Enter more values". Click save once you are done. Your created attribute will be automatically generated in the "Add new Product" form and
	will look similar to this one:
	</p>
	<img src="image/doc/categories/attributes/carrier.bmp">


<h4 id="features">Creating <i>Phone Features</i> attribute</h4>
<p>
	As in previous example we will use Text Selector because we know that cell phones can have many features (and it more convenient to list them 
	out than have them entered every time adding a new cell)
</p>
	<ul>
		<li>Type - Text selector is suitable for creating a predefined list of values which will be displayed as a selection option. To create
		a value list click <i>Values</i> tab which appears on the right of the <i>Main</i> tab. In the Values section click <i>Enter more values</i>
		and supply a value in the field provided:

		In the <i>Add new Product</i> form a value list might look similar to this one:</li>

		<br \>
		(Keep in mind that a long list of values enlarges your <i>Add Product</i> form as well.)
		<li>Can select multiple values - <ins>...</ins></li>
		<li>Required - <ins>...</ins></li>
	</ul>

<h4 id="capacity">Creating <i>Batery Capacity</i> attribute</h4>

<h4 id="bandwidth">Creating <i>Bandwidth</i> attribute</h4>


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