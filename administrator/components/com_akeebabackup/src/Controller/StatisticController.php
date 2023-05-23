<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Administrator\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

class StatisticController extends FormController
{
	protected $text_prefix = 'COM_AKEEBABACKUP_BUADMIN';

	public function __construct($config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $app, $input, $formFactory);

		$this->view_list = 'Manage';
		$this->view_item = 'Statistic';
	}

	protected function allowAdd($data = [])
	{
		return false;
	}

	protected function allowEdit($data = [], $key = 'id')
	{
		return Factory::getUser()->authorise('akeebabackup.download', $this->option);
	}

	protected function allowSave($data, $key = 'id')
	{
		return Factory::getUser()->authorise('akeebabackup.download', $this->option);
	}
}