<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                   |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Davey Shafik <davey@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * XML_FOAF Common Methods
 * @package XML_FOAF
 * @category XML
 */

/**
 * XML_FOAF Common Methods
 *
 * @package XML_FOAF
 * @author Davey <davey@php.net>
 * @version 0.1
 * @copyright Copyright 2003 Davey Shafik and Synaptic Media. All Rights Reserved.
 */

class XML_FOAF_Common {

    /**
     * Check if a property is allows for the current foaf:Agent
     *
     * @param string $property name of the Property to check. Without a namespace
     * @access public
     * @return boolean
     */

    function isAllowedForAgent($property)
    {
        $property = strtolower($property);
        $common = array (
                    'name',
                    'maker',
                    'depiction',
                    'fundedby',
                    'logo',
                    'page',
                    'theme',
                    'dnachecksum',
                    'title',
                    'nick',
                    'givenname',
                    'phone',
                    'mbox',
                    'mbox_sha1sum',
                    'gender',
                    'jabberid',
                    'aimchatid',
                    'icqchatid',
                    'yahoochatid',
                    'msnchatid',
                    'homepage',
                    'weblog',
                    'made',
                    'holdsaccount');
        $person = array (
                    'geekcode',
                    'interest',
                    'firstname',
                    'surname',
                    'family_name',
                    'plan',
                    'img',
                    'myersbriggs',
                    'workplacehomepage',
                    'workinfohomepage',
                    'schoolhomepage',
                    'knows',
                    'publications',
                    'currentproject',
                    'pastproject',
                    'based_near');
        $organization = array();
        $group = array(
                    'member',
                    'membershipclass');
        if (in_array($property,$common) || in_array($property, ${$this->agent})) {
            return true;
        } else {
            return false;
        }
    }

}