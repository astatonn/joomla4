<?php
/**
 * @Copyright
 *
 * @package	jsvisit_counter for Joomla! 3.x
 * @author     Joachim Schmidt {@link http://www.jschmidt-systemberatung.de/}
 * @version	Version: 2.0.0 - 2-February-2015
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
 *  03.08.2020: changed server for ip/country detection (2 server)
 *  14.08.2020: processing of ip 127.0.0.1 (localhost) enabled
 *  30.08.2020: added server for ip/country detection (now 3 server)
 *  31.08.2020: added server for ip/country detection (now 4 server)
 *  22.09.2020: use namespaced classes
 *  30.06.2022: change/add code to support jooomla V4
 *  06.08.2022: add support for selection of geolocation server
 *  11.12.2022: add check for counting (ro)bots
 *  16.12.2022: add/change code zo suppurt PostgreSQL database
 *  06.01.2023: add code to get client-ip if proxy server used
 */
// No direct access
namespace jsvisit_counter\plugin;
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Version;

class plgAjaxjsvisitHelper
{

	var $db;

	function countVisits ($geolocation_server, $check_bot = true)
	{
		$debug = false;

		if ($check_bot && isset( $_SERVER['HTTP_USER_AGENT']) )
		{
			//if ( preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i', $_SERVER['HTTP_USER_AGENT']))
			//	return false; // 'Above given bots detected'
			if ( $this->is_bot($_SERVER['HTTP_USER_AGENT']) )
				return false;
		}
		
		$joomla_version = new Version();
		if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
			$this->db = Factory::getContainer()->get('DatabaseDriver');
		else
			$this->db = Factory::getDbo();
	   
		$today = date("Y-m-d");
		$month = date("m");
		$week = intval(date('W'));
		
		// totals:
		$sql = "UPDATE #__visitors SET count = count + 1, date='" . $today . "' WHERE id = '7'";
		$this->jsvisitDBRequest(true, $sql);

		// monthly stats
		$sql = "select EXTRACT(MONTH FROM date),count from #__visitors where id = 5";
		$result = $this->jsvisitDBRequest(false, $sql);
		if ($month == $result[0])
			$sql = "UPDATE #__visitors SET count = count + 1, date='" . $today . "' WHERE id = '5'";
		else
		{
			$sql = "UPDATE #__visitors SET count = " . $result[1] . ", date='" . $today . "' WHERE id = '6'";
			$this->jsvisitDBRequest(true, $sql);
			$sql = "UPDATE #__visitors SET count = 1, date='" . $today . "' WHERE id = '5'";
		}
		$this->jsvisitDBRequest(true, $sql);

		// this week's stats
		if ( preg_match('/mysql/i',$this->db->getName()))
			$sql = "select WEEK(date, 3),count from #__visitors where id = 3";
		else
		    $sql = "select EXTRACT(WEEK FROM date),count from #__visitors where id = 3";
		$result = $this->jsvisitDBRequest(false, $sql);
		if ($week == $result[0])
			$sql = "UPDATE #__visitors SET count = count + 1, date='" . $today . "' WHERE id = '3'";
		else
		{
			$sql = "UPDATE #__visitors SET count = " . $result[1] . ", date='" . $today . "' WHERE id = '4'";
			$this->jsvisitDBRequest(true, $sql);
			$sql = "UPDATE #__visitors SET count = 1, date='" . $today . "' WHERE id = '3'";
		}
		$this->jsvisitDBRequest(true, $sql);

		// today's stats
		$sql = "select date, count from #__visitors where id = 1";
		$result = $this->jsvisitDBRequest(false, $sql);
		if ($today == $result[0])
			$sql = "UPDATE #__visitors SET count = count + 1, date='" . $today . "' WHERE id = '1'";
		else
		{
			$sql = "UPDATE #__visitors SET count = " . $result[1] . ", date='" . $today . "' WHERE id = '2'";
			$this->jsvisitDBRequest(true, $sql);
			$sql = "UPDATE #__visitors SET count = 1, date='" . $today . "' WHERE id = '1'";
		}
		$this->jsvisitDBRequest(true, $sql);

		/* update counts for countries */
		if (in_array(true, $geolocation_server))
		{
			$ip = trim($this->jsvisitgetIp());

			if ($ip == "::1")
				$ip = "127.0.0.1";

			if ($ip != - 1)
				$info = $this->jsvisitgetIpInfo($ip, $geolocation_server);

			if (isset($info['country']['code']))
			{
				$country = strtolower($info['country']['code']);
				if ($country == "xz")
				    $name = Factory::getApplication()->get('sitename');
			    else
			    	$name = $info['country']['name'];
				$sql = "SELECT country FROM #__visitors_country WHERE country ='" . $country . "'";
				$result = $this->jsvisitDBRequest(false, $sql);
				if ($result)
					$sql = "UPDATE #__visitors_country SET count = count + 1 WHERE country='" . $country . "'";
				else
					$sql = "INSERT into #__visitors_country (country, name, count) values('" . $country . "', '" . $name . "', '1')";
				$result = $this->jsvisitDBRequest(true, $sql);
			}
			else
			{
				$country = "zz";
				$name = "unknown";
		    }
		}

		if ($debug == true)
		{
			echo "<br> $ip";
			
			if ($ip != -1)
			  $host = gethostbyaddr($ip) . ": " . $_SERVER['HTTP_USER_AGENT'];
			else
			  $host = $_SERVER['HTTP_USER_AGENT'];
			$date = date("Y-m-d H:i:s");
			if (isset($info['server']['url']))
				$server = $info['server']['url'];
			else
				$server = 'not used';
			$sql = "INSERT into #__visitors_debug (ip, date, country, host, server, name)
            values('" . $ip . "','" . $date . "','" . $country . "', '" . $host . "','" . $server . "','" . $name . "')";
			$result = $this->jsvisitDBRequest(true, $sql);
		}
	}

