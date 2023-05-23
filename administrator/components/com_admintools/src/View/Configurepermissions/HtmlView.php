<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\View\Configurepermissions;

defined('_JEXEC') or die;

use Akeeba\Component\AdminTools\Administrator\Helper\Storage;
use Akeeba\Component\AdminTools\Administrator\Mixin\ViewLoadAnyTemplateTrait;
use Akeeba\Component\AdminTools\Administrator\Mixin\ViewTaskBasedEventsTrait;
use Akeeba\Component\AdminTools\Administrator\Model\ConfigurepermissionsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	use ViewLoadAnyTemplateTrait;
	use ViewTaskBasedEventsTrait;

	/**
	 * Default permissions for directories
	 *
	 * @var  string
	 */
	public $dirperms;

	/**
	 * Default permissions for files
	 *
	 * @var  string
	 */
	public $fileperms;

	/**
	 * Filesystem listing
	 *
	 * @var  array
	 */
	public $listing;

	/**
	 * Current path
	 *
	 * @var  string
	 */
	public $currentPath;

	/**
	 * Should I display hidden (dot) files?
	 *
	 * @var bool
	 */
	public $perms_show_hidden;

	protected function onBeforeMain()
	{
		// Default permissions
		$params = Storage::getInstance();

		$dirperms  = '0' . ltrim(trim($params->getValue('dirperms', '0755')), '0');
		$fileperms = '0' . ltrim(trim($params->getValue('fileperms', '0644')), '0');

		$dirperms = octdec($dirperms);

		if (($dirperms < 0600) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}

		$this->dirperms = '0' . decoct($dirperms);

		$fileperms = octdec($fileperms);

		if (($fileperms < 0600) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}

		$this->fileperms = '0' . decoct($fileperms);

		// File lists
		/** @var ConfigurepermissionsModel $model */
		$model         = $this->getModel();
		$listing       = $model->getListing();
		$this->listing = $listing;

		$relpath           = $model->getState('filter_path', '');
		$this->currentPath = $relpath;

		$this->perms_show_hidden = $params->getValue('perms_show_hidden', 0);

		$this->document->getWebAssetManager()
			->useScript('com_admintools.configure_permissions');

		ToolbarHelper::title(sprintf(Text::_('COM_ADMINTOOLS_TITLE_CONFIGUREPERMISSIONS')), 'icon-admintools');
		ToolbarHelper::back('COM_ADMINTOOLS_TITLE_CONTROLPANEL', 'index.php?option=com_admintools');

		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/admin-tools-joomla/fixing-permissions.html#fixpermsconfig');
	}

	protected function renderPermissions($perms)
	{
		if ($perms === false)
		{
			return '—';
		}

		return decoct($perms & 0777);
	}

	protected function renderUGID($uid, $gid)
	{
		static $users = [];
		static $groups = [];

		$user  = '&mdash;';
		$group = '&mdash;';

		if ($uid !== false)
		{
			if (!array_key_exists($uid, $users))
			{
				$users[$uid] = $uid;

				if (function_exists('posix_getpwuid'))
				{
					$uArray      = posix_getpwuid($uid);
					$users[$uid] = $uArray['name']; //." ($uid)";
				}
			}

			$user = $users[$uid];
		}

		if ($gid !== false)
		{
			if (!array_key_exists($gid, $groups))
			{
				$groups[$gid] = $gid;

				if (function_exists('posix_getgrgid'))
				{
					$gArray       = posix_getgrgid($gid);
					$groups[$gid] = $gArray['name']; //." ($gid)";
				}
			}

			$group = $groups[$gid];
		}

		return "$user:$group";
	}
}