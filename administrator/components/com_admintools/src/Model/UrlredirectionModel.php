<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Model;

defined('_JEXEC') or die;

use Akeeba\Component\AdminTools\Administrator\Mixin\ModelCopyTrait;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Uri\Uri;

#[\AllowDynamicProperties]
class UrlredirectionModel extends AdminModel
{
	use ModelCopyTrait;

	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $formFactory);

		$this->_parent_table = '';
	}

	/**
	 * @inheritDoc
	 */
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm(
			'com_admintools.urlredirection',
			'urlredirection',
			[
				'control'   => 'jform',
				'load_data' => $loadData,
			]
		) ?: false;

		if (empty($form))
		{
			return false;
		}

		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.edit', 'com_admintools'))
		{
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'required', 'false');
			$form->setFieldAttribute('published', 'validate', 'unset');
		}

		/**
		 * Prefix the “Visiting this” with the absolute URL to the site root, e.g. ‘https://www.example.com/mysite/’
		 * for clarity
		 */
		$form->setFieldAttribute('dest', 'addonBefore', rtrim(Uri::root(false), '/') . '/');

		return $form;
	}

	protected function loadFormData()
	{
		/** @var CMSApplication $app */
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_admintools.edit.urlredirection.data', []);
		$pk   = (int) $this->getState($this->getName() . '.id');
		$item = ($pk ? $this->getItem() : false) ?: [];

		$data = $data ?: $item;

		$this->preprocessData('com_admintools.urlredirection', $data);

		return $data;
	}
}