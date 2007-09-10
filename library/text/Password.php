<?php
class Password {
    const ALL = 0;
    const ALPHANUMERIC = 1;
    const ALPHABETICAL = 2;
    const NUMERIC = 3;
    
    const PRONOUNCEABLE = 0;
    const UNPRONOUNCEABLE = 1;
    const MIX = 2;
    
    public static function create($length = 10, $pronounceable = 0, $type = 0)
    {
        switch($pronounceable)
        {
            case self::PRONOUNCEABLE: 
                return Password::createPronounceable($length);
            case self::UNPRONOUNCEABLE:
                return Password::createUnpronounceable($length, $chars);
            case self::MIX:
            default:
                return Password::createPronounceable($length - 1) . Password::createUnpronounceable(1, self::NUMERIC);
        }    
    }

    private static function createPronounceable($length)
    {
        $retVal = '';
        
        $vowels = array('a', 'e', 'i', 'o', 'u', 'ae', 'ou', 'io', 'ea', 'ou', 'ia', 'ai');
        $consonants = array('b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm',  'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'tr', 'cr', 'fr', 'dr', 'wr', 'pr', 'th', 'ch', 'ph', 'st', 'sl', 'cl');

        $v_count = count($vowels);
        $c_count = count($consonants);
        
        $charCount = $v_count + $c_count;

        for ($i = 0; $i < $length; $i++) 
        {
            $retVal .= $consonants[mt_rand(0, $c_count-1)] . $vowels[mt_rand(0, $v_count-1)];
        }

        return substr($retVal, 0, $length);
    }

    private static function createUnpronounceable($length, $chars)
    {
        $password = '';

         switch($chars) 
         {
             case self::ALPHANUMERIC:
                 $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                 break;
             case self::ALPHABETICAL:
                 $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                 break;
             case self::NUMERIC:
                 $chars = '0123456789';
                 break;
             case self::ALL:
             default:
                 $chars = '_#@%&ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                 break;
         }

         $charCount = strlen($chars);
                 
         for ($i = 0; $i < $length; $i++) 
         {
             $num = mt_rand(0, $charCount - 1);
             $password .= $chars{$num};
         }
         
         return $password;
    }
}
?>
