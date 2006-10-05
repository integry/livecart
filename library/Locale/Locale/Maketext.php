<?php
/* vim:set expandtab tabstop=4 shiftwidth=4 textwidth=75: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | COPYRIGHT/LICENSE                                                    |
// |   Original idea for this module by Sean M. Burke <sburke@cpan.org>   |
// |   and Jordan Lachler <lachler@unm.edu>                               |
// |                                                                      |
// |   Original Perl module by Sean M. Burke <sburke@cpan.org>            |
// |   Original Perl module copyright by Sean M. Burke <sburke@cpan.org>  |
// |                                                                      |
// |   PHP Port by Hans Juergen von Lengerke <hans@lengerke.org>          |
// |   PHP Port copyright by Hans Juergen von Lengerke <hans@lengerke.org>|
// |                                                                      |
// |   This module is free software. You can redistribute it and/or       |
// |   modify it under the conditions of the Artistic License:            |
// |   http://www.opensource.org/licenses/artistic-license.php            |
// +----------------------------------------------------------------------+
//
// $Id: Maketext.php,v 1.11 2004/04/17 11:26:05 lengerkeh Exp $

/**
 * @package  Locale_Maketext
 * @category Internationalisation
 */

/**
 * Requires PEAR
 */
require('PEAR.php');

/**
 * Error constant indicating a Maketext compile error
 *
 * @access  public
 * @name    LOCALE_MAKETEXT_ERROR_COMPILE
 */
define('LOCALE_MAKETEXT_ERROR_COMPILE', 10);

/**
 * Extensible text localisation framework
 *
 * @author   Hans Juergen von Lengerke <hans@lengerke.org>
 * @version  $Revision: 1.11 $
 * @package  Locale_Maketext
 * @access   public
 */
class Locale_Maketext extends PEAR {

    # --------------------------------------------------------------------
    # Member variables
    # --------------------------------------------------------------------

    /**
     * cache for compiled message functions
     *
     * @access  private
     * @var     array
     */
    var $_msg_func_cache = array();

    # --------------------------------------------------------------------
    # Public methods
    # --------------------------------------------------------------------

    /**
    * static factory method for creating Locale_Maketext objects.
    *
    * Objects will be created dependend on the existence of locale-level
    * specialized Locale_Maketext classes. If a locale-level specialized
    * Locale_Maketext class exists, it will be instantiated and returned.
    * If it does not exist, the general purpose Locale_Maketext class will
    * be instantiated.
    *
    * If you want to extend the base Locale_Maketext class, just specify
    * the classname of your extended class as $basename.
    *
    * @static
    * @access   public
    * @return   object  mixed Returns $baseclass_$locale, or, if that
    *                         fails, $baseclass
    * @param    string  $baseclass "Locale_Maketext" or name of own,
    *                              extended Locale_Maketext base class
    * @param    string  $locale    Standard Locale Name (e.g. en_US, de_DE)
    */
    function &factory ($baseclass = __CLASS__, $locale = '')
    {
        $l10nclassname = "${baseclass}_${locale}";
				
        if (!class_exists ($l10nclassname)) {
            $l10nclassname = $baseclass;
        }        
        $l10nclass = new $l10nclassname;
        return $l10nclass;
    }

    /**
     * Constructor, private because the factory() method should be used
     *
     * @access private
     *
     */
    function Locale_Maketext()
    {
        $this->PEAR();
    }

    /**
     * Getter to message functions cache. Compiles $msg if not contained
     * in function cache.
     *
     * @access   public
     * @return   function reference to compiled maketext message
     * @param    string   $msg maketext message
     */
    function get_msg_func ($msg)
    {
        if (! array_key_exists($msg, $this->_msg_func_cache))
            $this->set_msg_func($msg, $this->_compile($msg));
        return $this->_msg_func_cache[$msg];
    }

    /**
     * Setter to message functions cache.
     *
     * @access  public
     * @return  void
     * @param   string    $msg maketext message
     * @param   function  compiled message function
     */
    function set_msg_func ($msg, $func)
    {
        $this->_msg_func_cache[$msg] = $func;
    }

