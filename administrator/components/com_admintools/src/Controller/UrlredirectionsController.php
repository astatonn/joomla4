<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Controller;

defined('_JEXEC') or die;

use Akeeba\Component\AdminTools\Administrator\Mixin\ControllerCopyTrait;
use Akeeba\Component\AdminTools\Administrator\Mixin\ControllerCustomACLTrait;
use Akeeba\Component\AdminTools\Administrator\Mixin\ControllerEventsTrait;
use Joomla\CMS\MVC\Controller\AdminController;

class UrlredirectionsController extends AdminController
{
	use ControllerEventsTrait;
	use ControllerCustomACLTrait;
	use ControllerCopyTrait;

	protected $text_prefix = 'COM_ADMINTOOLS_URLREDIRECTIONS';

	public function getModel($name = 'Urlredirection', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}