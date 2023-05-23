<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Utilities\IpHelper;

#[\AllowDynamicProperties]
class EmergencyofflineModel extends BaseModel
{
	/**
	 * Checks if the Emergency Off-Line Mode .htaccess backup exists
	 *
	 * @return  bool
	 */
	public function isOffline(): bool
	{
		$backupFile = JPATH_SITE . '/.htaccess.eom';

		if (!File::exists($backupFile))
		{
			return false;
		}

		$filedata = @file_get_contents($backupFile) ?: '';
		$lines    = explode("\n", $filedata) ?: [''];

		return trim($lines[0]) == '## EOMBAK - Do not remove this line or this file';
	}

	/**
	 * Tries to put the site in Emergency Off-Line Mode, backing up the original .htaccess file
	 *
	 * @return  bool True on success
	 */
	public function putOffline(): bool
	{
		// If the backup doesn't exist, try to create it
		$htaccessFilePath = JPATH_SITE . '/.htaccess';

		if (!$this->isOffline())
		{
			$backupFile = JPATH_SITE . '/.htaccess.eom';
			$sourceFile = $htaccessFilePath;

			$sourceData = @file_get_contents($sourceFile) ?: '';
			$sourceData = "## EOMBAK - Do not remove this line or this file\n" . $sourceData;
			$result     = File::write($backupFile, $sourceData);

			if (!$result)
			{
				return false;
			}

			if (@file_exists($sourceFile))
			{
				File::delete($sourceFile);
			}
		}

		// Create the offline.html file, if it doesn't exist. If you can't create it, don't worry too much.
		$offlineFile = JPATH_SITE . '/offline.html';

		if (!@file_exists($offlineFile))
		{
			$app      = Factory::getApplication();
			$message  = $app->get('offline_message');
			$sitename = $app->get('sitename');
			$langTag  = $app->getLanguage()->getTag();

			$fileContents = <<<ENDHTML
<html lang="$langTag">
<head>
	<title>$sitename</title>
	<meta name="color-scheme" content="light dark">
	<style>
		body{font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"}h1{font-size:1.5em;padding-bottom:0.5em;border-bottom:thin solid rgba(0,0,0,.1)}.container{padding:0;height:99vh;width:99vw;display:flex;justify-content:center;align-items:center}.banner{min-width:100px;max-width:800px;text-align:center;background:rgba(0,0,0,.2);padding:1em 3em;border:thin solid rgba(0,0,0,.3);border-radius:1em}@media (prefers-color-scheme: dark){h1{border-color:rgba(255,255,255,.3)}.banner{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1)}}
    </style>
</head>
<body>
<div class="container">
	<div class="banner">
		<h1>
			$sitename
		</h1>
		<p>
			$message
		</p>
	</div>
</div>
</body>
</html>
ENDHTML;

			File::write($offlineFile, $fileContents);
		}

		$htaccess = $this->getHtaccess();

		return File::write($htaccessFilePath, $htaccess);
	}

	/**
	 * Puts the site back on-line
	 *
	 * @return  bool  True on success
	 */
	public function putOnline(): bool
	{
		if (!$this->isOffline())
		{
			return false;
		}

		$htaccessPath    = JPATH_SITE . '/.htaccess';
		$oldHtaccessPath = JPATH_SITE . '/.htaccess.eom';

		if (@file_exists($htaccessPath) && !File::delete($htaccessPath))
		{
			return false;
		}

		if (!@file_exists($oldHtaccessPath))
		{
			return true;
		}

		$filedata = implode("\n", array_filter(@file($oldHtaccessPath) ?: [], function ($line) {
			return $line != "## EOMBAK - Do not remove this line or this file";
		}));

		if (File::write($htaccessPath, $filedata))
		{
			return File::delete($oldHtaccessPath);
		}

		return false;
	}

	/**
	 * Returns the contents of the stealthy .htaccess file
	 *
	 * @return string
	 */
	public function getHtaccess()
	{
		// Sniff the .htaccess for a RewriteBase line
		$rewriteBase = '';
		$sourceFile  = JPATH_SITE . '/.htaccess.eom';
		$sourceFile  = @file_exists($sourceFile) ? $sourceFile : JPATH_SITE . '/.htaccess';

		if (@file_exists($sourceFile))
		{
			$sourceData = @file($sourceFile);

			foreach ($sourceData as $line)
			{
				$line = trim($line);

				if (substr($line, 0, 12) == 'RewriteBase ')
				{
					$rewriteBase = $line;

					break;
				}
			}
		}

		// And finally create our stealth .htaccess
		$ip = IpHelper::getIp();
		$ip = str_replace('.', '\\.', $ip);

		return <<<HTACCESS
RewriteEngine On
$rewriteBase
RewriteCond %{REMOTE_ADDR}        !$ip
RewriteCond %{REQUEST_URI}        !offline\.html
RewriteCond %{REQUEST_URI}        !(\.png|\.jpg|\.gif|\.jpeg|\.bmp|\.swf|\.css|\.js)$
RewriteRule (.*)                  offline.html    [R=307,L]

HTACCESS;
	}
}