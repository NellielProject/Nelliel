<?php

namespace phpDOMExtend;

use DOMXPath;

class DOMHelperFunctions
{
    function __construct()
    {
        ;
    }

    public static function doXPathQuery($node, $expression, $context_node = null, $array = false)
    {
        $node_parent = $node->ownerDocument;

        if(is_null($node_parent))
        {
            $xpath = new DOMXPath($node);
        }
        else
        {
            $xpath = new DOMXPath($node->ownerDocument);
        }

        if(is_null($context_node))
        {
            $context_node = $node;
        }

        return $xpath->query($expression, $context_node);
    }

    public static function attributeListToArray($node_list, $attribute_name)
    {
        $array = array();

        foreach ($node_list as $node)
        {
            $array[$node->getAttribute($attribute_name)] = $node;
        }

        return $array;
    }
}