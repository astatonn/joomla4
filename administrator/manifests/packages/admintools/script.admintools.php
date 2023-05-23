<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @noinspection PhpUnused */

defined('_JEXEC') || die;

use Akeeba\Component\AdminTools\Administrator\Helper\TemplateEmails;
use Akeeba\Component\AdminTools\Administrator\Model\UpgradeModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

/**
 * Admin Tools package extension installation script file.
 *
 * @see https://docs.joomla.org/Manifest_files#Script_file
 * @see \Akeeba\Component\AdminTools\Administrator\Model\UpgradeModel
 */
class Pkg_AdmintoolsInstallerScript extends InstallerScript
{
	/**
	 * @since 7.2.0
	 * @var   DatabaseDriver|DatabaseInterface|null
	 */
	protected $dbo;

	protected $minimumPhp = '7.4.0';

	protected $minimumJoomla = '4.2.0';

	/**
	 * @param   string          $type
	 * @param   PackageAdapter  $parent
	 *
	 * @return  bool
	 *
	 * @since   7.0.0
	 */
	public function preflight($type, $parent)
	{
		if (!parent::preflight($type, $parent))
		{
			return false;
		}

		$this->setDboFromAdapter($parent);

		// Do not run on uninstall.
		if ($type === 'uninstall')
		{
			return true;
		}

		define('ADMINTOOLS_INSTALLATION_PRO', is_file($parent->getParent()->getPath('source') . '/com_admintools-pro.zip'));

		// Prevent users from installing this on Joomla 3
		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			$msg = "<p>This version of Admin Tools cannot run on Joomla 3. Please download and install Admin Tools 6 instead. Kindly note that our site's Downloads page clearly indicates which version of our software is compatible with Joomla 3 and which version is compatible with Joomla 4.</p>";

			Log::add($msg, Log::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Called after any type of installation / uninstallation action.
	 *
	 * @param   string          $type    Which action is happening (install|uninstall|discover_install|update)
	 * @param   PackageAdapter  $parent  The object responsible for running this script
	 *
	 * @return  bool
	 * @since   7.0.0
	 */
	public function postflight($type, $parent)
	{
		// Do not run on uninstall.
		if ($type === 'uninstall')
		{
			return true;
		}

		$this->setDboFromAdapter($parent);

		$model = $this->getUpgradeModel();

		if (!empty($model))
		{
			try
			{
				if (!$model->postflight($type, $parent))
				{
					return false;
				}
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		$this->updateEmails();

		return true;
	}

	/**
	 * Get the UpgradeModel of the installed component
	 *
	 * @return  UpgradeModel|null  The upgrade Model. NULL if it cannot be loaded.
	 * @since   7.0.0
	 */
	private function getUpgradeModel(): ?UpgradeModel
	{
		// Make sure the latest version of the Model file will be loaded, regardless of the OPcache state.
		$filePath = JPATH_ADMINISTRATOR . '/components/com_admintools/src/Model/UpgradeModel.php';

		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($filePath);
		}

		// Can I please load the model?
		if (!class_exists('\Akeeba\Component\AdminTools\Administrator\Model\UpgradeModel'))
		{
			if (!file_exists($filePath) || !is_readable($filePath))
			{
				return null;
			}

			/** @noinspection PhpIncludeInspection */
			include_once $filePath;
		}

		if (!class_exists('\Akeeba\Component\AdminTools\Administrator\Model\UpgradeModel'))
		{
			return null;
		}

		try
		{
			$upgradeModel = new UpgradeModel();
		}
		catch (Throwable $e)
		{
			return null;
		}

		if (method_exists($upgradeModel, 'setDatabase'))
		{
			$upgradeModel->setDatabase($this->dbo ?? Factory::getContainer()->get('DatabaseDriver'));
		}
		elseif (method_exists($upgradeModel, 'setDbo'))
		{
			$upgradeModel->setDbo($this->dbo ?? Factory::getContainer()->get('DatabaseDriver'));
		}

		if (method_exists($upgradeModel, 'init'))
		{
			$upgradeModel->init();
		}

		return $upgradeModel;
	}

	private function updateEmails(): void
	{
		// Make sure the latest version of the Helper file will be loaded, regardless of the OPcache state.
		$filePath = JPATH_ADMINISTRATOR . '/components/com_admintools/src/Helper/TemplateEmails.php';

		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($filePath);
		}

		if (!class_exists('\Akeeba\Component\AdminTools\Administrator\Helper\TemplateEmails'))
		{
			if (!file_exists($filePath) || !is_readable($filePath))
			{
				return;
			}

			/** @noinspection PhpIncludeInspection */
			include_once $filePath;
		}

		if (!class_exists('\Akeeba\Component\AdminTools\Administrator\Helper\TemplateEmails'))
		{
			return;
		}

		try
		{
			TemplateEmails::updateAllTemplates();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Set the database object from the installation adapter, if possible
	 *
	 * @param   InstallerAdapter|mixed  $adapter  The installation adapter, hopefully.
	 *
	 * @since   7.2.0
	 * @return  void
	 */
	private function setDboFromAdapter($adapter): void
	{
		$this->dbo = null;

		if (class_exists(InstallerAdapter::class) && ($adapter instanceof InstallerAdapter))
		{
			/**
			 * If this is Joomla 4.2+ the adapter has a protected getDatabase() method which we can access with the
			 * magic property $adapter->db. On Joomla 4.1 and lower this is not available. So, we have to first figure
			 * out if we can actually use the magic property...
			 */

			try
			{
				$refObj = new ReflectionObject($adapter);

				if ($refObj->hasMethod('getDatabase'))
				{
					$this->dbo = $adapter->db;

					return;
				}
			}
			catch (Throwable $e)
			{
				// If something breaks we will fall through
			}
		}

		$this->dbo = Factory::getContainer()->get('DatabaseDriver');
	}
}