<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML_FOAF
 *
 * XML FOAF package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2003-2008 Davey Shafik and Synaptic Media.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The name of the author may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  XML
 * @package   XML_FOAF
 * @author    Davey Shafik <davey@php.net>
 * @copyright 2003-2008 Davey Shafik and Synaptic Media.
 * @license   http://opensource.org/licenses/bsd-license New BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/XML_FOAF
 */

/**
 * @uses XML_FOAF_Common
 */
require_once 'XML/FOAF/Common.php';

/**
 * FOAF Creator
 *
 * @category  XML
 * @package   XML_FOAF
 * @author    Davey Shafik <davey@php.net>
 * @copyright 2003-2008 Davey Shafik and Synaptic Media.
 * @license   http://opensource.org/licenses/bsd-license New BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/XML_FOAF
 * @example   docs/examples/example1.php Basic Usage of XML_FOAF
 * @todo      Implement PEAR_Error handling
 */
class XML_FOAF extends XML_FOAF_Common
{

    /**
     * @var object XML_Tree object containing the FOAF RDF/XML Tree
     */
    var $foaf = null;

    /**
     * @var array Contains all namespaces in use
     */
    var $namespaces = array();

    /**
     * @var array Contains XML_Tree Child nodes for all FOAF elements
     */
    var $children = array();

    /**
     * @var object XML_Tree object for the FOAF
     */
    var $xml_tree = null;

    /**
     * XML_FOAF Constructor
     *
     * @param XML_FOAF_Parser $xml_foaf_parser_object a parser object
     *
     * @access private
     * @todo   PEAR CS - should require_once be include_once?
     */
    function __construct ($xml_foaf_parser_object = null)
    {
        if (!is_null($xml_foaf_parser_object)) {
            if (is_a($xml_foaf_parser_object, 'xml_foaf_parser')) {
                $regex[] = '/<([a-zA-Z0-9_]+:)?RDF .*?>/';
                $regex[] = '/<\/([a-zA-Z0-9_]+:)?RDF>/';
                $foaf    = preg_replace($regex, 
                    '', $xml_foaf_parser_object->foaf_xml);
                require_once 'XML/Tree.php';
                $this->xml_tree = new XML_Tree;
                $this->foaf     =& $this->xml_tree->getTreeFromString($foaf);
            }
        }
        $this->_setXmlns();
    }

    /**
     * Create new FOAF Agent
     *
     * @param string $agent_type Agent type, this can be Person, 
     *                           Organization, Group, Agent.
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_Organization
     *       FOAF Specification foaf:Organization
     * @link http://xmlns.com/foaf/0.1/#term_Group
     *       FOAF Specification foaf:Group
     * @link http://xmlns.com/foaf/0.1/#term_Person
     *       FOAF Specification foaf:Person
     * @link http://xmlns.com/foaf/0.1/#term_Agent
     *       FOAF Specification foaf:Agent
     * @foafstatus Unstable
     * @todo PEAR CS - should require_once be include_once?
     */
    function newAgent($agent_type = 'Person')
    {
        require_once 'XML/Tree.php';
        $this->xml_tree = new XML_Tree;
        $agent_type     = strtolower($agent_type);
        $this->agent    = $agent_type;
        switch ($agent_type) {
        case 'group':
            $this->foaf =& $this->xml_tree->addRoot('foaf:Group');
            break;
        case 'organization':
            $this->foaf =& $this->xml_tree->addRoot('foaf:Organization');
            break;
        case 'agent':
            $this->foaf =& $this->xml_tree->addRoot('foaf:Agent');
        case 'person':
        default:
            $this->foaf =& $this->xml_tree->addRoot('foaf:Person');
            break;
        }
    }

    /**
     * Set the foaf:name of the Agent
     *
     * @param string $name Name for the Agent.
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_name
     *       FOAF Specification foaf:name
     * @foafstatus Testing
     * @todo Allow for the xml:lang to be specified
     */
    function setName($name)
    {
        $this->children['name'] =& $this->foaf->addChild('foaf:name', $name);
    }

