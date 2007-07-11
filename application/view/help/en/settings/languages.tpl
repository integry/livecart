<p>LiveCart supports multiple languages for the same system, which enables you to adjust your shop for 
international usage. You can translate both interface information (menus, captions, error messages, etc.), 
as well as most of the data, which is stored in the system, like product descriptions, category names and 
others. (This may seem overwhelming at first but as you will see there are a couple of handy tools to help 
you deal with interface translations (see <a href="{help /customize.live}#translate">Live Translation</a>).)</p>

<div class="tasks">
<fieldset>
<legend>Things you can do</legend>
<ul>	
<!--
	<li>(<a href="{self}#change working">Change working (interface) language</a>)</li>
-->
	<li><a href="{self}#add">Add a new language</a></li>
	<li><a href="{self}#translate">Translate to other languages</a></li>
	<li><a href="{self}#reorder">Reorder languages</a></li>
	<li><a href="{self}#enable_disable">Enable or disable languages</a></li>
	<li><a href="{self}#change_default">Change default language</a></li>
	<li><a href="{self}#remove">Remove language</a></li>
</ul>
</fieldset>
</div>

<!--
<h3 id="change working">Change interface language</h3>	

<p>To change the interface language, click <strong>Language</strong> link in the LiveCart's main window's upper right corner and choose a language from the list displayed.</p>


-->
<h3 id="add">Add new language</h3>	
<p>To add a new language:</p>	

<ol>
<li>Click the "Add New Language" link:</li>
<img src="image/doc/language/add_language.bmp"/>
<li>Then select a language from the pulldown menu:</li>
<img src="image/doc/language/menu.bmp"/>
<li>Click "Add" for the language to appear below in the language list.</li>
<img src="image/doc/language/add_button.bmp"/>
</ol>


<h3 id="translate">Translate</h3>	
<p>To translate languages, proceed to the <a href="{help /settings.languages.edit}">Language translation</a> page by clicking the <strong>pen</strong> icon:</p>
<img src="image/doc/language/edit.bmp"/>


<h3 id="reorder">Reorder Languages</h3>	

<p>Language arrangement affects the order in which languages are displayed in language switching menus. To reorder languages click on a language and then drag and drop it to set the appropriate order.</p>
<img src="image/doc/language/sort.bmp"/>


<h3 id="enable_disable">Enable or disable languages</h3>	

<p>All the newly added languages are inactive by default. You might want to keep a language inactive while its
 translation is being carried out. To activate a language mark its checkbox on the left.</p>
<img src="image/doc/language/enable.bmp"/>

<p>Clear the checkbox to disable the language.</p>

<p class="note"><strong>Note</strong>: The disabled language will still be available for making translations in 
backend, so you could prepare all the necessary translations before displaying any content in it. 
When you are done with the translations just activate the language to make it available in your e-store.</p>	


<h3 id="change_default">Change default language</h3>	

<p>
The default language serves two main purposes:
<ul>
	<li>It is the language your e-store visitors see when first visiting the site. That is, all the frontend content is 
	displayed in the default language until the visitor switches to a different language.</li>
	<li>Any translations that are missing for any other language (interface elements, product or category 
	information, etc.) are automatically taken from the default language.</li>	
</ul>
</p>

<p>To change the default language click the "Set as default" link under the desired language name.</p>

<img src="image/doc/language/set_default_red.bmp"/>


<h3 id="remove">Remove a language</h3>	 

<p>To delete a language, hover over it and click the "Delete" icon on the left.</p>

<img src="image/doc/language/delete.bmp"/>

<p class="note"><strong>Note</strong>: If you remove a language (accidently or not), none of translations of the 
interface elements will be lost for good. If you removed a language by accident, just add it again and all your 
translations will still be in place.</p>

{helpSeeAlso}
	{see settings.languages.edit}
{/helpSeeAlso}