    /**
     * Convenient alias to maketext()
     *
     * @access   public
     * @returns  string processed maketext message
     * @param    mixed $args a list of arguments of which
     *                       the first argument is the
     *                       maketext message and the rest
     *                       are arguments used to process
     *                       that message.
     * @see      maketext
     */
    function _ ()
    {
        $args = func_get_args();
        return $this->maketext($args);
    }

    /**
     * process a maktext message with it's arguments
     *
     * <code>
     *
     *    // generates "2 cats sat on the mat"
     *    //
     *    $num_cats = 2;
     *    $num_mats = 1;
     *
     *    $mt->maketext(
     *       '[quant,_1,cat,cats] sat on the [numerate,_2,mat,mats].',
     *       $num_cats, $num_mats);
     *
     * </code>
     *
     * @access   public
     * @returns  mixed processed maketext message or PHP_Error
     * @param    mixed $args a list of arguments of which the first
     *                       argument is the maketext message and the rest
     *                       are arguments used to process that message.
     */
    function maketext ()
    {
        $args = func_get_args();
        if (!$args) return '';

        # argument list may have been given as
        # an array, for example when using _()
        #
        if (is_array($args[0])) $args = $args[0];

        # split msgid from msg parameters
        #
        $msgid  = $args[0];
        $params = array_slice($args,1);

        # fetch message from dictionary
        #
        $msg = $this->fetch_msg($msgid);

        # If we got a PEAR_Error, return it
        #
        if ($this->isError($msg)) {
            return $msg;
        }

        # get message function, may _compile()
        #
        $func = $this->get_msg_func($msg);

        # If we got a PEAR_Error, return it
        #
        if ($this->isError($func)) {
            return $func;
        }

        # process message function
        #
        return $func($this, $params);
    }

    /**
     * Fetch message from dictionary.
     *
     * Locale_Maketext, as a base class, not bound to any dictionaries. So
     * this function simply returns the input parameter.
     *
     * However, classes that extend from Locale_Maketext may well have
     * access to dictionaries. In such classes, this would be the place
     * where a msgid will be fetched from the dictionary, which could be a
     * gettext lookup in a PO file or a Database query. See {@link
     * Locale_Maketext_Gettext::fetch_msg()}
     *
     * @access public
     * @param  $msgid   string  message id for message lookup
     * @return $msgid   string  
     *
     */
    function fetch_msg($msgid)
    {
        return $msgid;
    }

    # --------------------------------------------------------------------
    # Maketext functions
    # --------------------------------------------------------------------

    /**
     * message processing function. Prepends quantifier to
     * chosen argument.
     *
     * @access private
     */
    function quant ($args)
    {
        $num   = $args[0];
        $forms = array_slice($args,1);

        if (!count($forms)) return $num;    # what should this mean?
        if (count($forms) > 2 && $num == 0)  # special zeroth case.
            return $forms[2];

        # Normal case
        #
        return $num . ' ' . $this->numerate($args);
    }

    /**
     * message processing function. Does not prepend quantifier to
     * chosen argument.
     *
     * @access private
     */
    function numerate ($args)
    {
        $num   = $args[0];
        $forms = array_slice($args,1);

        if (!count($forms)) return '';  # what should this mean?

        if (count($forms) == 1) {  # only headword form specified
            return ($num == 1) ? $forms[0] : $forms[0].'s'; # very cheap hack
        } else {  # singular and plural were specified
            return ($num == 1) ? $forms[0] : $forms [1];
        }
    }

    /**
     * message processing function. Uses sprintf to embed quantifier in
     * chosen argument.
     *
     * @access private
     */
    function quantf ($args)
    {
        $num   = $args[0];
        $forms = array_slice($args,1);

        if (!count($forms)) return ''; # what should this mean?
        if (count($forms) > 2 && $num == 0)  # special zeroth case.
            return $forms[2];

        if ($num == 1) {
            return sprintf($forms[0], $num);
        } else {
            return sprintf($forms[1], $num);
        }
    }

    # --------------------------------------------------------------------
    # Private methods
    # --------------------------------------------------------------------

