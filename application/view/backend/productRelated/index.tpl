<html>
<head>
    <base href="http://livecart/public/" />
</head>
<body>

<script type="text/javascript" src="/public/javascript/library/prototype/prototype.js"></script>
<script type="text/javascript" src="/public/javascript/library/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="/public/javascript/backend/Backend.js"></script>    
<script type="text/javascript" src="/public/javascript/library/ActiveList.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/ActiveForm.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/State.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/Validator.js" ></script>
<script type="text/javascript" src="/public/javascript/library/json.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/Product.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/RelatedProduct.js" ></script>

<link href="stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="stylesheet/library/ActiveList.css" media="screen" rel="Stylesheet" type="text/css"/>


Related products

product #{$id} in category #{$categoryID}


{literal}
<a href="#selectProduct" id="selectProduct_{/literal}{$productID}{literal}">{/literal}{t _select_product}{literal}</a>
<script type="text/javascript">
    Backend.RelatedProduct.links = {};
    Backend.RelatedProduct.links.related = '{/literal}{link controller=backend.productRelated action=related}{literal}';
    Backend.RelatedProduct.links.selectProduct = '{/literal}{link controller=backend.productRelated action=selectProduct}{literal}';
    
    Backend.RelatedProduct.messages = {};
    Backend.RelatedProduct.messages.selectProductTitle = '{t _select_product|add_slashes}';
    
    Event.observe($("selectProduct_{/literal}{$productID}{literal}"), 'click', function(e) {
        Event.stop(e);
        new Backend.RelatedProduct.SelectProductPopup(
            Backend.RelatedProduct.links.selectProduct, 
            Backend.RelatedProduct.messages.selectProductTitle, 
            {
                onProductSelect: function() { Backend.RelatedProduct.addProductToList({/literal}{$productID}{literal}, this.productID) }
            }
        );
    });
    
</script>
{/literal}


<ul id="relatedProducts_{$productID}" class="activeList_add_sort activeList_add_delete">
    
</ul>

{literal}
<script type="text/javascript">
    ActiveList.prototype.getInstance($("relatedProducts_{/literal}{$productID}{literal}"), 
    {
        beforeDelete: function(li){ console.info('beforeDelete') },
        beforeDelete: function(li, response){ console.info('afterDelete') },
        beforeDelete: function(li){ console.info('beforeSort') },
        beforeDelete: function(li, response){ console.info('afterSort') }
    });
</script>
{/literal}

</body>
</html>