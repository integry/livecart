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
    function toggleNewSpecField(e)
    {
        if(!e){
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        jsTrace.send($("specField_item_new_form").style.display);

        var saveButton = document.getElementsByClassName('specField_save', $("specField_item_new_form"))[0];
        saveButton.style.specField_controls = (saveButton.style.specField_controls = 'none') ? 'inline' : 'none';
//        if($("specField_item_new_form").style.display = 'none')
//        {
//            $('specField_item_new_show').style.display = 'none';

//		    Effect.BlindDown("specField_item_new_form", {duration: 0.3});
//		    Effect.Appear("specField_item_new_form", {duration: 0.66});

            setTimeout("Effect.BlindDown('specField_item_new_form', {duration: 0.5})", 50);
            setTimeout("Effect.Appear('specField_item_new_form', {duration: 0.5})", 50);
		    $("specField_item_new_form").style.display = 'block';

//        }
//        else
//        {
//            $('specField_item_new_show').style.display = 'block';
//            Effect.BlindUp("specField_item_new_form", {duration: 0.15});
//            Effect.Fade("specField_item_new_form", {duration: 0.15});
//            $("specField_item_new_form").style.display = 'none'
//        }

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
                this.toggleContainer(li, 'edit');

                var saveButton = document.getElementsByClassName("specField_save", li)[0];
                saveButton.style.specField_controls = (saveButton.style.specField_controls = 'none') ? 'inline' : 'none';
            }
        },
        afterEdit:      function(li, response)
        {
             this.toggleContainer(li, 'edit');
             this.getContainer(li, 'edit').innerHTML = response;
             LiveCart.AjaxUpdater.prototype.runJavaScripts(response);

            var saveButton = document.getElementsByClassName("specField_save", li)[0];
            saveButton.style.specField_controls = (saveButton.style.specField_controls = 'none') ? 'inline' : 'none';
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
    };


// ]!]>
</script>
{/literal}





<!-- Spec field title -->
<h2>Laptop</h2>

<!-- Form for creating new spec field -->
<div style="vertical-align: middle;">
    <a href="#new" id="specField_item_new_show">Add new spec field</a>
    <div id="specField_item_new_form">
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
    $("specField_item_new_show").onclick = function(e) { toggleNewSpecField(e) }
    document.getElementsByClassName("specField_cancel", $("specField_item_new_form"))[0].onclick = function(e) { toggleNewSpecField(e) }

	document.getElementsByClassName('specField_save', $("specField_item_new_form"))[0].onclick = function(e)
	{
//	    $("specField_item_new_form").getElementsByTagName("form")[0].submit();
	    saveSpecField($("specField_item_new_form").getElementsByTagName("form")[0])
	}

    new LiveCart.ActiveList('specField_items_list', specFieldListCallbacks);
</script>
{/literal}