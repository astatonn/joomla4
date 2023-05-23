<?php
/**
 * visit counter Joomla! Plugin
 * @author Joachim Schmidt - joachim.schmidt@jschmidt-systemberatung.de
 * @copyright Copyright (C) 2013 Joachim Schmidt. All rights reserved.
 * @license	 http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * change activity:
 * 01.02.2015: Release V2.0.0 for Joomla 3.x
 * 10.08.2020: Release V2.1.1 support for joomla 4
 * 22.09.2020: use namespaced classes
 * 30.06.2022: change/add code to support jooomla V4
 * 05.08.2022: add support to selection of geolocation server
 * 11.12.2022: add check for (ro)bots
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use jsvisit_counter\plugin\plgAjaxjsvisitHelper;


defined('_JEXEC') or die('Restricted access');


class plgSystemjsvisit_counter extends CMSPlugin
{

	var $params;

	var $init_script = true;

	function plg_counter_construct (&$subject, $params)
	{
		parent::__construct($subject, $params);
		$this->_plugin = PluginHelper::getPlugin('system', 'jsvisit_counter');
	}

	function onAjaxJsvisit_counter ()
	{
		include_once 'helper.php';
		
		$server = array( false, false, false, false );
		
		if ( $this->params->get('server1') == 1)
		   $server[0] = true;
		
	    if ( $this->params->get('server2') == 1)
		   $server[1] = true;
	   
		if ( $this->params->get('server3') == 1)
		 $server[2] = true;
	    
		 if ( strlen( $this->params->get('server4') ) > 30 )
		 	$server[3] = array ( true, $this->params->get('server4') );

		$this->helper = new plgAjaxjsvisitHelper();
		$this->helper->countVisits($server, $this->params->get('checkbot', 1) );
	}

	function onAfterDispatch ()
	{
		$app = Factory::getApplication();
		
		$document = $app->getDocument();
		if ($document->getType() != 'html')
			return true;
		
		if ($app->isClient("administrator"))
			return true;
		
		$version = new Version();
		$session = $this->params->get('sessiontime', 5);
		$base = URI::root(true);

		if ($this->init_script == true)
		{
			$java_script = "jQuery(document).ready(function() { jsvisitCountVisitors(" . $session . "); });";

			if (version_compare($version->getShortVersion(), '4', '>='))
			{
				$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
				$wa->registerAndUseScript('jsvisit.agent', 'media/plg_system_jsvisit/js/jsvisit_agent.js', ['position' => 'after'], [], ['jquery']);
				$wa->addInlineScript($java_script, ['position' => 'after'], [], ['jsvisit.agent']);
			}
			else
			{
				$document->addCustomTag("<script src='" . $base . "/media/plg_system_jsvisit/js/jsvisit_agent.js' type='text/javascript'></script>");
				$document->addCustomTag("<script type='text/javascript'>" . $java_script . " </script>");
			}
			
			$this->init_script = false;
		}
		return true;
	}
}

?>