<?php
/* vim: get expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
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
 * FOAF Parser
 * @package XML_FOAF
 * @category XML
 */

require_once 'XML/FOAF/Common.php';

define('RDFAPI_INCLUDE_DIR', '../../RAP/');
define('XML_FOAF_NS','http://xmlns.com/foaf/0.1/');
define('XML_FOAF_DC_NS','http://purl.org/dc/elements/1.1/');
define('XML_FOAF_RDF_NS','http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('XML_FOAF_PERSON', 1);
define('XML_FOAF_GROUP', 2);
define('XML_FOAF_ORGANIZATION', 3);
define('XML_FOAF_AGENT', 4);

/**
 * FOAF Parser
 *
 * Individual element parsers that start with _fetch will return multiple elements
 * into the result Array, those that start with _get will return only a single element
 *
 * @package XML_FOAF
 * @author Davey <davey@php.net>
 * @version 0.1
 * @copyright Copyright 2003 Davey Shafik and Synaptic Media. All Rights Reserved.
 * @example docs/examples/example2.php Basic Usage of XML_FOAF_Parser
 * @todo Implement PEAR_Error handling
 */

class XML_FOAF_Parser extends XML_FOAF_Common
{
    /**
     * @var string Original FOAF file
     */

    var $foaf_xml = '';

    /**
     * @var array FOAF data as Array
     */

    var $foaf_data;

    /**
     * @var object MemModel of FOAF
     */

    var $foaf;

    /**
     * @var object Instance of the RAP RDF_Parser
     */

    var $rdf_parser;

    /**
     * @var array Nodes assumed to be primary foaf:Agents
     */

    var $agent_nodes;

    /**
     * @var array Nodes found in <foaf:knows>
     */

    var $known_nodes;

    /**
     * XML_FOAF_Parser Constructor
     */

    function __construct() {
        require_once RDFAPI_INCLUDE_DIR . 'RdfAPI.php';
        $this->rdf_parser =& new RdfParser;
    }

    /**
     * XML_FOAF_Parser PHP4 Compatible Constructor
     */

    function XML_FOAF_Parser()
    {
        $this->__construct();
    }

    /**
     * Parse a FOAF at the specified URI
     *
     * @param $uri string URI for a FOAF file
     * @access public
     * @return void
     */

    function parseFromURI($uri)
    {
        $this->parseFromFile($uri);
    }

    /**
     * Parse a FOAF in the specified File
     *
     * @param $file string File Path for a FOAF file
     * @param $use_include_path Whether to look for the file in the php include_path
     * @access public
     * @return void
     */

    function parseFromFile($file,$use_include_path = false)
    {
        $this->foaf = file_get_contents($file,$use_include_path);
        $this->_parse();
    }

    /**
     * Parse a FOAF contained in the specified variable
     *
     * @param $mem string Variable holding a FOAF file's XML
     * @access public
     * @return void
     */

    function parseFromMem($mem)
    {
        $this->foaf = $mem;
        $this->_parse();
    }

    /**#@+
     * @access private
     * @return void
     */

    /**
     * Calls all the seperate property parsers
     */

    function _parse()
    {
        $this->foaf =& $this->rdf_parser->generateModel($this->foaf);
        $this->foaf->writeAsHTMLTable();
        $this->foaf_data = $this->_fetchAgent();
        $this->_getName();
        $this->_fetchDepiction();
        $this->_fetchFundedBy();
        $this->_fetchLogo();
        $this->_fetchPage();
    }

    /**
     * Parses our the foaf:Agents
     *
     * Looks for all foaf:Agents (foaf:Person,foaf:Group,foaf:Organzation and foaf:Agent)
     * and decides which are the primary agents (who/what the FOAF is about) and
     * which are only known by the primary agents
     *
     * @access private
     * @return void
     */

    function _fetchAgent()
    {
        $person_resource = new Resource(XML_FOAF_NS . 'Person');
        $persons = $this->foaf->find(null,null,$person_resource);
        $group_resource = new Resource(XML_FOAF_NS . 'Group');
        $groups = $this->foaf->find(null,null,$group_resource);
        $organization_resource = new Resource(XML_FOAF_NS . 'Organization');
        $organizations = $this->foaf->find(null,null,$organization_resource);
        $agent_resource = new Resource(XML_FOAF_NS . 'Agent');
        $agents = $this->foaf->find(null,null,$agent_resource);
        $agents->addModel($persons);
        $agents->addModel($groups);
        $agents->addModel($organizations);
        $knows_resource = new Resource(XML_FOAF_NS . 'knows');
        $knows = $this->foaf->find(null,$knows_resource,null);
        $i = 0;
        $agent_nodes = array();
        $known_nodes = array();
        foreach ($agents->triples as $agent) {
            $agent_nodes[$agent->subj->uri] = $agent->obj->uri;
            $i += 1;
            foreach ($knows->triples as $know) {
                if ($agent->subj->uri == $know->obj->uri) {
                    $agent_type = pathinfo($agent->obj->uri);
                    $agent_type = $agent_type['basename'];
                    $known = array('node' => $know->obj->uri, 'agent' => $agent_type);
                    $known_nodes["{$know->subj->uri}"][] = $known;
                    $this->known_nodes[] = $know->obj->uri;
                    unset($agent_nodes[$agent->subj->uri]);
                }
            }
        }

        $agents = array();
        $i = 0;
        foreach ($agent_nodes as $node => $agent_type) {
            $agent_type = pathinfo($agent_type);
            $agent_type = $agent_type['basename'];
            $agents[$i] = array ('node' => $node, 'agent' => $agent_type);
            $this->agent_nodes[] = $node;
            if (isset($known_nodes[$node])) {
                foreach ($known_nodes[$node] as $knows) {
                    $agents[$i]['knows'][] = $knows;
                }
            }
            $i += 1;
        }
        return $agents;
    }

    /**
     * Finds the foaf:name's and inserts them into the result array
     *
     * If more than one foaf:name is found for a single foaf:Agent, the
     * last found will be the one shown in the result
     */

    function _getName()
    {
        $name_resource = new Resource(XML_FOAF_NS . 'name');
        $names = $this->foaf->find(null,$name_resource,null);
        foreach ($names->triples as $name) {
            if (in_array($name->subj->uri,$this->agent_nodes)) {
                foreach ($this->foaf_data as $key => $agent) {
                    if (isset($agent['node']) && ($agent['node'] == $name->subj->uri)) {
                        $this->foaf_data[$key]['name'] = $name->obj->label;
                        break;
                    }
                }
            } elseif (in_array($name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $knows_key => $knows) {
                        if (isset($knows['node']) && ($knows['node'] == $name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$knows_key]['name'] = $name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:depiction's and inserts them into the result Array
     */

    function _fetchDepiction()
    {
        $depictions_resource = new Resource(XML_FOAF_NS . 'depiction');
        $depictions = $this->foaf->find(null,$depictions_resource,null);
        foreach ($depictions->triples as $depiction) {
            if (in_array($depiction->subj->uri,$this->agent_nodes)) {
                foreach ($this->foaf_data as $key => $agent) {
                    if (isset($agent['node']) && ($agent['node'] == $depiction->subj->uri)) {
                        $this->foaf_data[$key]['depiction'][] = $depiction->obj->uri;
                        break;
                    }
                }
            } elseif (in_array($depiction->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $depiction_key => $depiction_array) {
                        if (isset($depiction_array['node']) && ($depiction_array['node'] == $depiction->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$depiction_key]['depiction'][] = $depiction->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:fundedBy's and inserts them into the result Array
     */

    function _fetchFundedBy()
    {
        $funded_by_resource = new Resource(XML_FOAF_NS . 'funded_by');
        $funded_bys = $this->foaf->find(null,$funded_by_resource,null);
        foreach ($funded_bys->triples as $funded_by) {
            if (in_array($funded_by->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $agent) {
                    if ($agent == $funded_by->subj->uri) {
                        $this->foaf_data[$key]['funded_by'][] = $funded_by->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($funded_by->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['funded_by'] as $funded_by_key => $funded_by_array) {
                        if (isset($funded_by_array['node']) && ($funded_by_array['node'] == $funded_by->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$funded_by_key]['funded_by'][] = $funded_by->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:logo's and inserts them into the result Array
     */

    function _fetchLogo()
    {
        $logo_resource = new Resource(XML_FOAF_NS . 'logo');
        $logos = $this->foaf->find(null,$logo_resource,null);
        foreach ($logos->triples as $logo) {
            if (in_array($logo->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $agent) {
                    if ($agent == $logo->subj->uri) {
                        $this->foaf_data[$key]['logo'][] = $logo->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($logo->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['logo'] as $logo_key => $logo_array) {
                        if (isset($logo_array['node']) && ($logo_array['node'] == $logo->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$logo_key]['logo'][] = $logo->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:page's and inserts them into the result Array
     */

    function _fetchPage()
    {
        $page_resource = new Resource(XML_FOAF_NS . 'page');
        $pages = $this->foaf->find(null,$page_resource,null);
        foreach ($pages->triples as $page) {
            if (in_array($page->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $page->subj->uri) {
                        $this->foaf_data[$key]['page'][] = $page->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($page->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['page'] as $page_key => $page_array) {
                        if (isset($page_array['node']) && ($page_array['node'] == $page->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$page_key]['page'][] = $page->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:theme's and inserts them into the result Array
     */

    function _fetchTheme()
    {
        $theme_resource = new Resource(XML_FOAF_NS . 'theme');
        $themes = $this->foaf->find(null,$theme_resource,null);
        foreach ($themes->triples as $theme) {
            if (in_array($theme->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $theme->subj->uri) {
                        $this->foaf_data[$key]['theme'][] = $theme->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($theme->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['theme'] as $theme_key => $theme_array) {
                        if (isset($theme_array['node']) && ($theme_array['node'] == $theme->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$theme_key]['theme'][] = $theme->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all the foaf:title and inserts them into the result Array
     *
     * If more than one foaf:title is found for one foaf:Agent the
     * last one found is insert into the result
     */

    function _getTitle()
    {
        $title_resource = new Resource(XML_FOAF_NS . 'title');
        $titles = $this->foaf->find(null,$title_resource,null);
        foreach ($titles->triples as $title) {
            if (in_array($title->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $title->subj->uri) {
                        $this->foaf_data[$key]['title'] = $title->obj->label;
                    }
                    break;
                }
            } elseif (in_array($title->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['title'] as $title_key => $title_array) {
                        if (isset($title_array['node']) && ($title_array['node'] == $title->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$title_key]['title'] = $title->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:nick's and inserts them into the result Array
     */

    function _fetchNick()
    {
        $nick_resource = new Resource(XML_FOAF_NS . 'nick');
        $nicks = $this->foaf->find(null,$nick_resource,null);
        foreach ($nicks->triples as $nick) {
            if (in_array($nick->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $nick->subj->uri) {
                        $this->foaf_data[$key]['nick'][] = $nick->obj->label;
                    }
                    break;
                }
            } elseif (in_array($nick->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['nick'] as $nick_key => $nick_array) {
                        if (isset($nick_array['node']) && ($nick_array['node'] == $nick->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$nick_key]['nick'][] = $nick->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:givenName's and inserts them into the result Array
     *
     * If more than one foaf:givenName is found for a single foaf:Agent, the
     * last one found is inserted into the result array
     */

    function _getGivenName()
    {
        $given_name_resource = new Resource(XML_FOAF_NS . 'givenName');
        $given_names = $this->foaf->find(null,$given_name_resource,null);
        foreach ($given_names->triples as $given_name) {
            if (in_array($given_name->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $given_name->subj->uri) {
                        $this->foaf_data[$key]['givenName'] = $given_name->obj->label;
                    }
                    break;
                }
            } elseif (in_array($given_name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['givenName'] as $given_name_key => $given_name_array) {
                        if (isset($given_name_array['node']) && ($given_name_array['node'] == $given_name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$given_name_key]['givenName'] = $given_name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:phone's and inserts them into the result Array
     */

    function _fetchPhone()
    {
        $phone_resource = new Resource(XML_FOAF_NS . 'phone');
        $phones = $this->foaf->find(null,$phone_resource,null);
        foreach ($phones->triples as $phone) {
            if (in_array($phone->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $phone->subj->uri) {
                        $this->foaf_data[$key]['phone'][] = $phone->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($phone->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['phone'] as $phone_key => $phone_array) {
                        if (isset($phone_array['node']) && ($phone_array['node'] == $phone->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$phone_key]['phone'][] = $phone->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:mbox's and inserts them into the result Array
     */

    function _fetchMbox()
    {
        $mbox_resource = new Resource(XML_FOAF_NS . 'mbox');
        $mboxs = $this->foaf->find(null,$mbox_resource,null);
        foreach ($mboxs->triples as $mbox) {
            if (in_array($mbox->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $mbox->subj->uri) {
                        $this->foaf_data[$key]['mbox'][] = $mbox->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($mbox->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['mbox'] as $mbox_key => $mbox_array) {
                        if (isset($mbox_array['node']) && ($mbox_array['node'] == $mbox->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$mbox_key]['mbox'][] = $mbox->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:mbox_sha1sum's and inserts them into the result Array
     */

    function _fetchMboxSha1Sum()
    {
        $mbox_sha1sum_resource = new Resource(XML_FOAF_NS . 'mbox_sha1sum');
        $mbox_sha1sums = $this->foaf->find(null,$mbox_sha1sum_resource,null);
        foreach ($mbox_sha1sums->triples as $mbox_sha1sum) {
            if (in_array($mbox_sha1sum->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $mbox_sha1sum->subj->uri) {
                        $this->foaf_data[$key]['mbox_sha1sum'][] = $mbox_sha1sum->obj->label;
                    }
                    break;
                }
            } elseif (in_array($mbox_sha1sum->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['mbox_sha1sum'] as $mbox_sha1sum_key => $mbox_sha1sum_array) {
                        if (isset($mbox_sha1sum_array['node']) && ($mbox_sha1sum_array['node'] == $mbox_sha1sum->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$mbox_sha1sum_key]['mbox_sha1sum'][] = $mbox_sha1sum->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:gender's and inserts them into the result Array
     *
     * If more than one foaf:gender is found for a single foaf:Agent, the
     * last found is inserted into the result Array.
     */

    function _getGender()
    {
        $gender_resource = new Resource(XML_FOAF_NS . 'gender');
        $genders = $this->foaf->find(null,$gender_resource,null);
        foreach ($genders->triples as $gender) {
            if (in_array($gender->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $gender->subj->uri) {
                        $this->foaf_data[$key]['gender'] = $gender->obj->label;
                    }
                    break;
                }
            } elseif (in_array($gender->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['gender'] as $gender_key => $gender_array) {
                        if (isset($gender_array['node']) && ($gender_array['node'] == $gender->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$gender_key]['gender'][] = $gender->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:jabberID's and inserts them into the result Array
     */

    function _fetchJabberID()
    {
        $jabber_id_resource = new Resource(XML_FOAF_NS . 'jabberID');
        $jabber_ids = $this->foaf->find(null,$jabber_id_resource,null);
        foreach ($jabber_ids->triples as $jabber_id) {
            if (in_array($jabber_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $jabber_id->subj->uri) {
                        $this->foaf_data[$key]['jabberID'][] = $jabber_id->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($jabber_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['jabberID'] as $jabber_id_key => $jabber_id_array) {
                        if (isset($jabber_id_array['node']) && ($jabber_id_array['node'] == $jabber_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$jabber_id_key]['jabberID'][] = $jabber_id->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:aimChatID's and inserts them into the result Array
     */

    function _fetchAimChatID()
    {
        $aim_chat_id_resource = new Resource(XML_FOAF_NS . 'aimChatID');
        $aim_chat_ids = $this->foaf->find(null,$aim_chat_id_resource,null);
        foreach ($aim_chat_ids->triples as $aim_chat_id) {
            if (in_array($aim_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $aim_chat_id->subj->uri) {
                        $this->foaf_data[$key]['aimChatID'][] = $aim_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($aim_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['aimChatID'] as $aim_chat_id_key => $aim_chat_id_array) {
                        if (isset($aim_chat_id_array['node']) && ($aim_chat_id_array['node'] == $aim_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$aim_chat_id_key]['aimChatID'][] = $aim_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:icqChatID's and inserts them into the result Array
     */

    function _fetchIcqChatID()
    {
        $icq_chat_id_resource = new Resource(XML_FOAF_NS . 'icqChatID');
        $icq_chat_ids = $this->foaf->find(null,$icq_chat_id_resource,null);
        foreach ($icq_chat_ids->triples as $icq_chat_id) {
            if (in_array($icq_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $icq_chat_id->subj->uri) {
                        $this->foaf_data[$key]['icqChatID'][] = $icq_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($icq_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['icqChatID'] as $icq_chat_id_key => $icq_chat_id_array) {
                        if (isset($icq_chat_id_array['node']) && ($icq_chat_id_array['node'] == $icq_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$icq_chat_id_key]['icqChatID'][] = $icq_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:yahooChatID's and inserts them into the result Array
     */

    function _fetchYahooChatID()
    {
        $yahoo_chat_id_resource = new Resource(XML_FOAF_NS . 'yahooChatID');
        $yahoo_chat_ids = $this->foaf->find(null,$yahoo_chat_id_resource,null);
        foreach ($yahoo_chat_ids->triples as $yahoo_chat_id) {
            if (in_array($yahoo_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $yahoo_chat_id->subj->uri) {
                        $this->foaf_data[$key]['yahooChatID'][] = $yahoo_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($yahoo_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['yahooChatID'] as $yahoo_chat_id_key => $yahoo_chat_id_array) {
                        if (isset($yahoo_chat_id_array['node']) && ($yahoo_chat_id_array['node'] == $yahoo_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$yahoo_chat_id_key]['yahooChatID'][] = $yahoo_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:msnChatID's and inserts them into the result Array
     */

    function _fetchMsnChatID()
    {
        $msn_chat_id_resource = new Resource(XML_FOAF_NS . 'msnChatID');
        $msn_chat_ids = $this->foaf->find(null,$msn_chat_id_resource,null);
        foreach ($msn_chat_ids->triples as $msn_chat_id) {
            if (in_array($msn_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $msn_chat_id->subj->uri) {
                        $this->foaf_data[$key]['msnChatID'][] = $msn_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($msn_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['msnChatID'] as $msn_chat_id_key => $msn_chat_id_array) {
                        if (isset($msn_chat_id_array['node']) && ($msn_chat_id_array['node'] == $msn_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$msn_chat_id_key]['msnChatID'][] = $msn_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:onlineAccount's and inserts them into the result Array
     */

    function _fetchOnlineAccount()
    {
        $holds_account_resource = new Resource(XML_FOAF_NS . 'holdsAccount');
        $holds_accounts = $this->foaf->find(null,$holds_account_resource,null);
        $account_name_resource = new Resource(XML_FOAF_NS . 'accountName');
        $account_names = $this->foaf->find(null,$account_name_resource,null);
        $account_service_homepage_resource = new Resource(XML_FOAF_NS . 'accountServiceHomepage');
        $account_service_homepages = $this->foaf->find(null,$account_service_homepage_resource,null);
        $rdf_type_resource = new Resource(XML_FOAF_RDF_NS . 'type');
        $rdf_types = $this->foaf->find(null,$rdf_type_resource,null);

    }

    /**
     * Finds all foaf:onlineChatAccount's and inserts them into the result Array
     */

    function _fetchOnlineChatAccount()
    {

    }

    /**
     * Finds all foaf:onlineGamingAccount's and inserts them into the result Array
     */

    function _fetchOnlineGamingAccount()
    {

    }

    /**
     * Finds all foaf:onlineEcommerceAccount's and inserts them into the result Array
     */

    function _fetchOnlineEcommerceAccount()
    {

    }

    /**
     * Finds all foaf:homepage's and inserts them into the result Array
     */

    function _fetchHomepage()
    {

    }

    /**
     * Finds all foaf:weblog's and inserts them into the result Array
     */

    function _fetchWeblog()
    {

    }

    /**
     * Finds all foaf:made's and inserts them into the result Array
     */

    function _fetchMade()
    {

    }

    /* foaf:Person */

    /**
     * Finds all foaf:geekcode's and inserts them into the result Array
     *
     * If more than one foaf:geekcode is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getGeekcode()
    {

    }

    /**
     * Finds all foaf:firstName's and inserts them into the result Array
     *
     * If more than one foaf:firstName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFirstName()
    {

    }

    /**
     * Finds all foaf:surname's and inserts them into the result Array
     *
     * If more than one foaf:surname is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getSurname()
    {

    }

    /**
     * Finds all foaf:familyName's and inserts them into the result Array
     *
     * If more than one foaf:familyName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFamilyName()
    {

    }

    /**
     * Finds all foaf:plan's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getPlan()
    {

    }

    /**
     * Finds all foaf:img's and inserts them into the result Array
     */

    function _fetchImg()
    {

    }

    /**
     * Finds all foaf:myersBriggs's and inserts them into the result Array
     */

    function _fetchMyersBriggs()
    {

    }

    /**
     * Finds all foaf:workplaceHompage's and inserts them into the result Array
     */

    function _fetchWorkplaceHomepage()
    {

    }

    /**
     * Finds all foaf:workInfoHomepage's and inserts them into the result Array
     */

    function _fetchWorkInfoHomepage()
    {

    }

    /**
     * Finds all foaf:schoolHomepage's and inserts them into the result Array
     */

    function _fetchSchoolHomepage()
    {

    }

    /**
     * Finds all foaf:publication's and inserts them into the result Array
     */

    function _fetchPublication()
    {

    }

    /**
     * Finds all foaf:currentProject's and inserts them into the result Array
     */

    function _fetchCurrentProject()
    {

    }

    /**
     * Finds all foaf:pastProject's and inserts them into the result Array
     */

    function _fetchPastProject()
    {

    }

    /**
     * Finds all foaf:basedNear's and inserts them into the result Array
     */

    function _getBasedNear()
    {

    }

    /* foaf:Person && foaf:Group */

    /**
     * Finds all foaf:interest's and inserts them into the result Array
     */

    function _fetchInterest()
    {

    }

    /* foaf:Group */

    /**
     * Finds all foaf:member's and inserts them into the result Array
     */

    function _fetchMember()
    {

    }

    /**
     * Finds all foaf:membershipClass's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getMembershipClass()
    {

    }

    /* end of Agent only methods */

    /**
     * Finds all rdf:seeAlso's and inserts them into the result Array
     */

    function _fetchSeeAlso()
    {

    }

    /**
     * Finds all dc:title's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['title'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcTitle()
    {
        $dc_title_resource = new Resource(XML_FOAF_DC_NS . 'title');
        $dc_titles = $this->foaf->find(null,$dc_title_resource,null);
    }


    /**
     * Finds all dc:description's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['description'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcDescription()
    {
        $dc_description_resource = new Resource(XML_FOAF_DC_NS . 'description');
        $dc_descriptions = $this->foaf->find(null,$dc_description_resource,null);
    }

    /**#@-*/

    /**
     * Return parsed FOAF data as an Object
     *
     * @access public
     * @return object
     */

    function toObject()
    {
        $foaf_object = new stdClass();
        foreach ($this->foaf_data as $key=>$value) {
            $foaf_object->$key = $value;
        }
        return $foaf_object;
    }

    /**
     * Return parsed FOAF data as an Array
     *
     * @access public
     * @return array
     */

    function toArray()
    {
        return $this->foaf_data;
    }

    /**
     * Return parsed FOAF data pretty HTML
     *
     * @access public
     * @return string
     */

    function toHTML()
    {

    }
}

?>