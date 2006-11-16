{includeJs file=library/prototype/prototype.js}
{includeJs file=library/livecart.js}
{includeJs file=backend/keyboard.js}
{includeJs file=library/scriptaculous/scriptaculous.js}
{includeJs file=backend/activeList.js}
{includeJs file=backend/specFieldManager.js}
{includeJs file=library/trace/jsTrace.js}
{includeJs file=library/trace/dom-drag.js}
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


{literal}
<style type="text/css">
.activeList_icons
{
    float: left;
}

.activeList li
{
    clear: both;
}

.step_translations_language
{
	display: none;
}
fieldset.step_main,  fieldset.step_values, fieldset.step_translations
{
	display: none;
}

.specField_form_values_group div input
{
    width: 10em;
}

.sortable_drag_handler, .sortable_drag_handler_for_fields {
	cursor: pointer;
	cursor: hand;
    background: yellow;
    color: black;
    float: left;
}

ul#specField_items_list, ul#specField_items_list ul
{
    list_style: none;
}


li
{
    display: block;
}
</style>
{/literal}


{literal}
<script type="text/javascript">

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
{literal}
</script>
{/literal}


<ul id="specField_items_list" class="activeList_add_sort activeList_add_edit activeList_add_delete">
	<li id="specField_items_list_96"  class="">Item 1</li>
	<li id="specField_items_list_95"  class="">Item 2</li>
	<li id="specField_items_list_100" class="activeList_remove_sort">Item 3</li>
	<li id="specField_items_list_101" class="">Item 4</li>
	<li id="specField_items_list_102" class="">Item 5</li>
</ul>



{literal}
<script type="text/javascript">
    new LiveCart.ActiveList('specField_items_list', {
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty())
            {
                return '{/literal}{link controller=backend.specField action=item}{literal}'+this.getRecordId(li)
            }
            else
            {
                this.toggleContainer()
            }
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
        afterEdit:      function(li, response)
        {
             this.toggleContainer();
             this.getContainer(li).innerHTML = response;
             LiveCart.AjaxUpdater.prototype.runJavaScripts(response);
        },
        afterSort:      function(li, response)
        {
            jsTrace.send( 'Record #' + this.getRecordId(li) + ' changed position');
        },
        afterDelete:    function(li, response)
        {
            Element.remove(li);
            jsTrace.send('Record #' + this.getRecordId(li) + ' was deleted');
        }
    });
</script>
{/literal}