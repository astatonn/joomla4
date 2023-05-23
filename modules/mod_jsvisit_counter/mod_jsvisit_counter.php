<?php
/**
 * @Copyright
 *
 * @package jsvisit_counter for Joomla! 3.x
 * @author     Joachim Schmidt {@link http://www.jschmidt-systemberatung.de/}
 * @version    Version: 2.0.0 - 01-Feb-2015
 * @version    Version: 2.1.0 - 01-Sep-2018
 * @link       Project Site {@link http://www.jschmidt-systemberatung.de/}
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * change activity:
 *  02.10-2018: changed to new namespaced Joomla API
 *  24-01-2022: changed layout setting for counter
 *  12.12.2022: added option to select different counter values
 *
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use jsvisit_counter\module\mod_jsvisit_counterHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;

if (! PluginHelper::isEnabled('system', 'jsvisit_counter'))
{
	echo sprintf(Text::_("MOD_JSVISIT_COUNTER_ERROR"), 'system');
	return;
}

require_once (dirname(__FILE__) . '/helper.php');
$helper = new mod_jsvisit_counterHelper();

$joomlav4 = false;
$joomla_version = new Version();
if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
{
	$joomlav4 = true;
	$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
	if ( $params->get('cache', 0) )
	  $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
	    ->createCacheController('output', ['defaultgroup' => 'mod_jsvisit_counter']);
}
else
{
  $cache_time = (int) $params->get('cache_time', 900);
  $cache_enabled = $params->get('cache', 0);
  $cache = Factory::getCache('mod_jsvisit_counter');
  $cache->setCaching($cache_enabled);
  $cache->setLifeTime($cache_time);
 }

if ($params->get('reset_counter'))
{
	$helper->setCounter($params->get('initialvalue'));
	$params->set('reset_counter', '0');
	$module = ModuleHelper::getModule('mod_jsvisit_counter');
	$module_id = $module->id;
	$helper->updateParams($params, $module_id);
}

if ($params->get('counter', "1") != $params->get('current_counter'))
{
	$props = parse_ini_file(JPATH_SITE . "/modules/mod_jsvisit_counter/counter.props", true);
	$counter = $params->get('counter');
	
	$params->set('current_counter', $counter);
	$params->set('image', $props[$counter]['image']);
	$params->set('digit_width', $props[$counter]['digit_width']);
	$params->set('digit_offset', $props[$counter]['digit_offset']);
	$params->set('digit_height', $props[$counter]['digit_height']);
	
	$module = ModuleHelper::getModule('mod_jsvisit_counter');
	$module_id = $module->id;
	$helper->updateParams($params, $module_id);
}

$country_info = $helper->getCountries($params->get('countries', 10), floatval($params->get('percent', 0)), $params);
if ($country_info != null)
{
	$countries = $country_info[0];
	$total_countries = $country_info[1];
}
else
{
	$countries = array();
	$total_countries = 0;
}

if ($params->get('show_today'))
	$today = $helper->getVisitors(1);
else
    $today = null;
if ($params->get('show_yesterday'))
	$yesterday = $helper->getVisitors(2);
else
	$yesterday = null;
if ($params->get('show_thisweek'))
	$this_week = $helper->getVisitors(3);
else
	$this_week = null;
if ($params->get('show_lastweek'))
	$last_week = $helper->getVisitors(4);
else
	$last_week = null;
if ($params->get('show_thismonth'))
	$this_month = $helper->getVisitors(5);
else
	$this_month = null;
if ($params->get('show_lastmonth'))
	$last_month = $helper->getVisitors(6);
else
	$last_month = null;
if ($params->get('show_totals'))
	$totals = $helper->getVisitors(7);
else
    $totals = null;

$document = Factory::getApplication()->getDocument();

if ($params->get('boxlayout') == "1")
	$layout_class = "boxed";
else
	$layout_class = "table";

$counter = $helper->getCounter($params->get('digits', 6), $params->get('counter_value', 7) );

if ($params->get('randomcounter'))
	$stylesheet = $helper->createRandomLayout();
else
	$stylesheet = $helper->createLayout($params);

$style_url = "media/mod_jsvisit_counter/css/jvisit_counter.css";
if ($joomlav4)
{
	$wa->registerAndUseStyle("jsvisit", $style_url);
	$wa->addInlineStyle($stylesheet);
}
else
{
	$document->addStyleSheet($style_url);
    $document->addStyleDeclaration($stylesheet);
}

if ( $params->get('moduleclass_sfx') !== null)
  $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
else
  $moduleclass_sfx = "";
require ModuleHelper::getLayoutPath('mod_jsvisit_counter', $params->get('layout', 'default'));

?>