	function jsvisitgetIp ()
	{
	
		$keys = array(
				'HTTP_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR'
		);
		foreach ($keys as $k)
		{
			if (isset($_SERVER[$k]) && strpos ($_SERVER[$k], ',') !== false )
			{
				$ips = explode(',', $_SERVER[$k]);
				$_SERVER[$k] = trim($ips[count($ips) - 1]);
			}
				
			if (isset($_SERVER[$k]) && filter_var($_SERVER[$k], FILTER_VALIDATE_IP))
			  return $_SERVER[$k];
		}
		
		return -1;
	}
	
	function jsvisitgetIpInfo ($ip, $geolocation_server)
	{
		
		if (filter_var($ip, FILTER_VALIDATE_IP) == false)
			return null;

		if (substr($ip, 0, 3) == "127")
		{
			$lang = Factory::getApplication()->getLanguage();

			$info['country']['code'] = substr($lang->getTag(), 0, 2);
			preg_match('#\((.*?)\)#', $lang->getName(), $name);
			$info['country']['name'] = $name[1];
			$info['server']['url'] = 'localhost';

			return $info;
		}

		$server = array();

		if ($geolocation_server[0])
		{
			$server_props = array(
					'num' => 1,
					'transport' => 'tcp://',
					'url' => "http://www.geoplugin.net/json.gp?ip=" . $ip,
					'host' => 'geoplugin.net',
					'json' => true,
					'port' => 80
			);
			array_push($server, $server_props);
		}

		if ($geolocation_server[1])
		{
			$server_props = array(
					'num' => 2,
					'transport' => 'tcp://',
					'url' => "http://ip-api.com/json/" . $ip,
					'host' => 'ip-api.com',
					'json' => true,
					'port' => 80
			);
			array_push($server, $server_props);
		}

		if ($geolocation_server[2])
		{
			$server_props = array(
					'transport' => 'tcp://',
					'url' => "https://ip2c.org/" . $ip,
					'host' => "ip2c.org",
					'json' => false,
					'port' => 443
			);
			array_push($server, $server_props);
		}

		if ($geolocation_server[3][0])
		{
			$api_key = $geolocation_server[3][1];
			$server_props = array(
					'num' => 3,
					'transport' => 'tcp://',
					'url' => "https://ipapi.co/" . $ip . "/json/?key=" . $api_key,
					'host' => 'ipapi.co',
					'json' => true,
					'port' => 443
			);
			array_push($server, $server_props);
		}

		$i = 0;
		$found = false;
		
		if (count($server) > 1)
		{
			$key = rand(0, count($server)-1);
			while ($key > 0) {
				$temp = array_shift($server);
				$server[] = $temp;
				$key--;
			}
		}

		while ($i < count($server) && $found == false)
		{
			$info = null;

			if (function_exists("curl_init"))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_PORT, $server[$i]['port']);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_URL, $server[$i]['url']);
				$line = curl_exec($ch);
				if ($line === false)
					echo curl_error($ch);
				curl_close($ch);
			}
			else
			{
				$errno = "";
				$errstr = "";
				$line = "";
				$fp = fsockopen($server[$i]['transport'] . $url['host'], $server[$i]['port'], $errno, $errstr, 30);
				if (! $fp)
				{
					echo "<br /> $errstr";
				}
				else
				{
					$out = "GET " . $url['path'] . " HTTP/1.1\r\n";
					$out .= "Host: " . $url['host'] . "\r\n";
					$out .= "Connection: Close\r\n\r\n";
					fwrite($fp, $out);

					$info = "";
					while (! feof($fp))
					{
						$line .= fgets($fp, 512);
					}

					fclose($fp);
					$line = substr($line, strpos($line, "\r\n\r\n") + 4);
				}
			}

			$tmp = json_decode($line, true);
			$lastserver = $server[$i]['host'];

			if ($server[$i]['json'] == true)
			{
				if ($server[$i]['num'] == 1 && $tmp['geoplugin_status'] == 200)
				{
					$info['country']['code'] = $tmp['geoplugin_countryCode'];
					$info['country']['name'] = $tmp['geoplugin_countryName'];
					$info['server']['url'] = $server[$i]['host'];
				}
				elseif ($server[$i]['num'] == 2 && $tmp['status'] == 'success')
				{
					$info['country']['code'] = $tmp['countryCode'];
					$info['country']['name'] = $tmp['country'];
					$info['server']['url'] = $server[$i]['host'];
				}
				elseif ($server[$i]['num'] == 3 && ! isset($tmp['error']))
				{
					$info['country']['code'] = $tmp['country_code'];
					$info['country']['name'] = $tmp['country_name'];
					$info['server']['url'] = $server[$i]['host'];
				}
				elseif (isset($tmp['error']))
				{
					$info['country']['code'] = 'zz';
					$info['country']['name'] = $tmp['message'];
					$info['server']['url'] = $server[$i]['host'];
			    }
			}
			else
			{
				$tmp = explode(";", $line);
				if (count($tmp) > 3 && $tmp[0] == "1")
				{
					$info['country']['code'] = $tmp[1];
					$info['country']['name'] = $tmp[3];
					$info['server']['url'] = $server[$i]['host'];
				}
			}

			if (isset($info['country']['code']))
			{
				$found = true;
				$i = count($server) + 1;
				return $info;
			}
			
			$i ++;
		}

		$info['country']['code'] = "zz";
		$info['country']['name'] = "unknown";
		$info['server']['url'] = $lastserver;

		return $info;
	}

	function jsvisitDBRequest ($update, $sql)
	{
		if ($update == true)
		{
			$result = $this->db->setQuery($sql);
			$this->db->execute();
			return $result;
		}
		else
		{
			$result = $this->db->setQuery($sql);
			$row = $this->db->loadRow();
			return $row;
		}
		return - 1;
	}

	function is_bot($agent)
	{
		
		$bots = array(
				'Googlebot'
				, 'Baiduspider'
				, 'ia_archiver'
				, 'R6_FeedFetcher'
				, 'NetcraftSurveyAgent'
				, 'Sogou web spider'
				, 'bingbot'
				, 'Yahoo! Slurp'
				, 'facebookexternalhit'
				, 'PrintfulBot'
				, 'msnbot'
				, 'Twitterbot'
				, 'UnwindFetchor'
				, 'urlresolver'
				, 'Butterfly'
				, 'TweetmemeBot'
				, 'PaperLiBot'
				, 'MJ12bot'
				, 'AhrefsBot'
				, 'Exabot'
				, 'Ezooms'
				, 'YandexBot'
				, 'SearchmetricsBot'
				, 'picsearch'
				, 'TweetedTimes Bot'
				, 'QuerySeekerSpider'
				, 'ShowyouBot'
				, 'woriobot'
				, 'merlinkbot'
				, 'BazQuxBot'
				, 'Kraken'
				, 'SISTRIX Crawler'
				, 'R6_CommentReader'
				, 'magpie-crawler'
				, 'GrapeshotCrawler'
				, 'PercolateCrawler'
				, 'MaxPointCrawler'
				, 'R6_FeedFetcher'
				, 'NetSeer crawler'
				, 'grokkit-crawler'
				, 'SMXCrawler'
				, 'PulseCrawler'
				, 'Y!J-BRW'
				, '80legs'
				, 'Mediapartners-Google'
				, 'Spinn3r'
				, 'InAGist'
				, 'Python-urllib'
				, 'NING'
				, 'TencentTraveler'
				, 'Feedfetcher-Google'
				, 'mon.itor.us'
				, 'spbot'
				, 'Feedly'
				, 'bitlybot'
				, 'ADmantX'
				, 'Niki-Bot'
				, 'Pinterest'
				, 'python-requests'
				, 'DotBot'
				, 'HTTP_Request2'
				, 'linkdexbot'
				, 'A6-Indexer'
				, 'Baiduspider'
				, 'TwitterFeed'
				, 'Microsoft Office'
				, 'Pingdom'
				, 'BTWebClient'
				, 'KatBot'
				, 'SiteCheck'
				, 'proximic'
				, 'Sleuth'
				, 'Abonti'
				, '(BOT for JCE)'
				, 'Baidu'
				, 'Tiny Tiny RSS'
				, 'newsblur'
				, 'updown_tester'
				, 'linkdex'
				, 'baidu'
				, 'searchmetrics'
				, 'genieo'
				, 'majestic12'
				, 'spinn3r'
				, 'profound'
				, 'domainappender'
				, 'VegeBot'
				, 'terrykyleseoagency.com'
				, 'CommonCrawler Node'
				, 'AdlesseBot'
				, 'metauri.com'
				, 'libwww-perl'
				, 'rogerbot-crawler'
				, 'MegaIndex.ru'
				, 'ltx71'
				, 'Qwantify'
				, 'Traackr.com'
				, 'Re-Animator Bot'
				, 'Pcore-HTTP'
				, 'BoardReader'
				, 'omgili'
				, 'okhttp'
				, 'CCBot'
				, 'Java/1.8'
				, 'semrush.com'
				, 'feedbot'
				, 'CommonCrawler'
				, 'AdlesseBot'
				, 'MetaURI'
				, 'ibwww-perl'
				, 'rogerbot'
				, 'MegaIndex'
				, 'BLEXBot'
				, 'FlipboardProxy'
				, 'techinfo@ubermetrics-technologies.com'
				, 'trendictionbot'
				, 'Mediatoolkitbot'
				, 'trendiction'
				, 'ubermetrics'
				, 'ScooperBot'
				, 'TrendsmapResolver'
				, 'Nuzzel'
				, 'Go-http-client'
				, 'Applebot'
				, 'LivelapBot'
				, 'GroupHigh'
				, 'SemrushBot'
				, 'ltx71'
				, 'commoncrawl'
				, 'istellabot'
				, 'DomainCrawler'
				, 'cs.daum.net'
				, 'StormCrawler'
				, 'GarlikCrawler'
				, 'The Knowledge AI'
				, 'getstream.io/winds'
				, 'YisouSpider'
				, 'archive.org_bot'
				, 'semantic-visions.com'
				, 'FemtosearchBot'
				, '360Spider'
				, 'linkfluence.com'
				, 'glutenfreepleasure.com'
				, 'Gluten Free Crawler'
				, 'YaK/1.0'
				, 'Cliqzbot'
				, 'app.hypefactors.com'
				, 'axios'
				, 'semantic-visions.com'
				, 'webdatastats.com'
				, 'schmorp.de'
				, 'SEOkicks'
				, 'DuckDuckBot'
				, 'Barkrowler'
				, 'ZoominfoBot'
				, 'Linguee Bot'
				, 'Mail.RU_Bot'
				, 'OnalyticaBot'
				, 'Linguee Bot'
				, 'admantx-adform'
				, 'Buck/2.2'
				, 'Barkrowler'
				, 'Zombiebot'
				, 'Nutch'
				, 'SemanticScholarBot'
				, 'Jetslide'
				, 'scalaj-http'
				, 'XoviBot'
				, 'sysomos.com'
				, 'PocketParser'
				, 'newspaper'
				, 'serpstatbot'
				, 'MetaJobBot'
				, 'SeznamBot/3.2'
				, 'VelenPublicWebCrawler/1.0'
				, 'WordPress.com mShots'
				, 'adscanner'
				, 'BacklinkCrawler'
				, 'netEstate NE Crawler'
				, 'Astute SRM'
				, 'GigablastOpenSource/1.0'
				, 'DomainStatsBot'
				, 'Winds: Open Source RSS & Podcast'
				, 'dlvr.it'
				, 'BehloolBot'
				, '7Siters'
				, 'AwarioSmartBot'
				, 'Apache-HttpClient/5'
				, 'Seekport Crawler'
				, 'AHC/2.1'
				, 'eCairn-Grabber'
				, 'mediawords bot'
				, 'PHP-Curl-Class'
				, 'Scrapy'
				, 'curl/7'
				, 'Blackboard'
				, 'NetNewsWire'
				, 'node-fetch'
				, 'admantx'
				, 'metadataparser'
				, 'Domains Project'
				, 'SerendeputyBot'
				, 'Moreover'
				, 'DuckDuckGo'
				, 'monitoring-plugins'
				, 'Selfoss'
				, 'Adsbot'
				, 'acebookexternalhit'
				, 'SpiderLing'
				, 'Cocolyzebot'
				, 'AhrefsBot'
				, 'TTD-Content'
				, 'superfeedr'
				, 'Twingly'
				, 'Google-Apps-Scrip'
				, 'LinkpadBot'
				, 'CensysInspect'
				, 'Reeder'
				, 'tweetedtimes'
				, 'Amazonbot'
				, 'MauiBot'
				, 'Symfony BrowserKit'
				, 'DataForSeoBot'
				, 'GoogleProducer'
				, 'TinEye-bot-live'
				, 'sindresorhus/got'
				, 'CriteoBot'
				, 'Down/5'
				, 'Yahoo Ad monitoring'
				, 'MetaInspector'
				, 'PetalBot'
				, 'MetadataScraper'
				, 'Cloudflare SpeedTest'
				, 'CriteoBot'
				, 'aiohttp'
				, 'AppEngine-Google'
				, 'heritrix'
				, 'sqlmap'
				, 'Buck'
				, 'MJ12bot'
				, 'wp_is_mobile'
				, 'SerendeputyBot'
				, '01h4x.com'
				, '404checker'
				, '404enemy'
				, 'AIBOT'
				, 'ALittle Client'
				, 'ASPSeek'
				, 'Aboundex'
				, 'Acunetix'
				, 'AfD-Verbotsverfahren'
				, 'AiHitBot'
				, 'Aipbot'
				, 'Alexibot'
				, 'AllSubmitter'
				, 'Alligator'
				, 'AlphaBot'
				, 'Anarchie'
				, 'Anarchy'
				, 'Anarchy99'
				, 'Ankit'
				, 'Anthill'
				, 'Apexoo'
				, 'Aspiegel'
				, 'Asterias'
				, 'Atomseobot'
				, 'Attach'
				, 'AwarioRssBot'
				, 'BBBike'
				, 'BDCbot'
				, 'BDFetch'
				, 'BackDoorBot'
				, 'BackStreet'
				, 'BackWeb'
				, 'Backlink-Ceck'
				, 'BacklinkCrawler'
				, 'Badass'
				, 'Bandit'
				, 'Barkrowler'
				, 'BatchFTP'
				, 'Battleztar Bazinga'
				, 'BetaBot'
				, 'Bigfoot'
				, 'Bitacle'
				, 'BlackWidow'
				, 'Black Hole'
				, 'Blackboard'
				, 'Blow'
				, 'BlowFish'
				, 'Boardreader'
				, 'Bolt'
				, 'BotALot'
				, 'Brandprotect'
				, 'Brandwatch'
				, 'Buck'
				, 'Buddy'
				, 'BuiltBotTough'
				, 'BuiltWith'
				, 'Bullseye'
				, 'BunnySlippers'
				, 'BuzzSumo'
				, 'CATExplorador'
				, 'CCBot'
				, 'CODE87'
				, 'CSHttp'
				, 'Calculon'
				, 'CazoodleBot'
				, 'Cegbfeieh'
				, 'CensysInspect'
				, 'CheTeam'
				, 'CheeseBot'
				, 'CherryPicker'
				, 'ChinaClaw'
				, 'Chlooe'
				, 'Citoid'
				, 'Claritybot'
				, 'Cliqzbot'
				, 'Cloud mapping'
				, 'Cocolyzebot'
				, 'Cogentbot'
				, 'Collector'
				, 'Copier'
				, 'CopyRightCheck'
				, 'Copyscape'
				, 'Cosmos'
				, 'Craftbot'
				, 'Crawling at Home Project'
				, 'CrazyWebCrawler'
				, 'Crescent'
				, 'CrunchBot'
				, 'Curious'
				, 'Custo'
				, 'CyotekWebCopy'
				, 'DBLBot'
				, 'DIIbot'
				, 'DSearch'
				, 'DTS Agent'
				, 'DataCha0s'
				, 'DatabaseDriverMysqli'
				, 'Demon'
				, 'Deusu'
				, 'Devil'
				, 'Digincore'
				, 'DigitalPebble'
				, 'Dirbuster'
				, 'Disco'
				, 'Discobot'
				, 'Discoverybot'
				, 'Dispatch'
				, 'DittoSpyder'
				, 'DnBCrawler-Analytics'
				, 'DnyzBot'
				, 'DomCopBot'
				, 'DomainAppender'
				, 'DomainCrawler'
				, 'DomainSigmaCrawler'
				, 'DomainStatsBot'
				, 'Domains Project'
				, 'Dotbot'
				, 'Download Wonder'
				, 'Dragonfly'
				, 'Drip'
				, 'ECCP/1.0'
				, 'EMail Siphon'
				, 'EMail Wolf'
				, 'EasyDL'
				, 'Ebingbong'
				, 'Ecxi'
				, 'EirGrabber'
				, 'EroCrawler'
				, 'Evil'
				, 'Exabot'
				, 'Express WebPictures'
				, 'ExtLinksBot'
				, 'Extractor'
				, 'ExtractorPro'
				, 'Extreme Picture Finder'
				, 'EyeNetIE'
				, 'Ezooms'
				, 'FDM'
				, 'FHscan'
				, 'FemtosearchBot'
				, 'Fimap'
				, 'Firefox/7.0'
				, 'FlashGet'
				, 'Flunky'
				, 'Foobot'
				, 'Freeuploader'
				, 'FrontPage'
				, 'Fuzz'
				, 'FyberSpider'
				, 'Fyrebot'
				, 'G-i-g-a-b-o-t'
				, 'GT::WWW'
				, 'GalaxyBot'
				, 'Genieo'
				, 'GermCrawler'
				, 'GetRight'
				, 'GetWeb'
				, 'Getintent'
				, 'Gigabot'
				, 'Go!Zilla'
				, 'Go-Ahead-Got-It'
				, 'GoZilla'
				, 'Gotit'
				, 'GrabNet'
				, 'Grabber'
				, 'Grafula'
				, 'GrapeFX'
				, 'GrapeshotCrawler'
				, 'GridBot'
				, 'HEADMasterSEO'
				, 'HMView'
				, 'HTMLparser'
				, 'HTTP::Lite'
				, 'HTTrack'
				, 'Haansoft'
				, 'HaosouSpider'
				, 'Harvest'
				, 'Havij'
				, 'Heritrix'
				, 'Hloader'
				, 'HonoluluBot'
				, 'Humanlinks'
				, 'HybridBot'
				, 'IDBTE4M'
				, 'IDBot'
				, 'IRLbot'
				, 'Iblog'
				, 'Id-search'
				, 'IlseBot'
				, 'Image Fetch'
				, 'Image Sucker'
				, 'IndeedBot'
				, 'Indy Library'
				, 'InfoNaviRobot'
				, 'InfoTekies'
				, 'Intelliseek'
				, 'InterGET'
				, 'InternetSeer'
				, 'Internet Ninja'
				, 'Iria'
				, 'Iskanie'
				, 'IstellaBot'
				, 'JOC Web Spider'
				, 'JamesBOT'
				, 'Jbrofuzz'
				, 'JennyBot'
				, 'JetCar'
				, 'Jetty'
				, 'JikeSpider'
				, 'Joomla'
				, 'Jorgee'
				, 'JustView'
				, 'Jyxobot'
				, 'Kenjin Spider'
				, 'Keybot Translation-Search-Machine'
				, 'Keyword Density'
				, 'Kinza'
				, 'Kozmosbot'
				, 'LNSpiderguy'
				, 'LWP::Simple'
				, 'Lanshanbot'
				, 'Larbin'
				, 'Leap'
				, 'LeechFTP'
				, 'LeechGet'
				, 'LexiBot'
				, 'Lftp'
				, 'LibWeb'
				, 'Libwhisker'
				, 'LieBaoFast'
				, 'Lightspeedsystems'
				, 'Likse'
				, 'LinkScan'
				, 'LinkWalker'
				, 'Linkbot'
				, 'LinkextractorPro'
				, 'LinkpadBot'
				, 'LinksManager'
				, 'LinqiaMetadataDownloaderBot'
				, 'LinqiaRSSBot'
				, 'LinqiaScrapeBot'
				, 'Lipperhey'
				, 'Lipperhey Spider'
				, 'Litemage_walker'
				, 'Lmspider'
				, 'Ltx71'
				, 'MFC_Tear_Sample'
				, 'MIDown tool'
				, 'MIIxpc'
				, 'MJ12bot'
				, 'MQQBrowser'
				, 'MSFrontPage'
				, 'MSIECrawler'
				, 'MTRobot'
				, 'Mag-Net'
				, 'Magnet'
				, 'Mail.RU_Bot'
				, 'Majestic-SEO'
				, 'Majestic12'
				, 'Majestic SEO'
				, 'MarkMonitor'
				, 'MarkWatch'
				, 'Mass Downloader'
				, 'Masscan'
				, 'Mata Hari'
				, 'MauiBot'
				, 'Mb2345Browser'
				, 'MeanPath Bot'
				, 'Meanpathbot'
				, 'Mediatoolkitbot'
				, 'MegaIndex.ru'
				, 'Metauri'
				, 'MicroMessenger'
				, 'Microsoft Data Access'
				, 'Microsoft URL Control'
				, 'Minefield'
				, 'Mister PiX'
				, 'Moblie Safari'
				, 'Mojeek'
				, 'Mojolicious'
				, 'MolokaiBot'
				, 'Morfeus Fucking Scanner'
				, 'Mozlila'
				, 'Mr.4x3'
				, 'Msrabot'
				, 'Musobot'
				, 'NICErsPRO'
				, 'NPbot'
				, 'Name Intelligence'
				, 'Nameprotect'
				, 'Navroad'
				, 'NearSite'
				, 'Needle'
				, 'Nessus'
				, 'NetAnts'
				, 'NetLyzer'
				, 'NetMechanic'
				, 'NetSpider'
				, 'NetZIP'
				, 'Net Vampire'
				, 'Netcraft'
				, 'Nettrack'
				, 'Netvibes'
				, 'NextGenSearchBot'
				, 'Nibbler'
				, 'Niki-bot'
				, 'Nikto'
				, 'NimbleCrawler'
				, 'Nimbostratus'
				, 'Ninja'
				, 'Nmap'
				, 'Not'
				, 'Nuclei'
				, 'Nutch'
				, 'Octopus'
				, 'Offline Explorer'
				, 'Offline Navigator'
				, 'OnCrawl'
				, 'OpenLinkProfiler'
				, 'OpenVAS'
				, 'Openfind'
				, 'Openvas'
				, 'OrangeBot'
				, 'OrangeSpider'
				, 'OutclicksBot'
				, 'OutfoxBot'
				, 'PECL::HTTP'
				, 'PHPCrawl'
				, 'POE-Component-Client-HTTP'
				, 'PageAnalyzer'
				, 'PageGrabber'
				, 'PageScorer'
				, 'PageThing.com'
				, 'page-preview-tool'
				, 'Page Analyzer'
				, 'Pandalytics'
				, 'Panscient'
				, 'Papa Foto'
				, 'Pavuk'
				, 'PeoplePal'
				, 'Petalbot'
				, 'Pi-Monster'
				, 'Picscout'
				, 'Picsearch'
				, 'PictureFinder'
				, 'Piepmatz'
				, 'Pimonster'
				, 'Pixray'
				, 'PleaseCrawl'
				, 'Pockey'
				, 'ProPowerBot'
				, 'ProWebWalker'
				, 'Probethenet'
				, 'Psbot'
				, 'Pu_iN'
				, 'Pump'
				, 'PxBroker'
				, 'PyCurl'
				, 'QueryN Metasearch'
				, 'Quick-Crawler'
				, 'RSSingBot'
				, 'RankActive'
				, 'RankActiveLinkBot'
				, 'RankFlex'
				, 'RankingBot'
				, 'RankingBot2'
				, 'Rankivabot'
				, 'RankurBot'
				, 'Re-re'
				, 'ReGet'
				, 'RealDownload'
				, 'Reaper'
				, 'RebelMouse'
				, 'Recorder'
				, 'RedesScrapy'
				, 'RepoMonkey'
				, 'Ripper'
				, 'RocketCrawler'
				, 'Rogerbot'
				, 'SBIder'
				, 'SEOkicks'
				, 'SEOkicks-Robot'
				, 'SEOlyticsCrawler'
				, 'SEOprofiler'
				, 'SEOstats'
				, 'SISTRIX'
				, 'SMTBot'
				, 'SalesIntelligent'
				, 'ScanAlert'
				, 'Scanbot'
				, 'ScoutJet'
				, 'Scrapy'
				, 'Screaming'
				, 'ScreenerBot'
				, 'ScrepyBot'
				, 'Searchestate'
				, 'SearchmetricsBot'
				, 'Seekport'
				, 'SemanticJuice'
				, 'Semrush'
				, 'SemrushBot'
				, 'SentiBot'
				, 'SeoSiteCheckup'
				, 'SeobilityBot'
				, 'Seomoz'
				, 'Shodan'
				, 'Siphon'
				, 'SiteCheckerBotCrawler'
				, 'SiteExplorer'
				, 'SiteLockSpider'
				, 'SiteSnagger'
				, 'SiteSucker'
				, 'Site Sucker'
				, 'Sitebeam'
				, 'Siteimprove'
				, 'Sitevigil'
				, 'SlySearch'
				, 'SmartDownload'
				, 'Snake'
				, 'Snapbot'
				, 'Snoopy'
				, 'SocialRankIOBot'
				, 'Sociscraper'
				, 'Sogou web spider'
				, 'Sosospider'
				, 'Sottopop'
				, 'SpaceBison'
				, 'Spammen'
				, 'SpankBot'
				, 'Spanner'
				, 'Spbot'
				, 'Spinn3r'
				, 'SputnikBot'
				, 'Sqlmap'
				, 'Sqlworm'
				, 'Sqworm'
				, 'Steeler'
				, 'Stripper'
				, 'Sucker'
				, 'Sucuri'
				, 'SuperBot'
				, 'SuperHTTP'
				, 'Surfbot'
				, 'SurveyBot'
				, 'Suzuran'
				, 'Swiftbot'
				, 'Szukacz'
				, 'T0PHackTeam'
				, 'T8Abot'
				, 'Teleport'
				, 'TeleportPro'
				, 'Telesoft'
				, 'Telesphoreo'
				, 'Telesphorep'
				, 'TheNomad'
				, 'The Intraformant'
				, 'Thumbor'
				, 'TightTwatBot'
				, 'Titan'
				, 'Toata'
				, 'Toweyabot'
				, 'Tracemyfile'
				, 'Trendiction'
				, 'Trendictionbot'
				, 'True_Robot'
				, 'Turingos'
				, 'Turnitin'
				, 'TurnitinBot'
				, 'TwengaBot'
				, 'Twice'
				, 'Typhoeus'
				, 'URLy.Warning'
				, 'URLy Warning'
				, 'UnisterBot'
				, 'Upflow'
				, 'V-BOT'
				, 'VB Project'
				, 'VCI'
				, 'Vacuum'
				, 'Vagabondo'
				, 'VelenPublicWebCrawler'
				, 'VeriCiteCrawler'
				, 'VidibleScraper'
				, 'Virusdie'
				, 'VoidEYE'
				, 'Voil'
				, 'Voltron'
				, 'WASALive-Bot'
				, 'WBSearchBot'
				, 'WEBDAV'
				, 'WISENutbot'
				, 'WPScan'
				, 'WWW-Collector-E'
				, 'WWW-Mechanize'
				, 'WWW::Mechanize'
				, 'WWWOFFLE'
				, 'Wallpapers'
				, 'Wallpapers/3.0'
				, 'WallpapersHD'
				, 'WeSEE'
				, 'WebAuto'
				, 'WebBandit'
				, 'WebCollage'
				, 'WebCopier'
				, 'WebEnhancer'
				, 'WebFetch'
				, 'WebFuck'
				, 'WebGo IS'
				, 'WebImageCollector'
				, 'WebLeacher'
				, 'WebPix'
				, 'WebReaper'
				, 'WebSauger'
				, 'WebStripper'
				, 'WebSucker'
				, 'WebWhacker'
				, 'WebZIP'
				, 'Web Auto'
				, 'Web Collage'
				, 'Web Enhancer'
				, 'Web Fetch'
				, 'Web Fuck'
				, 'Web Pix'
				, 'Web Sauger'
				, 'Web Sucker'
				, 'Webalta'
				, 'WebmasterWorldForumBot'
				, 'Webshag'
				, 'WebsiteExtractor'
				, 'WebsiteQuester'
				, 'Website Quester'
				, 'Webster'
				, 'Whack'
				, 'Whacker'
				, 'Whatweb'
				, 'Who.is Bot'
				, 'Widow'
				, 'WinHTTrack'
				, 'WiseGuys Robot'
				, 'Wonderbot'
				, 'Woobot'
				, 'Wotbox'
				, 'Wprecon'
				, 'Xaldon WebSpider'
				, 'Xaldon_WebSpider'
				, 'Xenu'
				, 'YoudaoBot'
				, 'Zade'
				, 'Zauba'
				, 'Zermelo'
				, 'Zeus'
				, 'Zitebot'
				, 'ZmEu'
				, 'ZoomBot'
				, 'ZoominfoBot'
				, 'ZumBot'
				, 'ZyBorg'
				, 'adscanner'
				, 'archive.org_bot'
				, 'arquivo-web-crawler'
				, 'arquivo.pt'
				, 'autoemailspider'
				, 'backlink-check'
				, 'cah.io.community'
				, 'check1.exe'
				, 'clark-crawler'
				, 'coccocbot'
				, 'cognitiveseo'
				, 'com.plumanalytics'
				, 'crawl.sogou.com'
				, 'crawler.feedback'
				, 'crawler4j'
				, 'dataforseo.com'
				, 'demandbase-bot'
				, 'domainsproject.org'
				, 'eCatch'
				, 'evc-batch'
				, 'facebookscraper'
				, 'gopher'
				, 'heritrix'
				, 'instabid'
				, 'internetVista monitor'
				, 'ips-agent'
				, 'isitwp.com'
				, 'iubenda-radar'
				, 'linkdexbot'
				, 'lwp-request'
				, 'lwp-trivial'
				, 'magpie-crawler'
				, 'meanpathbot'
				, 'mediawords'
				, 'muhstik-scan'
				, 'netEstate NE Crawler'
				, 'oBot'
				, 'page scorer'
				, 'pcBrowser'
				, 'plumanalytics'
				, 'polaris version'
				, 'probe-image-size'
				, 'ripz'
				, 's1z.ru'
				, 'satoristudio.net'
				, 'scalaj-http'
				, 'scan.lol'
				, 'seobility'
				, 'seocompany.store'
				, 'seoscanners'
				, 'seostar'
				, 'serpstatbot'
				, 'sexsearcher'
				, 'sitechecker.pro'
				, 'siteripz'
				, 'sogouspider'
				, 'sp_auditbot'
				, 'spyfu'
				, 'sysscan'
				, 'tAkeOut'
				, 'trendiction.com'
				, 'trendiction.de'
				, 'ubermetrics-technologies.com'
				, 'voyagerx.com'
				, 'webgains-bot'
				, 'webmeup-crawler'
				, 'webpros.com'
				, 'webprosbot'
				, 'x09Mozilla'
				, 'x22Mozilla'
				, 'xpymep1.exe'
				, 'zauba.io'
				, 'zgrab'
				, 'petalsearch'
				, 'protopage'
				, 'Miniflux'
				, 'Feeder'
				, 'Semanticbot'
				, 'ImageFetcher'
		);
		
		foreach($bots as $b)
		{
			if( stripos( $agent, $b ) !== false ) return true;
		}
		return false;
	}
	
	
}