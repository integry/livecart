<script type="text/javascript">
    require_once('library/prototype/prototype.js');
    require_once('library/livecart.js');
    require_once('backend/keyboard.js');
    require_once('library/scriptaculous/scriptaculous.js');
    require_once('backend/activeList.js');
    require_once('backend/specFieldManager.js');
    require_once('library/trace/jsTrace.js');
    require_once('library/trace/dom-drag.js');
</script>

<h2>Laptop</h2>





<script type="text/javascript">{literal}
	function createTypesOptions(types)
	{
	   var typesOptions = {};
	   $H(types).each(function(value) {
	       var options = [];

	       $H(value.value).each(function(option) {
	           options[options.length] = new Option(option.value, option.key);
	       });

	       typesOptions[value.key] = options;
		});

		return typesOptions;
	}

{/literal}
{foreach from=$configuration item="configItem" key="configKey"}
    {if $configKey == 'types'}
        LiveCart.SpecFieldManager.prototype.{$configKey} = createTypesOptions({json array=$configItem});
    {else}
        LiveCart.SpecFieldManager.prototype.{$configKey} = {json array=$configItem};
    {/if}
{/foreach}
</script>



{literal}
<style type="text/css">
.specField_form_values_group div input
{
    width: 10em;
}

fieldset.step_main,  fieldset.step_values, fieldset.step_translations, .step_translations_language
{
	display: none;
}

.change_state
{
    float: left;
    padding: 0 5px;
}

.specField_save
{
	display: none;
	clear: right;
}

#specField_item_new
{
    background: #cccccc;
    display: none;
}

</style>
{/literal}

<ul id="specField_items_list" class="activeList_add_delete activeList_add_edit activeList_add_sort">
{foreach item="field" from=$specFields}
	<li id="specField_items_list_{$field.ID}">
    	<div class="specField_title">{$field.name.en}</div>
	</li>
{/foreach}
</ul>


<div>
    <a href="#new" id="specField_item_new_show">Add new spec field</a>
    <div id="specField_item_new">
    {include file="backend/specField/form.manageSpecField.tpl"}
    </div>
</div>

{literal}
<script type="text/javascript">
    function showNewSpecField(e)
    {
        Event.stop(e);
		$("specField_item_new").style.display = 'block';

        var saveButton = document.getElementsByClassName("specField_save", $("specField_item_new"))[0];
        saveButton.style.display = (saveButton.style.display = 'none') ? 'block' : 'none';
	}

	function saveSpecField(form)
	{
        new Ajax.Request(
            form.action,
            {
                method: form.method,
                postBody: Form.serialize(form),
                onComplete: function(param) { jsTrace.send("Data sent") }
            }
        );
	}


	document.getElementsByClassName('specField_save', $("specField_item_new"))[0].onclick = function(e)
	{
//	    $("specField_item_new").getElementsByTagName("form")[0].submit();
	    saveSpecField($("specField_item_new").getElementsByTagName("form")[0])
	}

    $("specField_item_new_show").onclick = function(e) { if(!e){ e = window.event; e.target = e.srcElement; } showNewSpecField(e) }

</script>



<script type="text/javascript">
    new LiveCart.ActiveList('specField_items_list', {
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit'))
            {
                return '{/literal}/backend.specField/item/{literal}'+this.getRecordId(li)
            }
            else
            {
                this.toggleContainer(li, 'edit');

                var saveButton = document.getElementsByClassName("specField_save", li)[0];
                saveButton.style.display = (saveButton.style.display = 'none') ? 'block' : 'none';
            }
        },
        afterEdit:      function(li, response)
        {
             this.toggleContainer(li, 'edit');
             this.getContainer(li, 'edit').innerHTML = response;
             LiveCart.AjaxUpdater.prototype.runJavaScripts(response);

            var saveButton = document.getElementsByClassName("specField_save", li)[0];
            saveButton.style.display = (saveButton.style.display = 'none') ? 'block' : 'none';
        },
        beforeSort:     function(li, order)
        {
            return 'someurl.php?' + order
        },
        beforeDelete:   function(li)
        {
            if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?'))
            {
                return '{/literal}{link controller=backend.specField action=delete}{literal}?id='+this.getRecordId(li)
            }
        },
        afterSort:      function(li, response)
        {
            jsTrace.send( 'Record #' + this.getRecordId(li, 'edit') + ' changed position');
        },
        afterDelete:    function(li, response)
        {
            Element.remove(li);
            jsTrace.send('Record #' + this.getRecordId(li, 'edit') + ' was deleted');
        }
    });
</script>
{/literal}