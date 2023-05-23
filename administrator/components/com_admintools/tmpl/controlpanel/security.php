<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var  \Akeeba\Component\AdminTools\Administrator\View\Controlpanel\HtmlView $this */
?>

<div class="card mb-3">
	<h3 class="card-header bg-primary text-white">
		<?=Text::_('COM_ADMINTOOLS_CONTROLPANEL_LBL_SECURITY'); ?>
	</h3>

	<div class="card-body d-flex flex-row flex-wrap align-items-stretch">
		<?php if(ADMINTOOLS_PRO && $this->needsQuickSetup): ?>
			<a class="text-center align-self-stretch btn btn-outline-warning text-dark border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Quickstart') ?>">
				<div class="bg-warning d-block text-center p-3 h2">
					<span class="fa fa-bolt"></span>
				</div>
				<span>
				<?= Text::_('COM_ADMINTOOLS_TITLE_QUICKSTART') ?>
			</span>
			</a>
		<?php endif; ?>

		<?php if ($this->htMakerSupported): ?>
			<a class="text-center align-self-stretch btn btn-outline-danger border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Emergencyoffline') ?>">
				<div class="bg-danger text-white d-block text-center p-3 h2">
					<span class="fa fa-power-off"></span>
				</div>
				<span>
					<?= Text::_('COM_ADMINTOOLS_TITLE_EMERGENCYOFFLINE') ?>
				</span>
			</a>
		<?php endif; ?>

		<a class="text-center align-self-stretch btn btn-outline-warning text-dark border-0" style="width: 10em"
		   href="<?= Route::_('index.php?option=com_admintools&view=Mainpassword') ?>">
			<div class="bg-warning d-block text-center p-3 h2">
				<span class="fa fa-lock"></span>
			</div>
			<span>
				<?= Text::_('COM_ADMINTOOLS_TITLE_MAINPASSWORD') ?>
			</span>
		</a>

		<?php if ($this->htMakerSupported): ?>
			<a class="text-center align-self-stretch btn btn-outline-warning text-dark border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Adminpassword') ?>">
				<div class="bg-warning d-block text-center p-3 h2">
					<span class="fa fa-<?= $this->adminLocked ? 'lock' : 'lock-open' ?>"></span>
				</div>
				<span>
					<?= Text::_('COM_ADMINTOOLS_TITLE_ADMINPASSWORD') ?>
				</span>
			</a>
		<?php endif; ?>

		<?php if($this->isPro): ?>
			<?php if($this->htMakerSupported): ?>
				<a class="text-center align-self-stretch btn btn-outline-primary border-0" style="width: 10em"
				   href="<?= Route::_('index.php?option=com_admintools&view=Htaccessmaker') ?>">
					<div class="bg-primary text-white d-block text-center p-3 h2">
						<span class="fa fa-file-alt"></span>
					</div>
					<span>
						<?= Text::_('COM_ADMINTOOLS_TITLE_HTACCESSMAKER') ?>
					</span>
				</a>
			<?php endif; ?>

			<?php if($this->nginxMakerSupported): ?>
				<a class="text-center align-self-stretch btn btn-outline-primary border-0" style="width: 10em"
				   href="<?= Route::_('index.php?option=com_admintools&view=Nginxconfmaker') ?>">
					<div class="bg-primary text-white d-block text-center p-3 h2">
						<span class="fa fa-file-alt"></span>
					</div>
					<span>
						<?= Text::_('COM_ADMINTOOLS_TITLE_NGINXCONFMAKER') ?>
					</span>
				</a>
			<?php endif; ?>

			<?php if($this->webConfMakerSupported): ?>
				<a class="text-center align-self-stretch btn btn-outline-primary border-0" style="width: 10em"
				   href="<?= Route::_('index.php?option=com_admintools&view=Webconfigmaker') ?>">
					<div class="bg-primary text-white d-block text-center p-3 h2">
						<span class="fa fa-file-alt"></span>
					</div>
					<span>
						<?= Text::_('COM_ADMINTOOLS_TITLE_WEBCONFIGMAKER') ?>
					</span>
				</a>
			<?php endif; ?>

			<a class="text-center align-self-stretch btn btn-outline-dark border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Webapplicationfirewall') ?>">
				<div class="bg-dark text-white d-block text-center p-3 h2">
					<span class="fa fa-times-circle"></span>
				</div>
				<span>
					<?= Text::_('COM_ADMINTOOLS_TITLE_WAF') ?>
				</span>
			</a>

			<a class="text-center align-self-stretch btn btn-outline-dark border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Scans') ?>">
				<div class="bg-dark text-white d-block text-center p-3 h2">
					<span class="fa fa-search"></span>
				</div>
				<span>
					<?= Text::_('COM_ADMINTOOLS_TITLE_SCANS') ?>
				</span>
			</a>

			<a class="text-center align-self-stretch btn btn-outline-dark border-0" style="width: 10em"
			   href="<?= Route::_('index.php?option=com_admintools&view=Schedulinginformation') ?>">
				<div class="bg-dark text-white d-block text-center p-3 h2">
					<span class="fa fa-calendar-alt"></span>
				</div>
				<span>
					<?= Text::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION') ?>
				</span>
			</a>

		<?php endif; ?>
	</div>
</div>
