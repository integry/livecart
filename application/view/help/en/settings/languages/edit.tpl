<p>To prepare your store for usage in another language you have to translate appropriate words or phrases
from English to the necessary language. (You can translate both frontend and backend of your store.)</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
	<ul>	
		<li><a href="{self}#user_interface">Translate user interface</a></li>
		<li><a href="{self}#product_info">Translate product info</a></li>
		<li><a href="{self}#find_missing_translations">Find missing translations</a></li>
	</ul>
</fieldset>
</div>


<h3 id="user_interface">Translate user interface</h3>

<p>There are two ways to translate the interface
<ul>	
	<li>Using a "Word Tree" - it's a straighforward technique discussed below.</li>
	<li>Using a "Live Translation" tool - the tool allows you to translate user interface directly from the frontend. To start using live translation tool, go to the Customize -> <a href="{help /customize.live}#translate">Live Customization</a> section.</li>
</ul>

<p>To translate interface using the <strong>word tree</strong>:</p>
<ol>
	<li>Select a group on the tree:</li>
	<img src="image/doc/language/edit/tree.bmp"/>
	<li>Enter the corresponding translations in the fields provided.</li>
	<p>(Take a look at the following example of an error message:)</p>
	<img src="image/doc/language/edit/field.bmp"/>
	<ul>
		<li>here <span style="background: #ffffcc">text field</span> is a field for your translations;</li>
		<li><span style="background: #ffffcc">_err_numeric</span> is a variable name;</li>
		<li>and <span style="background: #ffffcc">Values must be numeric</span> is the default translation which is used if the text field is left empty.</li>
	</ul>
	<p class="note"><strong>Note</strong>: You can also enter multiple lines of text in the text area - just click the "Down Arrow" key on your keyboard to switch to multi-line input field.</p>
	<li>Translation filter allows you to search and filter results. The search is carried out on the default values in English and translations as well but only within the word group chosen in the first step. You can search all groups by marking the "Search all files" checkbox.</li>
	<p>The following filtering options are available:</p>

<img src="image/doc/language/edit/translation_filter.bmp"/>
<ul>
	<li><span style="background: #ffffcc">All</span> – show all the existing words</li>
	<li><span style="background: #ffffcc">Translated</span> – display only translated words</li>
	<li><span style="background: #ffffcc">Not translated</span> – show words that have not been translated</li>
</ul>
<p>The results that match your search criteria appear below automatically.</p>
</ol>

<h3 id="product_info">Translate product info</h3>	

<p>You can translate products, categories, attributes and other data directly from their management pages, 
for example, products modification page. The fields for entering translations are typically placed below the
 main data form.</p>
 

<h3 id="find_missing_translations">Find missing translations</h3>	

<ul>
	<li>To find missing translations, select "Not translated" in the Translation Filter.</li>
	<li>Mark the "Search all files" checkbox.</li>
</ul>
<img src="image/doc/language/edit/translation_filter2.bmp"/>

<p></p>
<!--
<h3 id="find_specific_word">Find and translate a specific word or sentence</h3>
<img src="image/doc/language/edit/search.bmp"/>
<p>The results that match your search criteria appear below automatically.</p>
-->

{helpSeeAlso}
	{see settings.languages}
{/helpSeeAlso}	