    /**
     * Add a foaf:depiction element
     *
     * @param string $uri URI For the Depicted image
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_depiction
     *       FOAF Specification foaf:depiction
     * @foafstatus Testing
     */
    function addDepiction($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['depiction'][] =& $this->foaf->addChild('foaf:depiction', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:fundedBy element
     *
     * @param string $uri URI for the funder
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_fundedBy
     *       FOAF Specification foaf:fundedBy
     * @foafstatus Unstable
     */
    function addFundedBy($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['fundedby'][] =& $this->foaf->addChild('foaf:fundedBy', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:logo element
     *
     * @param string $uri URI for Logo Image
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_logo
     *       FOAF Specification foaf:logo
     * @foafstatus Testing
     */
    function addLogo($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['logo'][] =& $this->foaf->addChild('foaf:logo', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:page element
     *
     * @param string $document_uri URI for the Document being reference
     * @param string $title        Title for the Document
     * @param string $description  Description for the Document
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_page
     *       FOAF Specification foaf:page
     * @foafstatus Testing
     */
    function addPage($document_uri,$title = null,$description = null)
    {
        $page     =& $this->foaf->addChild('foaf:page');
        $document =& $page->addChild('foaf:Document', 
            '', array('rdf:about' => $document_uri));
        if (!is_null($title)) {
            $document->addChild('dc:title', $title);
        }
        if (!is_null($description)) {
            $document->addChild('dc:description', $description);
        }
        $this->children['page'][] =& $page;
    }

    /**
     * Add a foaf:theme element
     *
     * @param string $uri URI for the Theme
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_theme
     *       FOAF Specification foaf:theme
     * @foafstatus unstable
     */
    function addTheme($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['theme'][] =& $this->foaf->addChild('foaf:theme', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * set foaf:title
     *
     * @param string $title foaf:Agents title
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_title
     *       FOAF Specification foaf:title
     * @foafstatus testing
     */
    function setTitle($title)
    {
        $this->children['title'] =& $this->foaf->addChild('foaf:title', $title);
    }

    /**
     * Add a foaf:nick element
     *
     * @param string $nick foaf:Agents Nickname
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_nick
     *       FOAF Specification foaf:nick
     * @foafstatus testing
     */
    function addNick($nick)
    {
        $this->children['nick'][] =& $this->foaf->addChild('foaf:nick', $nick);
    }

    /**
     * set foaf:givenname
     *
     * @param string $given_name foaf:Agents Given Name
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_givenname
     *       FOAF Specification foaf:givenname
     * @foafstatus testing
     */
    function setGivenName($given_name)
    {
        $this->children['givenname'] =& $this->foaf->addChild('foaf:givenname', 
            $given_name);
    }

    /**
     * Add a foaf:phone element
     *
     * @param string $phone foaf:Agents Phone Number
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_phone
     *       FOAF Specification foaf:phone
     * @foafstatus testing
     */
    function addPhone($phone)
    {
        if (substr($phone, 0, 4) != 'tel:') {
            $phone = 'tel:' .$phone;
        }
        $this->children['phone'][] =& $this->foaf->addChild('foaf:phone', 
            '', array('rdf:resource' => $phone));
    }

    /**
     * Add a foaf:mbox or foaf:mbox_sha1sum element
     *
     * @param string  $mbox         Mailbox, either a mailto:addr, 
     *                              addr or an sha1 sum of mailto:addr
     * @param boolean $sha1         Whether or not to use foaf:mbox_sha1sum
     * @param boolean $is_sha1_hash Whether or not given $mbox is already an sha1 sum
     *
     * @return void
     * @access public
     * @see XML_FOAF::setMboxSha1Sum
     * @link http://xmlns.com/foaf/0.1/#term_mbox_sha1sum
     *       FOAF Specification foaf:mbox_sha1sum
     * @link http://xmlns.com/foaf/0.1/#term_mbox
     *       FOAF Specification foaf:mbox
     * @foafstatus testing
     */
    function addMbox($mbox,$sha1 = false,$is_sha1_hash = false)
    {
        if (substr($mbox, 0, 7) != 'mailto:' 
            && $is_sha1_hash == false
        ) {
            $mbox = 'mailto:' .$mbox;
        }

        if ($sha1 == true) {
            if ($is_sha1_hash == false) {
                $mbox = sha1($mbox);
            }
            $this->children['mbox_sha1sum'][] =& 
                $this->foaf->addChild('foaf:mbox_sha1sum', $mbox);
        } else {
            $this->children['mbox'][] =& 
                $this->foaf->addChild('foaf:mbox', 
                    '', array('rdf:resource' => $mbox));
        }

    }

    /**
     * Add a foaf:mbox_sha1sum element
     *
     * @param string  $mbox        Mailbox, either a mailto:addr, 
     *                             addr or an sha1 sum of mailto:addr
     * @param boolean $is_sha1_sum Whether or not given $mbox is already an sha1 sum
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_mbox_sha1sum
     *       FOAF Specification foaf:mbox_sha1sum
     * @foafstatus testing
     */
    function addMboxSha1Sum($mbox,$is_sha1_sum = false)
    {
        $this->addMbox($mbox, true, $is_sha1_sum);
    }

    /**
     * set foaf:gender
     *
     * @param string $gender foaf:Agents Gender (typically 'male' or 'female')
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_gender
     *       FOAF Specification foaf:gender
     * @foafstatus testing
     */
    function setGender($gender)
    {
        $this->children['gender'] =& 
            $this->foaf->addChild('foaf:gender', strtolower($gender));
    }

    /**
     * Add a foaf:jabberID element
     *
     * @param string $jabber_id A Jabber ID
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_jabberID
     *       FOAF Specification foaf:jabberID
     * @foafstatus testing
     */
    function addJabberID($jabber_id)
    {
        $this->children['jabbberid'][] =& 
            $this->foaf->addChild('foaf:jabberID', $jabber_id);
    }

    /**
     * Add a foaf:aimChatID element
     *
     * @param string $aim_chat_id An AIM Username
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_aimChatID
     *       FOAF Specification foaf:aimChatID
     * @foafstatus testing
     */
    function addAimChatID($aim_chat_id)
    {
        $this->children['aimchatid'][] =& 
            $this->foaf->addChild('foaf:aimChatID', $aim_chat_id);
    }

    /**
     * Add a foaf:icqChatID element
     *
     * @param string $icq_chat_id An ICQ Number
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_icqChatID
     *       FOAF Specification foaf:icqChatID
     * @foafstatus testing
     */
    function addIcqChatID($icq_chat_id)
    {
        $this->children['icqchatid'][] =& 
            $this->foaf->addChild('foaf:icqChatID', $icq_chat_id);
    }

    /**
     * Add a foaf:yahooChatID element
     *
     * @param string $yahoo_chat_id A Yahoo! Messenger ID
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_yahooChatID
     *       FOAF Specification foaf:yahooChatID
     * @foafstatus testing
     */
    function addYahooChatID($yahoo_chat_id)
    {
        $this->children['yahoochatid'][] =& 
            $this->foaf->addChild('foaf:yahooChatID', $yahoo_chat_id);
    }

    /**
     * Add a foaf:msnChatID element
     *
     * @param string $msn_chat_id A MSN Chat username
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_msnChatID
     *       FOAF Specification foaf:msnChatID
     * @foafstatus testing
     */
    function addMsnChatID($msn_chat_id)
    {
        $this->children['msnchatid'][] =&  
            $this->foaf->addChild('foaf:msnChatID', $msn_chat_id);
    }

    /**
     * Add a foaf:OnlineAccount element
     *
     * @param string $account_name             Account Name
     * @param string $account_service_homepage URI to Account Service Homepage
     * @param string $account_type             Account type (e.g 
     *                                         http://xmlns.com/foaf/0.1/OnlineChatAccount)
     *
     * @return void
     * @access public
     * @see XML_FOAF::setOnlineChatAccount, XML_FOAF::setMsnChatID,
     *      XML_FOAF::setIcqChatID, XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID, XML_FOAF::setJabberID,
     *      XML_FOAF::setOnlineGamingAccount, XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage
     *       FOAF Specification foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName
     *       FOAF Specification foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineAccount
     *       FOAF Specification foaf:OnlineAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount
     *       FOAF Specification foaf:holdsAccount
     * @foafstatus unstable
     * @todo PEAR CS - can't obey 85-char limit on @param $account_type
     * @todo PEAR CS - can't obey 85-char limit in function signature
     */
    function addOnlineAccount($account_name, $account_service_homepage = null, $account_type = null)
    {
        $holds_account  =& $this->foaf->addChild('foaf:holdsAccount');
        $online_account =& $holds_account->addChild('foaf:OnlineAccount');
        $online_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_account->addChild('foaf:accountServiceHomepage', 
                '', array('rdf:resource' => $account_service_homepage));
        }
        if (!is_null($account_type)) {
            $online_account->addChild('rdf:type', 
                '', array('rdf:resource' => $account_type));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineChatAccount element
     *
     * @param string $account_name             Account Name
     * @param string $account_service_homepage URI Tto Account Service Homepage
     *
     * @return void
     * @access public
     * @see XML_FOAF::setOnlineAccount, XML_FOAF::setMsnChatID,
     *      XML_FOAF::setIcqChatID, XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID, XML_FOAF::setJabberID,
     *      XML_FOAF::setOnlineGamingAccount,XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage
     *       FOAF Specification foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName
     *       FOAF Specification foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount
     *       FOAF Specification foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount
     *       FOAF Specification foaf:holdsAccount
     * @foafstatus unstable
     */
    function addOnlineChatAccount($account_name,$account_service_homepage)
    {
        $holds_account       =& $this->foaf->addChild('foaf:holdsAccount');
        $online_chat_account =& $holds_account->addChild('foaf:OnlineChatAccount');
        $online_chat_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_chat_account->addChild('foaf:accountServiceHomepage', 
                '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineGamingAccount element
     *
     * @param string $account_name             Account Name
     * @param string $account_service_homepage URI to Account Service Homepage
     *
     * @return void
     * @access public
     * @see XML_FOAF::setOnlineAccount, XML_FOAF::setMsnChatID,
     *      XML_FOAF::setIcqChatID, XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID, XML_FOAF::setJabberID,
     *      XML_FOAF::setOnlineChatAccount, XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage
     *       FOAF Specification foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName
     *       FOAF Specification foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount
     *       FOAF Specification foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount
     *       FOAF Specification foaf:holdsAccount
     * @foafstatus unstable
     */
    function addOnlineGamingAccount($account_name,$account_service_homepage)
    {
        $holds_account         =& $this->foaf->addChild('foaf:holdsAccount');
        $online_gaming_account =& 
            $holds_account->addChild('foaf:OnlineGamingAccount');
        $online_gaming_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_gaming_account->addChild('foaf:accountServiceHomepage', 
                '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineEcommerceAccount element
     *
     * @param string $account_name             Account Name
     * @param string $account_service_homepage URI to Account Service Homepage
     *
     * @return void
     * @access public
     * @see XML_FOAF::setOnlineAccount, XML_FOAF::setMsnChatID,
     *      XML_FOAF::setIcqChatID, XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID, XML_FOAF::setJabberID,
     *      XML_FOAF::setOnlineChatAccount, XML_FOAF::setOnlineGamingAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage
     *       FOAF Specification foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName
     *       FOAF Specification foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount
     *       FOAF Specification foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount
     *       FOAF Specification foaf:holdsAccount
     * @foafstatus unstable
     */
    function addOnlineEcommerceAccount($account_name,$account_service_homepage)
    {
        $holds_account            =& $this->foaf->addChild('foaf:holdsAccount');
        $online_ecommerce_account =& 
            $holds_account->addChild('foaf:OnlineEcommerceAccount');
        $online_ecommerce_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_ecommerce_account->addChild('foaf:accountServiceHomepage', 
                '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:homepage element
     *
     * @param string $uri URI for the Homepage
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_homepage
     *       FOAF Specification foaf:homepage
     * @foafstatus stable
     */
    function addHomepage($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['homepage'][] =& $this->foaf->addChild('foaf:homepage', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:weblog element
     *
     * @param string $uri URI for the weblog
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_weblog
     *       FOAF Specification foaf:weblog
     * @foafstatus testing
     */
    function addWeblog($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['weblog'][] =& $this->foaf->addChild('foaf:weblog', 
            '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:made element
     *
     * @param string $uri URI for the thing foaf:Agent made
     *
     * @return void
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_made
     * @foafstatus testing
     */
    function addMade($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['made'][] =& $this->foaf->addChild('foaf:made', 
            '', array('rdf:resource' => $uri));
    }

    /** start of Agent-only methods */
    /**#@+
     * @todo Return a PEAR_Error instead of false
     */

    /* foaf:Person */

    /**
     * set foaf:geekcode
     *
     * @param string $geek_code foaf:Agents Geek Code
     *
     * @return boolean
     * @access public
     * @link http://www.joereiss.net/geek/geek.html Geek Code Generator
     * @link http://www.geekcode.com/geek.html Geek Code official website
     * @link http://xmlns.com/foaf/0.1/#term_geekcode
     *       FOAF Specification foaf:geekcode
     * @foafstatus testing
     */
    function setGeekcode($geek_code)
    {
        if ($this->isAllowedForAgent('geekcode')) {
            $this->children['geekcode'] =& 
                $this->foaf->addChild('foaf:geekcode', $geek_code);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:firstName
     *
     * @param string $first_name foaf:Agents First Name
     *
     * @return boolean
     * @access public
     * @see XML_FOAF::setGivenName,XML_FOAF::setName
     * @link http://xmlns.com/foaf/0.1/#term_firstName
     *       FOAF Specification foaf:firstName
     * @foafstatus testing
     */
    function setFirstName($first_name)
    {
        if ($this->isAllowedForAgent('firstname')) {
            $this->children['firstname'] =& 
                $this->foaf->addChild('foaf:firstName', $first_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:surname
     *
     * @param string $surname foaf:Agents Surname
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_surname
     *       FOAF Specification foaf:surname
     * @foafstatus testing
     */
    function setSurname($surname)
    {
        if ($this->isAllowedForAgent('surname')) {
            $this->children['surname'] =& 
                $this->foaf->addChild('foaf:surname', $surname);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:familyName
     *
     * @param string $family_name foaf:Agents Family name
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_firstName
     *       FOAF Specification foaf:familyName
     * @foafstatus testing
     */
    function setFamilyName($family_name)
    {
        if ($this->isAllowedForAgent('family_name')) {
            $this->children['familyname'] =& 
                $this->foaf->addChild('foaf:family_name', $family_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:plan
     *
     * @param string $plan .plan file contents
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_plan
     *       FOAF Specification foaf:plan
     * @foafstatus testing
     */
    function setPlan($plan)
    {
        if ($this->isAllowedForAgent('plan')) {
            $this->children['plan'] =& $this->foaf->addChild('foaf:plan', $plan);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:img element
     *
     * @param string $uri URI for the img being depicted
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_img
     *       FOAF Specification foaf:img
     * @foafstatus testing
     */
    function addImg($uri)
    {
        if ($this->isAllowedForAgent('img')) {
            $uri                     = $this->_resolveURI($uri);
            $this->children['img'][] =& $this->foaf->addChild('foaf:img', 
                '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:myersBriggs elements
     *
     * @param string $myers_briggs Myers Briggs Personality classification
     *
     * @return boolean
     * @access public
     * @link http://www.teamtechnology.co.uk/tt/t-articl/mb-simpl.htm
     *       Myers Briggs - Working out your type
     * @link http://xmlns.com/foaf/0.1/#term_myersBriggs
     *       FOAF Specification foaf:myersBriggs
     * @foafstatus testing
     */
    function addMyersBriggs($myers_briggs)
    {
        if ($this->isAllowedForAgent('myersbriggs')) {
            $uri                             = $this->_resolveURI($uri);
            $this->children['myersbriggs'][] =& 
                $this->foaf->addChild('foaf:myersBriggs', $myers_briggs);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:workplaceHome element
     *
     * @param string $uri URI for the Workplace Homepage
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_workplaceHomepage
     *       FOAF Specification foaf:workplaceHomepage
     * @foafstatus testing
     */
    function addWorkplaceHomepage($uri)
    {
        if ($this->isAllowedForAgent('workplaceHomepage')) {
            $uri                                   = $this->_resolveURI($uri);
            $this->children['workplacehomepage'][] =& 
                $this->foaf->addChild('foaf:workplaceHomepage', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:workInfoHomepage element
     *
     * @param string $uri URI for Work Information Homepage
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_workInfoHomepage
     *       FOAF Specification foaf:workInfoHomepage
     * @foafstatus testing
     */
    function addWorkInfoHomepage($uri)
    {
        if ($this->isAllowedForAgent('workInfoHomepage')) {
            $uri                                  = $this->_resolveURI($uri);
            $this->children['workinfohomepage'][] =& 
                $this->foaf->addChild('foaf:workInfoHomepage', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:schoolHomepage element
     *
     * @param string $uri URI for School Homepage
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_schoolHomepage
     *       FOAF Specification foaf:schoolHomepage
     * @foafstatus testing
     */
    function addSchoolHomepage($uri)
    {
        if ($this->isAllowedForAgent('schoolHomepage')) {
            $uri                               = $this->_resolveURI($uri);
            $this->childen['schoolhomepage'][] = 
                $this->foaf->addChild('foaf:schoolHomepage', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:publications elements
     *
     * @param string $uri URI to the publications
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_publications
     *       FOAF Specification foaf:publications
     * @foafstatus unstable
     */
    function addPublications($uri)
    {
        if ($this->isAllowedForAgent('publications')) {
            $uri                              = $this->_resolveURI($uri);
            $this->children['publications'][] =& 
                $this->foaf->addChild('foaf:publications', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:currentProject element
     *
     * @param string $uri URI to a current projects homepage
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_currentProject
     *       FOAF Specification foaf:currentProject
     * @foafstatus testing
     */
    function addCurrentProject($uri)
    {
        if ($this->isAllowedForAgent('currentProject')) {
            $uri                                = $this->_resolveURI($uri);
            $this->children['currentproject'][] =& 
                $this->foaf->addChild('foaf:currentProject', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:pastProject element
     *
     * @param string $uri URI to a past projects homepage
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_pastProject
     *       FOAF Specification foaf:pastProject
     * @foafstatus testing
     */
    function addPastProject($uri)
    {
        if ($this->isAllowedForAgent('pastProject')) {
            $uri                             = $this->_resolveURI($uri);
            $this->children['pastproject'][] =& 
                $this->foaf->addChild('foaf:pastProject', 
                    '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:basedNear
     *
     * @param float $geo_lat  Latitute for the geo:Point
     * @param float $geo_long Longitude for the geo:Point
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_based_near
     *       FOAF Specification foaf:basedNear
     * @link http://www.w3.org/2003/01/geo/
     *       An RDF Geo Vocabulary: Point/lat/long/alt
     * @link http://esw.w3.org/topic/GeoInfo GeoInfo Wiki
     * @link http://rdfweb.org/topic/UsingBasedNear Using foaf:based_near
     * @foafstatus unstable
     */
    function setBasedNear($geo_lat,$geo_long)
    {
        if ($this->isAllowedForAgent('based_near')) {
            $this->namespaces['geo'] = 'http://www.w3.org/2003/01/geo/wgs84_pos#';
            $based_near              =& $this->foaf->addChild('foaf:based_near');
            $geo_point               =& $based_near->addChild('geo:Point', 
                '', array('geo:lat' => $geo_lat, 'geo:long' => $geo_long));

            $this->children['basednear'][] =& $based_near;
            return true;
        } else {
            return false;
        }
    }

    /* foaf:Person && foaf:Group */

    /**
     * Add a foaf:interest element
     *
     * @param string $uri URI with Info about the Interest
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_interest
     * @foafstatus testing
     */
    function addInterest($uri)
    {
        if ($this->isAllowedForAgent('interest')) {
            $uri                          = $this->_resolveURI($uri);
            $this->children['interest'][] =& 
                $this->foaf->addChild('foaf:interest', 
                    '', array('rdf:resource' => $uri));
        } else {
            return false;
        }
    }

    /* foaf:Group */

    /**
     * Add a foaf:member element
     *
     * @param object &$foaf_agent XML_FOAF object (with a foaf:agent set)
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_member
     *       FOAF Specification foaf:member
     * @foafstatus unstable
     */
    function &addMember(&$foaf_agent)
    {
        if ($this->isAllowedForAgent('member')) {
            $member =& $this->foaf->addChild('foaf:member');
            $member->addChild($foaf_agent);
            $this->children['member'][] =& $member;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set foaf:membershipClass
     *
     * @param mixed &$membership_class XML String or XML_Tree/XML_Tree_Node object
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_membershipClass
     *       FOAF Specification foaf:membershipClass
     * @foafstatus unstable
     */
    function setMembershipClass(&$membership_class)
    {
        if ($this->isAllowedForAgent('membershipClass')) {
            if (is_string($membership_class)) {
                $membership_tree = new XML_Tree;
                $membership_tree->getTreeFromString($membership_class);
                $this->children['membershipclass'] =& 
                    $this->foaf->addChild($membership_tree);
            } else {
                $this->children['membershipclass'] =& 
                    $this->foaf->addChild($membership_class);
            }
            return true;
        } else {
            return false;
        }
    }

    /**#@-*/
    /* end of Agent only methods */

    /**
     * set rdfs:seeAlso
     *
     * @param string $uri URI for the resource
     *
     * @return boolean
     * @access public
     * @link http://www.w3.org/TR/rdf-schema/#ch_seealso
     *       RDF Schema Specification - rdfs:seeAlso
     */
    function addSeeAlso($uri)
    {
        $uri = $this->_resolveURI($uri);

        $this->children['seealso'][] =& 
            $this->foaf->addChild('rdfs:seeAlso', '', array('rdf:resource' => $uri));
    }

    /**
     * set a foaf:knows
     *
     * @param object &$foaf_agent XML_FOAF Object for the foaf:knows Agent
     *
     * @return boolean
     * @access public
     * @link http://xmlns.com/foaf/0.1/#term_knows
     *       FOAF Specification foaf:knows
     * @foafstatus testing
     */
    function &addKnows(&$foaf_agent)
    {
        $this->knows =& $this->foaf->addChild('foaf:knows');
        $this->knows->addChild($foaf_agent->foaf);
        return true;
    }

    /**
     * Add an XML_Tree, XML_Tree_Node object or XML String to the FOAF
     *
     * @param mixed &$xml_tree XML_Tree, XML_Tree_Node or XML String
     *
     * @return boolean
     * @access public
     */
    function addChild(&$xml_tree)
    {
        if (is_array($xml_tree)) {
            if (is_string($xml_tree['xml'])) {
                $tree = new XML_Tree;
                $tree->getTreeFromString($xml_tree['xml']);
                $xml_tree['child']->addChild($tree);
            } else {
                $xml_tree['child']->addChild($xml_tree['xml']);
            }
        } else {
            if (is_string($xml_tree)) {
                $tree = new XML_Tree;
                $tree->getTreeFromString($xml_tree);
                $this->foaf->addChild($tree);
            } else {
                $this->foaf->addChild($xml_tree);
            }
        }
    }

    /**
     * Echo the FOAF RDF/XML tree
     *
     * @param boolean $without_rdf Ouput RDF/XML inside <rdf:RDF> root elements
     *
     * @return boolean
     * @access public
     */
    function dump($without_rdf = false)
    {
        echo $this->get($without_rdf);
        return true;
    }

    /**
     * Return the FOAF RDF/XML tree
     *
     * @param boolean $without_rdf Return RDF/XML inside <rdf:RDF> root element
     *
     * @return string
     * @access public
     */
    function toXML($without_rdf = false)
    {
        if ($without_rdf == false) {
            $foaf = "<rdf:RDF" . $this->_getXmlns() 
                . ">\n" . $this->foaf->get() . "</rdf:RDF>";
        } else {
            $foaf = $this->foaf->get();
        }
        return $foaf;
    }

    /**
     * Alias for toXML
     *
     * @param boolean $without_rdf Return RDF/XML inside <rdf:RDF> root element
     *
     * @return string
     * @access public
     */
    function get($without_rdf = false)
    {
        return $this->toXML($without_rdf);
    }

    /**
     * Set an XML Namespace
     *
     * @param string $qualifier XML Namespace qualifier
     * @param string $uri       XML Namespace URI
     *
     * @return boolean
     * @access public
     */
    function addXmlns($qualifier,$uri)
    {
        $this->namespaces[$qualifier] = $uri;
    }

    /**
     * Return XML Namespaces as xml attributes
     *
     * @return string
     * @access private
     */
    function _getXmlns()
    {
        $namespaces = '';
        foreach ($this->namespaces as $qualifier => $uri) {
            $namespaces .= ' xmlns:' .$qualifier. ' = "' .$uri. '"';
        }
        return $namespaces;
    }

    /**
     * Set default XML Namespaces
     *
     * @return void
     * @access private
     */
    function _setXmlns()
    {
        $this->namespaces['foaf'] = "http://xmlns.com/foaf/0.1/";
        $this->namespaces['rdf']  = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
        $this->namespaces['rdfs'] = "http://www.w3.org/2000/01/rdf-schema#";
        $this->namespaces['dc']   = "http://purl.org/dc/elements/1.1/";
    }
    
    /**
     * Validate a URI
     *
     * @param string $uri URI to validate
     *
     * @return mixed false if invalid, full URI if not
     * @access private
     * @todo   PEAR CS - should require_once be include_once?
     */
    function _resolveURI($uri)
    {
        require_once 'Net/URL.php';
        $net_url = new Net_URL($uri);
        $net_url->setProtocol('http');
        $uri = $net_url->getURL();
        return $uri;
    }
}

/*
foaf:Person
geekcode, firstName, surname, family_name, plan, img, myersBriggs, 
workplaceHomepage, workInfoHomepage, schoolHomepage, knows, interest, 
topic_interest, publications, currentProject, pastProject, based_near, 
name, maker, depiction, fundedBy, logo, page, theme, dnaChecksum, title, 
nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, aimChatID, 
icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount

foaf:Organization
name, maker, depiction, fundedBy, logo, page, theme, dnaChecksum, title, 
nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, aimChatID, 
icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount

foaf:Group
member, membershipClass, name, maker, depiction, fundedBy, logo, page, theme, 
dnaChecksum, title, nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, 
aimChatID, icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount
*/

