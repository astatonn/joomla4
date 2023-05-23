<?php
/**
 * @author     Mathew Lenning <mathew.lenning@gmail.com>
 * @authorUrl  http://mathewlenning.com
 * @copyright  Copyright (C) 2015 Mathew Lenning. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class PlgSystemGotoadmin
 *
 * @since  0.0.1
 */
class PlgSystemGotoadmin extends JPlugin
{
	/**
	 * Method to check if the person has set the redirect variable to the specified value
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		/** @var Joomla\CMS\Application\AdministratorApplication $app */
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$input = $app->input;
		$redirect = $this->params->get('redirect', JUri::root(true));
		$varName = $this->params->get('redirect_var', 'gtky_key');
		$varValue = $this->params->get('redirect_val', 1);

		$inputVal = $input->get($varName, null);

		$isUnknowGuest = ($user->guest && (empty($inputVal) || $inputVal != $varValue));

		$shouldRedirect = (!$app->isClient('site') && !empty($redirect));

		$isLogin = ($input->get('option') === 'com_login' && $input->get('task') === 'login');

		if ($isUnknowGuest && $shouldRedirect && !$isLogin)
		{
			$app->redirect($redirect);
			$app->close();
		}
	}

}
