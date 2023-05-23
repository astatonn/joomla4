<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Table;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

/**
 * @property int    $id            Unique ID
 * @property string $source        When visiting this URL Path
 * @property string $dest          You are redirected to this URL or URL Path
 * @property int    $ordering      Ordering
 * @property int    $published     Is this published?
 * @property string $created           Created date and time
 * @property int    $created_by        Created by this user
 * @property string $modified          Modified date and time
 * @property int    $modified_by       Modified by this user
 * @property int    $checked_out       Checked out by this user
 * @property string $checked_out_time  Checked out date and time
 * @property int    $keepurlparams Should I keep the URL params (default) or quash them?
 */
class UrlredirectionTable extends AbstractTable
{
	public function __construct(DatabaseDriver $db, DispatcherInterface $dispatcher = null)
	{
		parent::__construct('#__admintools_redirects', 'id', $db, $dispatcher);
	}

	protected function onBeforeCheck()
	{
		if (!$this->source)
		{
			throw new Exception(Text::_('COM_ADMINTOOLS_REDIRECTION_ERR_NEEDS_SOURCE'));
		}

		if (!$this->dest)
		{
			throw new Exception(Text::_('COM_ADMINTOOLS_REDIRECTION_ERR_NEEDS_DEST'));
		}

		if (empty($this->published) && ($this->published !== 0))
		{
			$this->published = 0;
		}

		if (is_null($this->keepurlparams))
		{
			$this->keepurlparams = 1;
		}
	}
}