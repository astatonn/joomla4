<?php
/**
 * @Copyright
 *
 * @package jsvisit_counter for Joomla! 3.x
 * @author     Joachim Schmidt {@link http://www.jschmidt-systemberatung.de/}
 * @version Version: 1.0.0 - 15-June-2013
 * @version Version: 2.1.0 - September-2018
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
 *  08.08-2022: added/changed code for multilanguage support
 *  19.08-2022: added/changed code for multilanguage support (support Tags en-** and de-**)
 *  12.12.2022: added option to select different counter values
 *  16.12.2022: addwd/changed code zo suppurt PostgreSQL database
 */
namespace  jsvisit_counter\module;
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

class mod_jsvisit_counterHelper
{
	protected $_db;
	protected $_lang;
	protected $_tag;

	function __construct ()
	{
		$joomla_version = new Version();
		if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
		{
			$this->_db = Factory::getContainer()->get('DatabaseDriver');
			$lang = Factory::getApplication()->getLanguage();
		}
		else
		{
			$this->_db = Factory::getDbo();
		    $lang = Factory::getLanguage();
		}
		$lang_tag = explode("-", $lang->getTag());
		$this->_lang = $lang_tag[0];
		$this->_tag = $lang->getTag();
	}

	public static function isEnabled ($param)
	{
		$param = strtolower(trim($param));
		if (($param == "") || ($param == "0") || ($param == "no") || ($param == "-1"))
			return false;
		return true;
	}

	function getCountries ($num_countries, $minimum_percent, $params)
	{
		$countries = array();

		$sql = "select SUM(count) from #__visitors_country";
		$this->_db->setQuery($sql);
		$total_visits = $this->_db->loadResult();
		if ($total_visits == null)
			return null;

		$sql = "select count(*) from #__visitors_country";
		$this->_db->setQuery($sql);
		$total_countries = $this->_db->loadResult();

		$sql = "SELECT country, name, count FROM #__visitors_country ORDER BY count DESC LIMIT " . $num_countries;
		$this->_db->setQuery($sql);
		$rows = $this->_db->loadObjectList();
		$i = 0;
		$total_visitors = $this->getVisitors(7, false);

		foreach ($rows as $row)
		{
			$percent = $row->count * 100 / $total_visits;
			$count2 = $total_visitors * ($row->count / $total_visits);

			if ($percent > $minimum_percent)
			{
				$countries[$i]['name'] = $row->name;
	            if ( $this->ckeck_key( $key = "MOD_JSVISIT_COUNTRY_" . strtoupper(str_replace(" ", "_", $countries[$i]['name']) ) ) )
				   $countries[$i]['name'] = $key;
				$countries[$i]['flag'] = $row->country . ".png";
				$countries[$i]['count'] = $this->format_number($row->count);
				$countries[$i]['count2'] = $this->format_number($count2);
				$countries[$i]['percent'] = $this->format_number($percent, $params->get('decimals') ) . "%";
			}
			$i ++;
		}

		return array(
				$countries,
				$total_countries
		);
	}

	function ckeck_key ($key)
	{
		if ($this->_lang == 'de')
		{
		  $ini1_keys = parse_ini_file(JPATH_BASE . "/language/" . $this->_tag . "/" . $this->_tag . ".mod_jsvisit_counter.ini", true);
		  if (file_exists(JPATH_BASE . "/language/overrides/" . $this->_tag . ".override.ini"))
		    $ini2_keys = parse_ini_file(JPATH_BASE . "/language/overrides/" . $this->_tag . ".override.ini", true);
		  else
		  	$ini2_keys = array();
		}
		elseif ($this->_lang == 'en')
		{
		  $ini1_keys = array();
		  if (file_exists(JPATH_BASE . "/language/overrides/" . $this->_tag . ".override.ini"))
		    $ini2_keys = parse_ini_file(JPATH_BASE . "/language/overrides/" . $this->_tag . ".override.ini", true);
		  else
		    $ini2_keys = array();
		}
		elseif ($this->_lang == 'pl')
		{
		  $ini1_keys = parse_ini_file(JPATH_BASE . "/language/pl-PL/pl-PL.mod_jsvisit_counter.ini", true);
		  if (file_exists(JPATH_BASE .  "/language/overrides/pl-PL.override.ini"))
		    $ini2_keys = parse_ini_file(JPATH_BASE . "/language/overrides/pl-PL.override.ini", true);
		  else
		    $ini2_keys = array();
		}
		else
		  return false;
	     
		if ( !is_array($ini1_keys) || !is_array($ini2_keys) )
		    return false;
		  
		if (array_key_exists($key, $ini1_keys) || array_key_exists($key, $ini2_keys) )
		  return true;
		else
		  return false;
	}
		
