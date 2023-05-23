<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Administrator\Model;

use DirectoryIterator;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

/**
 * Handles post-installation and upgrade tasks.
 *
 * This model centralises code we previously had scattered around our installation script files. It handles the
 * following tasks that Joomla can't reasonably do by itself:
 *
 * - Migrating form one package name to another when some / all of the included extensions have the same names.
 * - Migrating from FOF-based to Joomla-core-MVC-based extensions.
 * - Enabling plugins and modules upon new installation to provide a better UX without any non-obvious steps.
 * - Downgrading from a Pro to a Core version of an extension.
 * - Executing custom, extension-specific upgrade tasks.
 */
#[\AllowDynamicProperties]
class UpgradeModel extends BaseModel implements DatabaseAwareInterface
{
	use DatabaseAwareTrait;

	/**
	 * Name of the package being replaced
	 *
	 * @var   string
	 */
	private const OLD_PACKAGE_NAME = 'pkg_akeeba';

	/**
	 * Name of the new package this component belongs to
	 *
	 * @var   string
	 */
	private const PACKAGE_NAME = 'pkg_akeebabackup';

	/**
	 * Criteria for determining this is the Pro version by inspecting the filesystem.
	 *
	 * Each array element is an array in itself with two elements:
	 * * 0: const|file|folder
	 * * 1: constant name; or path to the file or folder to check for existence
	 *
	 * Matching any criterion means we have the Pro version
	 *
	 * @var   array
	 */
	private const PRO_CRITERIA = [
		['const', 'AKEEBABACKUP_PRO'],
		['const', 'AKEEBABACKUP_INSTALLATION_PRO'],
		//['folder', JPATH_ADMINISTRATOR . '/components/com_akeebabackup/AliceChecks'],
	];

	/**
	 * Files and folders to remove from both Core and Pro versions
	 *
	 * @var array[]
	 */
	private const REMOVE_FROM_ALL_VERSIONS = [
		'files'   => [
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Model/UsageStatisticsModel.php',

			// Remove iDriveSync — the service has been discontinued
			JPATH_ADMINISTRATOR . 'administrator/components/com_akeebabackup/engine/Postproc/idrivesync.json',
			JPATH_ADMINISTRATOR . 'administrator/components/com_akeebabackup/engine/Postproc/Idrivesync.php',
			JPATH_ADMINISTRATOR . 'administrator/components/com_akeebabackup/engine/Postproc/Connector/Idrivesync.php',

			// Remove Piecon
			JPATH_ADMINISTRATOR . 'media/com_akeebabackup/js/piecon.js',
			JPATH_ADMINISTRATOR . 'media/com_akeebabackup/js/piecon.min.js',
			JPATH_ADMINISTRATOR . 'media/com_akeebabackup/js/piecon.min.js.map',

			// Legacy helpers
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Helper/CacheCleaner.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Helper/ComponentParams.php',

			// Legacy filters
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Stack/StackMyjoomla.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Stack/myjoomla.json',
		],
		'folders' => [
			JPATH_ADMINISTRATOR . 'administrator/components/com_akeebabackup/platform/Joomla/Finalization',

			// Legacy traits
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Controller/Mixin',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Dispatcher/Mixin',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Model/Mixin',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/Table/Mixin',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/View/Mixin',
		],
	];

