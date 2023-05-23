<?php
/**
 * jmootips Joomla! Plugin
 * @author Joachim Schmidt - joachim.schmidt@jschmidt-systemberatung.de
 * @copyright Copyright (C) 2013 Joachim Schmidt. All rights reserved.
 * @license	 http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * change activity:
 * 1.02.2015: Release V2.0.0 for Joomla 3.x
 * 1.10.2018: Use new Namespaced Joomla API
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Version;

class plgsystemjsvisit_counterInstallerScript
{

	function postflight ($parent, $type)
	{
		$version = new Version();
		if (version_compare($version->getShortVersion(), '4.0', '>='))
			$db = Factory::getContainer()->get('DatabaseDriver');
		else
			$db = Factory::getDbo();

		// Enable plugin
		$sql = " UPDATE  #__extensions SET enabled=1 WHERE type='plugin' AND element='jsvisit_counter' AND folder='system';";
		$db->setQuery($sql);
		$db->execute();
	}
}

?>