    /**
     * compiles a Maketext message to a PHP function, via PHPs
     * create_function()
     *
     * @access private
     */
    function _compile ($msg)
    {
        preg_match_all('/[^\~\[\]]+|~.|\[|\]|~|$/', $msg, $matches);

        $in_group = 0;
        $code  = array('return \'\'');
        $chunk = '';

        foreach ($matches[0] as $m) {
            if ($m == '' || $m == '[') {
                if ($in_group) {
                    if ($m == '') {
                        return $this->_die_pointing(
                            $msg, $chunk, "Unterminated bracket group");
                    } else {
                        return $this->_die_pointing(
                            $msg, $chunk, "You can't nest bracket group");
                    }
                } else {
                    if ($m == '') {
                        # End of Message
                    } else {
                        $in_group = 1;
                    }
                    # Add preceding literal to code, if any
                    #
                    if ($chunk) {
                        $code[] = ".'".$chunk."'";
                        $chunk = '';
                    }
                }

            } elseif ($m == ']') {
                if ($in_group) {
                    $in_group = 0;

                    # Obtain method and args
                    #
                    $cmatches = preg_split ('/(?<!~),/', $chunk);

                    if ($cmatches[0] || count($cmatches) > 1) {
                        $method = $cmatches[0];
                        $params = array_slice($cmatches,1);

                        # Special case, treat [_1,..] as [,_1,...]
                        #
                        if (preg_match('/^_\d+/', $method)) {
                            array_unshift($params, $method);
                            $method = '';
                        } elseif ($method == '*') {
                            $method = 'quant';
                        }
                        # Not in PHP's Locale_Maketext. Should use I18N package
                        # from PEAR? Hmm... maybe *I* should require I18N
                        # package?
                        #
                        # elseif ($method == '#') {
                        #     $method = 'numf';
                        # }

                        # Start code for parameter concatenation or
                        # function call with parameters
                        #
                        if ($method == '') {
                            $code[] = '.implode(array(';
                        } else {
                            if (method_exists($this, $method)) {
                                $code[] = '.$locale->'.$method.'(array(';
                            } else {
                                return $this->_die_pointing(
                                    $msg, $chunk, 'Method "'.$method.
                                    '()" not found in class "'.
                                    get_class($this).'"');
                            }
                        }

                        foreach ($params as $param) {
                            # Now unescape escaped commas
                            #
                            $param = preg_replace('/~,/', ',', $param);

                            # Add parameter to function call
                            # TODO: *_ meaning all message parameters
                            if (preg_match('/^_(\d+)$/', $param, $pmatch))
                            {
                                $code[] = "\t".'$args['.--$pmatch[1].'],';
                            } else {
                                $code[] = "\t\"$param\",";
                            }
                        }

                        $code[] = '))';
                    }
                    $chunk = '';
                } else {
                    return $this->_die_pointing($msg, $chunk, "Unbalanced ']'");
                }
            } elseif (substr($m,0,1) != '~') {
                # it's stuff not containing "~" or "[" or "]"
                # i.e., a literal blob
                $chunk .= $m;
            } elseif ($m == '~~') {
                $chunk .= '~';
            } elseif ($m == '~[') {
                $chunk.='[';
            } elseif ($m == '~]') {
                $chunk.=']';
            } elseif ($m == '~,') {
                $chunk .= '~,';
            } elseif ($m == '~') {
                # possible only at msg end
                $chunk .= '~';
            } else {
                # It's a "~X" where X is not a special character.
                # Consider it a literal ~ and X
                $chunk .= $m;
            }
        }
        $code[] = ';';
        //$func_code = implode($code, "\n");
        //echo "FUNCTION:\n$func_code\n";        
        return create_function('&$locale, $args', implode($code, "\n"));        
    }
	
    /**
     * raises a PEAR_ERROR_DIE PEAR_Error about a Syntax Error in a
     * Maketext message.  Points roughly to the part of the string where
     * the error occured.
     *
     * @access private
     */
    function _die_pointing($msg, $chunk, $error)
    {
        return $this->raiseError(
            "Locale_Maketext Syntax Error: $error in ".
            "message \"$msg\" near \"$chunk\"\n", 
            LOCALE_MAKETEXT_ERROR_COMPILE,
            PEAR_ERROR_DIE);
    }

#    function _die_pointing($msg, $chunk, $error)
#    {
#        die ("Locale_Maketext Syntax Error: ".
#             "$error in message \"$msg\" near \"$chunk\"\n");
#    }
}

?>