	/**
	 * Files and folders to remove ONLY from the Core version
	 *
	 * @var array[]
	 */
	private const REMOVE_FROM_CORE = [
		'files'   => [
			// Pro engine features
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Directftp.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/directftp.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Directftpcurl.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/directftpcurl.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Directsftp.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/directsftp.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Directsftpcurl.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/directsftpcurl.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Jps.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/jps.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/Zipnative.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Archiver/zipnative.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Connector/**"',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/amazons3.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Amazons3.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/azure.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Azure.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/backblaze.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Backblaze.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/box.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Box.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/cloudfiles.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Cloudfiles.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/cloudme.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Cloudme.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/dreamobjects.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Dreamobjects.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/dropbox.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Dropbox.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/dropbox2.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Dropbox2.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/ftp.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Ftp.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/ftpcurl.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Ftpcurl.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/googledrive.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Googledrive.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/googlestorage.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Googlestorage.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/googlestoragejson.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Googlestoragejson.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/idrivesync.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Idrivesync.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/onedrive.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Onedrive.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/onedrivebusiness.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Onedrivebusiness.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/ovh.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Ovh.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/pcloud.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Pcloud.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/s3.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/S3.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/sftp.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Sftp.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/sftpcurl.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Sftpcurl.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/sugarsync.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Sugarsync.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/swift.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Swift.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/webdav.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Postproc/Webdav.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Scan/large.json',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/engine/Scan/Large.php',

			// Kickstart, used for Site Transfer Wizard
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/installers/kickstart.txt',

			// Pro features: Controllers
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/AliceController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/DiscoverController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/IncludefoldersController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/MultipledatabasesController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/RegexdatabasefiltersController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/RegexfilefiltersController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/RemoteFilesController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/RestoreController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/ScheduleController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/S3importController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/TransferController.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Controller/UploadController.php',

			// Pro features: Models
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/AliceModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/DiscoverModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/IncludefoldersModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/MultipledatabasesModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/RegexdatabasefiltersModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/RegexfilefiltersModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/RemoteFilesModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/RestoreModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/ScheduleModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/S3importModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/TransferModel.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/Model/UploadModel.php',

			// Pro features: Platform files
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Components.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Extensiondirs.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Extensionfiles.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Languages.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Modules.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Plugins.php',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Filter/Templates.php',

			// Pro features: integrated restoration — Should be removed by Joomla itself
			// JPATH_ADMINISTRATOR . '/components/com_akeebabackup/restore.php',
		],
		'folders' => [
			// Pro features: API application — Should be removed by Joomla itself
			// JPATH_API . '/components/com_akeebabackup',

			// Pro features: ALICE — Should be removed by Joomla itself
			// JPATH_ADMINISTRATOR . '/components/com_akeebabackup/AliceChecks',

			// Pro features: Joomla CLI integration
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/src/CliCommands',

			// Pro features: Views
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Alice',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Discover',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Includefolders',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Multipledatabases',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Regexdatabasefilters',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Regexfilefilters',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/RemoteFiles',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Restore',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Schedule',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/S3import',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Transfer',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/View/Upload',

			// Pro features: View templates
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Alice',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Discover',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Includefolders',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Multipledatabases',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Regexdatabasefilters',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Regexfilefilters',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/RemoteFiles',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Restore',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Schedule',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/S3import',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Transfer',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/tmpl/Upload',

			// Pro features: Platform folders
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Config/Pro',
			JPATH_ADMINISTRATOR . '/components/com_akeebabackup/platform/Joomla/Finalization',

			// Pro features: Frontend controllers — Should be removed by Joomla itself
			// JPATH_SITE . '/src/Controller',

			// Pro features: Frontend models — Should be removed by Joomla itself
			// JPATH_SITE . '/src/Model',
		],
	];

	/** @var string[] Included extensions to automatically publish on a NEW INSTALLATION */
	private const ENABLE_EXTENSIONS = [
		'plg_quickicon_akeebabackup',
		'plg_system_backuponupdate',
	];

	/** @var string[] Included extensions to automatically publish on NEW INSTALLATION OR UPGRADE */
	private const ALWAYS_ENABLE_EXTENSIONS = [
		'plg_console_akeebabackup',
		'plg_task_akeebabackup',
		'plg_webservices_akeebabackup',
	];

	/** @var string[] Extensions to always uninstall if they are still installed (runs on install and upgrade) */
	private const REMOVE_EXTENSIONS = [];

	/** @var string[] Included extensions to be uninstalled when installing the Core version */
	private const PRO_ONLY_EXTENSIONS = [
		'plg_console_akeebabackup',
		'plg_system_backuponupdate',
		'plg_actionlog_akeebabackup',
		'plg_task_akeebabackup',
		'plg_webservices_akeebabackup',
	];

	/** @var string Relative directory to the custom handlers */
	private const CUSTOM_HANDLERS_DIRECTORY = 'UpgradeHandler';

	/**
	 * The database driver.
	 *
	 * @var    DatabaseInterface
	 * @since  9.3.0
	 */
	protected $_db;

	/**
	 * List of extensions included in both old and new packages (if applicable)
	 *
	 * @var   array
	 */
	private $extensionsList;

	/**
	 * Caches the extension names to IDs so we don't query the database too many times.
	 *
	 * @var   array
	 */
	private $extensionIds = [];

	/**
	 * UpgradeModel custom handlers, implementing custom logic for each extension.
	 *
	 * @var object[]
	 */
	private $customHandlers = [];

	public function init()
	{
		// Find out the common extensions
		if ($this->isSamePackage())
		{
			$this->extensionsList = $this->getExtensionsFromPackage(self::PACKAGE_NAME);
		}
		else
		{
			$oldExtensions        = $this->getExtensionsFromPackage(self::OLD_PACKAGE_NAME);
			$newExtensions        = $this->getExtensionsFromPackage(self::PACKAGE_NAME);
			$this->extensionsList = array_intersect($newExtensions, $oldExtensions);
		}

		// Load extension-specific adapters
		$this->loadCustomHandlers();
	}

	/**
	 * Handles the package's post-flight routine
	 *
	 * @param   string               $type    Which action is happening (install|uninstall|discover_install|update)
	 * @param   PackageAdapter|null  $parent  The object responsible for running this script. NULL if running outside
	 *                                        of the package's script.
	 *
	 * @return  bool
	 */
	public function postflight(string $type, ?PackageAdapter $parent = null): bool
	{
		switch ($type)
		{
			// Brand new installation (regular or through Discover)
			case 'install':
			case 'discover_install':
				$this->runIsolated([
					'upgradeFromOldPackage',
					'uninstallExtensions',
					'publishExtensionsOnInstall',
					'publishExtensionsAlways',
					'removeObsoleteFiles',
					'adoptMyExtensions',
				]);

				$this->runCustomHandlerEvent('onInstall', $type, $parent);
				break;

			// Update to a new version
			case 'update':
			default:
				$this->runIsolated([
					'removeObsoleteFiles',
					'publishExtensionsAlways',
					'uninstallExtensions',
					'uninstallProExtensions',
					'adoptMyExtensions',
				]);

				$this->runCustomHandlerEvent('onUpdate', $type, $parent);
				break;

			// Uninstallation
			case 'uninstall':
				$this->runCustomHandlerEvent('onUninstall', $type, $parent);
				break;
		}

		return true;
	}

	/**
	 * Runs an event across all custom handler objects.
	 *
	 * @param   string  $eventName     The name of the event to run
	 * @param   mixed   ...$arguments  Arguments to the event
	 *
	 * @return  array  The results of the custom handler events.
	 */
	public function runCustomHandlerEvent(string $eventName, ...$arguments): array
	{
		$result = [];

		foreach ($this->customHandlers as $adapter)
		{
			if (!method_exists($adapter, $eventName))
			{
				continue;
			}

			try
			{
				$result[] = $adapter->{$eventName}(...$arguments);
			}
			catch (Throwable $e)
			{
				if (defined('JDEBUG') && JDEBUG)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage());
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the extension ID for a Joomla extension given its name.
	 *
	 * This is deliberately public so that custom handlers can use it without having to reimplement it.
	 *
	 * @param   string  $extension  The extension name, e.g. `plg_system_example`.
	 *
	 * @return  int|null  The extension ID or null if no such extension exists
	 */
	public function getExtensionId(string $extension): ?int
	{
		if (isset($this->extensionIds[$extension]))
		{
			return $this->extensionIds[$extension];
		}

		$this->extensionIds[$extension] = null;

		$criteria = $this->extensionNameToCriteria($extension);

		if (empty($criteria))
		{
			return $this->extensionIds[$extension];
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
		            ->select($db->quoteName('extension_id'))
		            ->from($db->quoteName('#__extensions'));

		foreach ($criteria as $key => $value)
		{
			$type = is_numeric($value) ? ParameterType::INTEGER : ParameterType::STRING;
			$type = is_bool($value) ? ParameterType::BOOLEAN : $type;
			$type = is_null($value) ? ParameterType::NULL : $type;

			/**
			 * This is required since $value is passed by reference in bind(). If we do not do this unholy trick the
			 * $value variable is overwritten in the next foreach() iteration, therefore all criteria values will be
			 * equal to the last value iterated. Groan...
			 */
			$varName    = 'queryParam' . ucfirst($key);
			${$varName} = $value;

			$query->where($db->qn($key) . ' = :' . $key)
			      ->bind(':' . $key, ${$varName}, $type);
		}

		try
		{
			$this->extensionIds[$extension] = (int) $db->setQuery($query)->loadResult();
		}
		catch (RuntimeException $e)
		{
			return null;
		}

		return $this->extensionIds[$extension];
	}

	/**
	 * Adopt the extensions by new package.
	 *
	 * This modifies the package_id column of the #__extensions table for the records of the extensions declared in the
	 * new package's manifest. This allows you to use Discover to install new extensions without leaving them “orphan”
	 * of a package in the #__extensions table, something which could cause problems when running Joomla! Update.
	 *
	 * @return  void
	 */
	public function adoptMyExtensions(): void
	{
		// Get the extension ID of the new package
		$newPackageId = $this->getExtensionId(self::PACKAGE_NAME);

		if (empty($newPackageId))
		{
			return;
		}

		// Get the extension IDs
		$extensionIDs = array_map([$this, 'getExtensionId'], $this->getExtensionsFromPackage(self::PACKAGE_NAME));
		$extensionIDs = array_filter($extensionIDs, function ($x) {
			return !empty($x);
		});

		if (empty($extensionIDs))
		{
			return;
		}

		/**
		 * Looks stupid? This realigns the integer keys because whereIn() expects 0-based, monotonically increasing
		 * array keys. Otherwise it ends up emitting null values. GROAN!
		 */
		$extensionIDs = array_merge($extensionIDs);

		// Reassign all extensions
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
		            ->update($db->quoteName('#__extensions'))
		            ->set($db->qn('package_id') . ' = :package_id')
		            ->whereIn($db->qn('extension_id'), $extensionIDs, ParameterType::INTEGER)
		            ->bind(':package_id', $newPackageId, ParameterType::INTEGER);
		$db->setQuery($query)->execute();
	}

	/**
	 * Handle the package upgrade from the old to the new package.
	 *
	 * These versions would also run on Joomla 4 but are replaced with this new package. Since the package name is
	 * different but some of the included extensions are under the same name we need to deal with them. Namely, we need
	 * to:
	 *
	 * * Change the `package_id` in the `#__extensions` table to that of the new `pkg_akeebabackup` package. This is
	 *   currently not used anywhere(?) but it might be the case that Joomla finalyl decides to prevent standalone
	 *   uninstallation of extensions which are part of a package.
	 * * Remove the extensions from the `#__akeeba_common` entries which mark them as dependent on FOF 3.x or 4.x. This
	 *   is so that FOF 3.x / 4.x can be uninstalled when the old package (`pkg_akeeba`) is being uninstalled, since
	 *   these extensions will NOT be removed with it, per the item below.
	 * * Edit the cached XML manifest file of the old `pkg_akeeba` package so that it doesn't try to uninstall the
	 *   extensions it has in common with the new `pkg_akeebabackup` package. Joomla SHOULD figure this out by means of
	 *   the recorded `package_id` in the `#__extensions` table but it currently doesn't seem to have any code to do
	 *   that. Therefore editing the cached XML manifest is the only reasonable way to do this.
	 *
	 * @return  void
	 * @noinspection PhpUnused
	 */
	protected function upgradeFromOldPackage(): void
	{
		if ($this->isSamePackage())
		{
			$this->unregisterFromFOF('3');
			$this->unregisterFromFOF('4');

			return;
		}

		if (!$this->hasOldPackage())
		{
			return;
		}

		$this->reassignExtensions();
		/** @noinspection PhpRedundantOptionalArgumentInspection */
		$this->unregisterFromFOF('3');
		$this->unregisterFromFOF('4');
		$this->removeExtensionsFromPackageManifest();
	}

	/**
	 * Publish a list of extensions.
	 *
	 * Used to publish various plugins when you install the package.
	 *
	 * @return  void
	 */
	protected function publishExtensionsOnInstall(?array $extensionsList = null): void
	{
		$extensionsList = $extensionsList ?? self::ENABLE_EXTENSIONS;
		$extensionIDs   = array_map([$this, 'getExtensionId'], $extensionsList);
		$extensionIDs   = array_filter($extensionIDs, function ($x) {
			return !empty($x);
		});

		if (empty($extensionIDs))
		{
			return;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
		            ->update($db->quoteName('#__extensions'))
		            ->set($db->qn('enabled') . ' = 1')
		            ->whereIn($db->quoteName('extension_id'), $extensionIDs);
		try
		{
			$db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return;
		}
	}

	protected function publishExtensionsAlways()
	{
		$this->publishExtensionsOnInstall(self::ALWAYS_ENABLE_EXTENSIONS);
	}

	/**
	 * Removes obsolete files and folders.
	 *
	 * This is required because Joomla's extensions installer will only check for the top-level files and directories
	 * listed in the XML manifest. Any folders and files deeper than that will not be removed automatically.
	 *
	 * @return  void
	 * @noinspection PhpUnused
	 */
	protected function removeObsoleteFiles(): void
	{
		// We will definitely remove REMOVE_FROM_ALL_VERSIONS in all versions
		$removeSource = self::REMOVE_FROM_ALL_VERSIONS;
		$isPro        = $isPro ?? $this->isPro();

		if (!$isPro)
		{
			$removeSource['files']   = array_merge($removeSource['files'], self::REMOVE_FROM_CORE['files']);
			$removeSource['folders'] = array_merge($removeSource['folders'], self::REMOVE_FROM_CORE['folders']);
		}

		// Remove files
		foreach ($removeSource['files'] as $file)
		{
			if (!is_file($file))
			{
				continue;
			}

			File::delete($file);
		}

		// Remove folders
		foreach ($removeSource['folders'] as $folder)
		{
			$this->deleteFolder($folder);
		}
	}

	/**
	 * Uninstalls the extensions which are marked as always to be uninstalled.
	 *
	 * @return  void
	 * @noinspection PhpUnused
	 */
	protected function uninstallExtensions(): void
	{
		// Tell Joomla to uninstall the extensions always meant to be removed.
		foreach (self::REMOVE_EXTENSIONS as $extension)
		{
			$this->uninstallExtension($extension);
		}
	}

	/**
	 * Uninstalls Pro-only extensions from the Core version of the package.
	 *
	 * @return  void
	 * @noinspection PhpUnused
	 */
	protected function uninstallProExtensions(): void
	{
		// If it's the Pro version we don't uninstall anything.
		if ($this->isPro())
		{
			return;
		}

		// Tell Joomla to uninstall the Pro-only extensions.
		foreach (self::PRO_ONLY_EXTENSIONS as $extension)
		{
			$this->uninstallExtension($extension);
		}
	}

	private function deleteFolder(string $path): bool
	{
		// If the folder does not exist in the requested form return early.
		$hasMixedCase = is_dir($path);

		if (!$hasMixedCase)
		{
			return false;
		}

		// If the folder is all lowercase return early.
		$baseName          = basename($path);
		$lowercaseBaseName = strtolower($baseName);

		if ($baseName === $lowercaseBaseName)
		{
			return $hasMixedCase && Folder::delete($path);
		}

		// We have a mixed case folder. Further investigation necessary.
		$altPath      = dirname($path) . '/' . $lowercaseBaseName;
		$hasLowercase = is_dir($altPath);

		// If the lowercase path does not exist we have a case-sensitive filesystem. Return early.
		if (!$hasLowercase)
		{
			return $hasMixedCase && Folder::delete($path);
		}

		// Both folders exist. Are they the same?
		$testBasename      = UserHelper::genRandomPassword(8) . '.dat';
		$data              = UserHelper::genRandomPassword(32);
		$lowercaseTestFile = $altPath . '/' . $testBasename;
		$uppercaseTestFile = $path . '/' . $testBasename;

		File::write($lowercaseTestFile, $data);

		$readData = file_get_contents($uppercaseTestFile);

		File::delete($lowercaseTestFile);

		// The two folders are different. We have a case-sensitive filesystem. Proceed with deletion.
		if ($readData !== $data)
		{
			return Folder::delete($path);
		}

		/**
		 * The two folders are identical.
		 *
		 * It is impossible to know if the folder is written on disk as lowercase or mixed case. We must rename it to
		 * all lowercase. If we don't, moving the site to a case-sensitive filesystem will break it (the folder will be
		 * in the wrong case!). Therefore we have to do a two-step process to effect the rename on a case-insensitive
		 * filesystem...
		 */
		$intermediateBasename = $lowercaseBaseName . '_' . UserHelper::genRandomPassword(8);
		$intermediatePath     = dirname($path) . '/' . $intermediateBasename;

		Folder::move($path, $intermediatePath);
		Folder::move($intermediatePath, $altPath);

		return false;
	}

	/**
	 * Runs a method inside a try/catch block to suppress any errors
	 *
	 * @param   string[]  $methodNames  The method name to run
	 *
	 * @return  void
	 */
	private function runIsolated(array $methodNames): void
	{
		foreach ($methodNames as $methodName)
		{
			try
			{
				$this->{$methodName}();
			}
			catch (Throwable $e)
			{
				// No problem, let's move on.
			}
		}
	}

	/**
	 * Does the old package even exist?
	 *
	 * @return   bool
	 */
	private function hasOldPackage(): bool
	{
		if (empty(self::OLD_PACKAGE_NAME))
		{
			return false;
		}

		$eid = $this->getExtensionId(self::OLD_PACKAGE_NAME);

		return !empty($eid);
	}

	/**
	 * Reassign the extensions to the new package.
	 *
	 * This modifies the package_id column of the #__extensions table for the records of the records defined in
	 * $this->extensionsList. Since these are shared between the old and new packages we need to change their package ID
	 * to the new package's ID. Otherwise Joomla might be confused as to which package "owns" them.
	 *
	 * @return  void
	 */
	private function reassignExtensions(): void
	{
		// Get the extension ID of the new package
		$newPackageId = $this->getExtensionId(self::PACKAGE_NAME);

		if (empty($newPackageId))
		{
			return;
		}

		// Get the extension IDs
		$extensionIDs = array_map([$this, 'getExtensionId'], $this->extensionsList);
		$extensionIDs = array_filter($extensionIDs, function ($x) {
			return !empty($x);
		});

		if (empty($extensionIDs))
		{
			return;
		}

		/**
		 * Looks stupid? This realigns the integer keys because whereIn() expects 0-based, monotonically increasing
		 * array keys. Otherwise it ends up emitting null values. GROAN!
		 */
		$extensionIDs = array_merge($extensionIDs);

		// Reassign all extensions
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
		            ->update($db->quoteName('#__extensions'))
		            ->set($db->qn('package_id') . ' = :package_id')
		            ->whereIn($db->qn('extension_id'), $extensionIDs, ParameterType::INTEGER)
		            ->bind(':package_id', $newPackageId, ParameterType::INTEGER);
		$db->setQuery($query)->execute();
	}

	/**
	 * Unregisters a list of extensions from being marked as dependent on the specified FOF version.
	 *
	 * @param   string  $fofVersion  PHP version to unregister the extensions from
	 *
	 * @return  void
	 */
	private function unregisterFromFOF($fofVersion = '3')
	{
		// Make sure we have an extensions list and it's canonical (admin modules have mod_ prefix, not amod_).
		$extensions = $this->extensionsList;
		$extensions = array_map(function ($name) {
			if (substr($name, 0, 5) == 'amod_')
			{
				$name = 'mod_' . substr($name, 5);
			}

			return $name;
		}, $extensions);

		// Get the existing list of extensions dependent on the specified version of FOF.
		$keyName = 'fof' . $fofVersion . '0';
		$db      = $this->getDatabase();
		$query   = $db->getQuery(true)
		              ->select($db->quoteName('value'))
		              ->from($db->quoteName('#__akeeba_common'))
		              ->where($db->quoteName('key') . ' = :keyName')
		              ->bind(':keyName', $keyName);
		try
		{
			$json = $db->setQuery($query)->loadResult();
			$list = ($json === null) ? [] : json_decode($json, true);
		}
		catch (RuntimeException $e)
		{
			return;
		}

		// If the list is empty I am already done.
		if (is_null($list) || !is_array($list))
		{
			return;
		}

		// Remove the common extensions which no longer depend on FOF.
		$list = array_diff($list, $extensions);
		$json = json_encode($list);

		// Update the #__akeeba_common table.
		$query = $db->getQuery(true)
		            ->update($db->quoteName('#__akeeba_common'))
		            ->set($db->quoteName('value') . ' = :json')
		            ->where($db->quoteName('key') . ' = :keyName')
		            ->bind(':json', $json)
		            ->bind(':keyName', $keyName);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return;
		}
	}

	/**
	 * Removes the common extensions from old package's cached manifest.
	 *
	 * This prevents Joomla from uninstalling modules, plugins etc which are nominally included in both packages when
	 * you uninstall the old package.
	 *
	 * @return  void
	 */
	private function removeExtensionsFromPackageManifest(): void
	{
		// Make sure we have an old package and a list of extensions
		$oldPackage = self::OLD_PACKAGE_NAME;
		$extensions = $this->extensionsList;

		if (empty($oldPackage) || empty($extensions))
		{
			return;
		}

		// Get the cached manifest as a SimpleXMLElement node
		$xml = $this->getPackageXMLManifest($oldPackage);

		if (is_null($xml))
		{
			return;
		}

		// Walk through all the <file> tags and remove the extensions in the $extensions list
		foreach ($xml->xpath('//files/file') as $fileField)
		{
			$extension = $this->xmlNodeToExtensionName($fileField);

			if (is_null($extension) || !in_array($extension, $extensions))
			{
				continue;
			}

			unset($fileField[0][0]);
		}

		// Save the modified manifest back to the package manifests cache.
		$filePath = $this->getCachedManifestPath($oldPackage);
		$contents = $xml->asXML();

		@file_put_contents($filePath, $contents);
	}

	/**
	 * Gets a SimpleXMLElement representation of the cached manifest of the extension.
	 *
	 * @param   string  $package
	 *
	 * @return  SimpleXMLElement|null
	 */
	private function getPackageXMLManifest(string $package): ?SimpleXMLElement
	{
		$filePath = $this->getCachedManifestPath($package);

		if (!@file_exists($filePath) || !@is_readable($filePath))
		{
			return null;
		}

		$xmlContent = @file_get_contents($filePath);

		if (empty($xmlContent))
		{
			return null;
		}

		return new SimpleXMLElement($xmlContent);
	}

	/**
	 * Get the list of extensions included in a package
	 *
	 * @param   string  $package
	 *
	 * @return  array
	 */
	private function getExtensionsFromPackage(string $package): array
	{
		$extensions = [];
		$xml        = $this->getPackageXMLManifest($package);

		if (is_null($xml))
		{
			return $extensions;
		}

		foreach ($xml->xpath('//files/file') as $fileField)
		{
			$extension = $this->xmlNodeToExtensionName($fileField);

			if (is_null($extension))
			{
				continue;
			}

			$extensions[] = $extension;
		}

		return $extensions;
	}

	/**
	 * Take a SimpleXMLElement `<file>` node of the package manifest and return the corresponding Joomla extension name
	 *
	 * @param   SimpleXMLElement  $fileField  The `<file>` node of the package manifest
	 *
	 * @return  string|null  The extension name, null if it cannot be determined.
	 */
	private function xmlNodeToExtensionName(SimpleXMLElement $fileField): ?string
	{
		$type = (string) $fileField->attributes()->type;
		$id   = (string) $fileField->attributes()->id;

		switch ($type)
		{
			case 'component':
			case 'file':
			case 'library':
				$extension = $id;
				break;

			case 'plugin':
				$group     = (string) $fileField->attributes()->group ?? 'system';
				$extension = 'plg_' . $group . '_' . $id;
				break;

			case 'module':
				$client    = (string) $fileField->attributes()->client ?? 'site';
				$extension = (($client != 'site') ? 'a' : '') . $id;
				break;

			default:
				$extension = null;
				break;
		}

		return $extension;
	}

	/**
	 * Convert a Joomla extension name to `#__extensions` table query criteria.
	 *
	 * The following kinds of extensions are supported:
	 * * `pkg_something` Package type extension
	 * * `com_something` Component
	 * * `plg_folder_something` Plugins
	 * * `mod_something` Site modules
	 * * `amod_something` Administrator modules. THIS IS CUSTOM.
	 * * `file_something` File type extension
	 * * `lib_something` Library type extension
	 *
	 * @param   string  $extensionName
	 *
	 * @return  string[]
	 */
	private function extensionNameToCriteria(string $extensionName): array
	{
		$parts = explode('_', $extensionName, 3);

		switch ($parts[0])
		{
			case 'pkg':
				return [
					'type'    => 'package',
					'element' => $extensionName,
				];

			case 'com':
				return [
					'type'    => 'component',
					'element' => $extensionName,
				];

			case 'plg':
				return [
					'type'    => 'plugin',
					'folder'  => $parts[1],
					'element' => $parts[2],
				];

			case 'mod':
				return [
					'type'      => 'module',
					'element'   => $extensionName,
					'client_id' => 0,
				];

			// That's how we note admin modules
			case 'amod':
				return [
					'type'      => 'module',
					'element'   => substr($extensionName, 1),
					'client_id' => 1,
				];

			case 'file':
				return [
					'type'    => 'file',
					'element' => $extensionName,
				];

			case 'lib':
				return [
					'type'    => 'library',
					'element' => $parts[1],
				];
		}

		return [];
	}

	/**
	 * Get the absolute filesystem path
	 *
	 * @param   string  $package
	 *
	 * @return  string
	 */
	private function getCachedManifestPath(string $package): string
	{
		return JPATH_MANIFESTS . '/packages/' . $package . '.xml';
	}

	/**
	 * Is this the Pro version?
	 *
	 * This is determined by examining the constants, files and folders defined in self::PRO_CRITERIA
	 *
	 * @return  bool
	 * @see     self::PRO_CRITERIA
	 */
	private function isPro(): bool
	{
		if (empty(self::PRO_CRITERIA))
		{
			return false;
		}

		foreach (self::PRO_CRITERIA as $criterion)
		{
			[$type, $value] = $criterion;

			switch ($type)
			{
				case 'const':
				case 'constant':
					if (!defined($value))
					{
						continue 2;
					}

					if (constant($value))
					{
						return true;
					}

					break;

				case 'folder':
					if (@file_exists($value) && @is_dir($value))
					{
						return true;
					}
					break;

				case 'file':
					if (@file_exists($value) && @is_file($value))
					{
						return true;
					}
					break;

				default:
					continue 2;
			}
		}

		return false;
	}

	/**
	 * Uninstall an extension by name.
	 *
	 * @param   string  $extension
	 *
	 * @return  bool
	 */
	private function uninstallExtension(string $extension): bool
	{
		// Let's get the extension ID. If it's not there we can't uninstall this extension, right..?
		$eid = $this->getExtensionId($extension);

		if (empty($eid))
		{
			return false;
		}

		// Get an Extension table object and Installer object.
		$row       = new Extension($this->getDatabase());
		$installer = Installer::getInstance();

		// Load the extension row or fail the uninstallation immediately.
		try
		{
			if (!$row->load($eid))
			{
				return false;
			}
		}
		catch (Throwable $e)
		{
			// If the database query fails or Joomla experiences an unplanned rapid deconstruction let's bail out.
			return false;
		}

		// Can't uninstalled protected extensions
		/** @noinspection PhpUndefinedFieldInspection */
		if ((int) $row->locked === 1)
		{
			return false;
		}

		// An extension row without a type? What have you done to your database, you MONSTER?!
		if (empty($row->type))
		{
			return false;
		}

		// Do the actual uninstallation. Try to trap any errors, just in case...
		try
		{
			return $installer->uninstall($row->type, $eid);
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	/**
	 * Loads any custom handlers.
	 *
	 * @return  void
	 */
	private function loadCustomHandlers(): void
	{
		$handlerNamespace = __NAMESPACE__ . '\\' . self::CUSTOM_HANDLERS_DIRECTORY;

		$this->customHandlers = [];

		// Scan the directory and load the custom handlers
		$targetDirectory = __DIR__ . '/' . self::CUSTOM_HANDLERS_DIRECTORY;

		if (!@file_exists($targetDirectory) || !@is_dir($targetDirectory))
		{
			return;
		}

		$di = new DirectoryIterator($targetDirectory);

		/** @var DirectoryIterator $entry */
		foreach ($di as $entry)
		{
			// Ignore folders
			if ($entry->isDot() || $entry->isDir())
			{
				continue;
			}

			// Ignore non-PHP directories
			if ($entry->getExtension() != 'php')
			{
				continue;
			}

			// Get the class name
			$bareName          = basename($entry->getFilename(), '.php');
			$bareNameCanonical = preg_replace('/[^A-Z_]/i', '', $bareName);

			/**
			 * Some hosts rename files with numeric suffixes, e.g. FooBar.php is renamed to FooBar.01.php. In both cases
			 * the bare class name would be "FooBar" but the canonical would be "FooBar" vs "FooBar.01". This check
			 * makes sure that renamed files will NOT be loaded. Ever.
			 */
			if ($bareName != $bareNameCanonical)
			{
				continue;
			}

			// Have I already loaded an object this class? Yeah, sometimes hosts do weird(er) things.
			if (array_key_exists($bareNameCanonical, $this->customHandlers))
			{
				continue;
			}

			// Try to load the file
			require_once $entry->getPathname();

			// Make sure we actually loaded a class I can use
			$classFQN = $handlerNamespace . '\\' . $bareNameCanonical;

			if (!class_exists($classFQN, false))
			{
				continue;
			}

			// Add the custom handler, passing a reference to ourselves
			$this->customHandlers[$bareNameCanonical] = new $classFQN($this, $this->getDatabase());
		}
	}

	/**
	 * Are the old and new packages identical?
	 *
	 * Also returns true if no OLD_PACKAGE_NAME has been specified.
	 *
	 * @return  bool
	 */
	private function isSamePackage(): bool
	{
		return empty(self::OLD_PACKAGE_NAME) || (self::OLD_PACKAGE_NAME === self::PACKAGE_NAME);
	}
}