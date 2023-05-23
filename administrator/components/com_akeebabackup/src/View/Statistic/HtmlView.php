<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Administrator\View\Statistic;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Model\BannerModel;

#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 *
	 */
	public function display($tpl = null): void
	{
		/** @var BannerModel $model */
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
		$this->state = $model->getState();

		// Check for errors.
		$errors = $this->get('Errors');

		if (
			(is_array($errors) || $errors instanceof \Countable)
				? count($errors)
				: 0
		)
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::title(Text::_('COM_AKEEBABACKUP_BUADMIN_LOG_EDITCOMMENT'), 'icon-akeeba');

		$toolbar = Toolbar::getInstance();

		// If not checked out, can save the item.
		$toolbar->apply('statistic.apply');
		$toolbar->save('statistic.save');

		$toolbar->cancel('statistic.cancel');

		$toolbar->divider();
		$toolbar->help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-joomla/adminsiter-backup-files.html');
	}
}