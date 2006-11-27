{includeJs file=library/scriptaculous/scriptaculous.js}
{includeJs file=backend/keyboard.js}
{includeJs file=library/livecart.js}
{includeJs file=backend/activeList.js}
{includeJs file=backend/specFieldManager.js}
{includeJs file=library/trace/jsTrace.js}
{includeJs file=library/trace/dom-drag.js}

{literal}
<script type="text/javascript">
//<[!CDATA[


    /**
     * Create spec field prototype. Some fields are always the same
     * so we define them in
     */
    {/literal}
    {foreach from=$configuration item="configItem" key="configKey"}
        {if $configKey == 'types'}
            LiveCart.SpecFieldManager.prototype.{$configKey} = LiveCart.SpecFieldManager.prototype.createTypesOptions({json array=$configItem});
        {else}
            LiveCart.SpecFieldManager.prototype.{$configKey} = {json array=$configItem};
        {/if}
    {/foreach}
    {literal}


    specFieldListCallbacks = {
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit'))
            {
                return '{/literal}/backend.specField/item/{literal}'+this.getRecordId(li)
            }
            else
            {
                var controls = document.getElementsByClassName("specField_controls", li)[0];
                controls.style.display = (controls.style.display = 'none') ? 'inline' : 'none';

                this.toggleContainer(li, 'edit');
            }
        },
        afterEdit:      function(li, response)
        {

            this.getContainer(li, 'edit').innerHTML = response;
            LiveCart.AjaxUpdater.prototype.runJavaScripts(response);

//            this.getContainer(li, 'edit').innerHTML = 'test';

            var controls = document.getElementsByClassName("specField_controls", li)[0];
            controls.style.display = (controls.style.display = 'none') ? 'inline' : 'none';

            jsTrace.send("afterEdit()");

            this.rebindIcons(li);
            this.createSortable();

            this.toggleContainer(li, 'edit');
        },


        beforeSort:     function(li, order)
        {
            return 'someurl.php?' + order
        },
        afterSort:      function(li, response)
        {
//            alert( 'Record #' + this.getRecordId(li, 'edit') + ' changed position');
        },


        beforeDelete:   function(li)
        {
            if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?'))
            {
                return '{/literal}{link controller=backend.specField action=delete}{literal}?id='+this.getRecordId(li)
            }
        },
        afterDelete:    function(li, response)
        {
            Element.remove(li);
            alert('Record #' + this.getRecordId(li, 'edit') + ' was deleted');
        }
    };


// ]!]>
</script>
{/literal}





<!-- Spec field title -->
<h2>Laptop</h2>

<!-- Form for creating new spec field -->
<div id="specField_item_new">
    <a href="#new" id="specField_item_new_show">Add new spec field</a>
    <div id="specField_item_new_form" style="display: none;">
        {include file="backend/specField/form.manageSpecField.tpl"}
    </div>
</div>

<br />

<!-- List of all spec fields -->
<ul id="specField_items_list" class="activeList_add_delete activeList_add_edit activeList_add_sort">
{foreach item="field" from=$specFields}
	<li id="specField_items_list_{$field.ID}">
    	<div class="specField_title">{$field.name.en}</div>
	</li>
{/foreach}
</ul>


{literal}
<script type="text/javascript">
    $("specField_item_new_show").onclick = function(e) { LiveCart.SpecFieldManager.prototype.createNewAction(e) }
    window.activeSpecFieldsList = new LiveCart.ActiveList('specField_items_list', specFieldListCallbacks);
</script>
{/literal}