	function setCounter ($number = 0, $id = 7)
	{
		$today = date("Y-m-d");
		$sql = "UPDATE #__visitors SET count = " . $number . ", date='" . $today . "' WHERE id = '" . $id . "'";
		$this->_db->setQuery($sql);
		$this->_db->execute();
	}

	function updateParams ($params, $id)
	{
		$query = $this->_db->getQuery(true);

		// Build the query
		$parms = $params->toString();
		$query = "update #__modules set params = " . $this->_db->quote($parms) . " where module = 'mod_jsvisit_counter' and id = " . $id  . ";";
	        
		// Execute the query
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	function getCounter ($digitnumbers, $counter_value)
	{
		$number = $this->getVisitors($counter_value, false);
		$length = (int) $digitnumbers;
		$number = ($length > strlen($number)) ? substr('000000000' . $number, - $length) : $number;
		$digits = str_split($number);
		$counter = "";
		$visitors = $this->format_number($number) . " " . Text::_('MOD_JSVISIT_COUNTER_VISITORS');
		foreach ($digits as $digit)
		{
			$counter .= "<span class=\"digit-$digit\" title=\"$visitors\">$digit</span>";
		}
		return $counter;
	}

	function getVisitors ($timeframe, $format = true)
	{
		$sql = "select count from #__visitors where id = '" . $timeframe . "'";
		$this->_db->setQuery($sql);
		$result = $this->_db->loadResult();
		if ($result)
		{
			$visitors = $this->_db->loadColumn(0);
			if ($format)
				return $this->format_number($visitors[0]);
			else
				return $visitors[0];
		}
		else
			return null;
	}

	function createLayout ($params)
	{
		$width = $params->get('digit_width');
		$offset = $params->get('digit_offset');
		$height = $params->get('digit_height');
		$image = $params->get('image');
		$url = URI::root() . "media/mod_jsvisit_counter/images/" . $image;

		$css = "    /* mod jsvisit counter layout */";
		$css .= "\n    .digit-0,.digit-1,.digit-2,.digit-3,.digit-4,.digit-5,.digit-6,.digit-7,.digit-8,.digit-9
               { width: " . $width . "px; height: " . $height . "px; background: url('" . $url .
				"') no-repeat top left; text-indent: -9999em; display: inline-block; }";

		for ($i = 0; $i < 10; $i ++)
			$css .= "\n    .digit-" . $i . " { background-position: " . - $i * $offset . "px 0; }";

		return $css;
	}

	function createRandomLayout ()
	{
		$layout = rand(1, 20);
		$props = parse_ini_file(JPATH_SITE . "/modules/mod_jsvisit_counter/counter.props", true);
		$width = $props[$layout]['digit_width'];
		$offset = $props[$layout]['digit_offset'];
		$height = $props[$layout]['digit_height'];
		$image = $props[$layout]['image'];

		$url = URI::root() . "media/mod_jsvisit_counter/images/" . $image;

		$css = "    /* mod jsvisit counter layout */";
		$css .= "\n    .digit-0,.digit-1,.digit-2,.digit-3,.digit-4,.digit-5,.digit-6,.digit-7,.digit-8,.digit-9
               { width: " . $width . "px; height: " . $height . "px; background: url('" . $url . "') no-repeat top left; text-indent: -9999em; display: inline-block; }";

		for ($i = 0; $i < 10; $i ++)
			$css .= "\n    .digit-" . $i . " { background-position: " . - $i * $offset . "px 0; }";
			
			return $css;
	}
	
	function format_number ($number, $decimal = 0)
	{
		if ( preg_match('/ch|da|de|el|es|fr|it|nl|pl|pt|tr/i', $this->_lang) )
			return number_format($number, $decimal, ',', '.');
		elseif ( preg_match('/et|cs|fi|nb|sv|uk/i', $this->_lang) )
		    return number_format($number, $decimal, ',', ' ');
		else
			return number_format($number, $decimal, '.', ',');
	}
}
