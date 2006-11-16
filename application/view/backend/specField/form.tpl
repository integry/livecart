{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}

{includeCss file="base.css"}

{form handle=$specFieldForm action="controller=backend.specField action=add" method="post" onsubmit="new LiveCart.AjaxUpdater(this, 'output', 'indicator', 'top'); return false;"}
<fieldset>
	<legend>Add new category field</legend>
	{error}
		<span class="error">There are some error in this form (see below)</span>
	{/error}
	<table class="formContainer">
		<tr>
			<td class="labelContainer">Field name: </td>
			<td class="fieldContainer">
				{textfield name="name"}
				{error for="name" msg=$msg}
					<br/>
					<span class="error">{$msg}</span>
				{/error}
			</td>
		</tr>
		<tr>
			<td>Field Description:</td>
			<td>
				{textarea name="description"}
				{error for="description" msg=$msg}
					<br/>
					<span class="error">{$msg}</span>
				{/error}
			</td>
		</tr>
		<tr>
			<td>Field handle:</td>
			<td>{textfield name="handle" style="padding: 5px;"}</td>
		</tr>
		<tr>
			<td>Field type:</td>
			<td>{selectfield name="type" options=$typeList}</td>
		</tr>
		<tr>
			<td>Language field:</td>
			<td>{checkbox name="language_field" value="yes"}</td>
		</tr>
		<tr>
			<td>Radio button test:</td>
			<td>
				{radio name="test" value="1"} Red<br/>
				{radio name="test" value="3"} Greed<br/>
				{radio name="test" value="5"} Blue
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<div id="indicator" style="display: none">Processing...</div>
				<input type="submit" class="submit" value="Add field &raquo;"/>
			</td>
		</tr>
	</table>
</fieldset>

<div id="output">
	{foreach from=$specFieldList item=specField}
	{include file="backend/specField/item.tpl"}
	{/foreach}
</div>

<fieldset>
	<legend>Category field list</legend>
</fieldset>
{/form}
