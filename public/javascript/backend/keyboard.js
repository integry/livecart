var Keyboard = Class.create();
Keyboard.prototype = {
    KEY_TAB:    9,
    KEY_ENTER:  13,
    KEY_SHIFT:  16,
    KEY_ESC:    27,
    KEY_UP:     38,
    KEY_DOWN:   40,
    KEY_DEL:    46,

    initialize: function()
    {

    },

    /**
     * Determines which key was pressed3
     *
     * @return int Key number
     */
    function getPressedKey(e)
    {
        // IE
    	if (window.event)
        {
        	keynum = e.keyCode;
        }

        // Netscape/Firefox/Opera
    	else if (e.which)
        {
        	keynum = e.which;
        }

        return keynum;
    }

    /**
     * Deselects any window text (except in controls)
     */
//    function deselectText()
//    {
//    	if (document.selection)
//    	{
//    		document.selection.empty();
//    	}
//    	else if (window.getSelection)
//    	{
//    	    window.getSelection().removeAllRanges();
//    	}
//    }
}

var keyboard = new Keyboard();

