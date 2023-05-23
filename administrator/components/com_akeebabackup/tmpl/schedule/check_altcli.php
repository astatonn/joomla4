<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\Component\AkeebaBackup\Administrator\View\Schedule\HtmlView $this */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Language\Text;

?>
<div class="card mb-3">
	<h3 class="card-header">
		<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_ALTCLICRON') ?>
	</h3>

	<div class="card-body">
		<?php if(!$this->checkinfo->info->legacyapi): ?>
			<div class="alert alert-danger">
				<p>
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_LEGACYAPI_DISABLED') ?>
				</p>
				<p>
					<a href="<?= $this->enableLegacyFrontendURL ?>" class="btn btn-primary">
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_BTN_ENABLE_LEGACYAPI') ?>
					</a>
				</p>
			</div>
		<?php elseif(!trim($this->checkinfo->info->secret)): ?>
			<div class="alert alert-danger">
				<p>
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_SECRET') ?>
				</p>
				<p>
					<a href="<?= $this->resetSecretWordURL ?>" class="btn btn-primary">
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_BTN_RESET_SECRETWORD') ?>
					</a>
				</p>
			</div>
		<?php else: ?>
			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_GENERICUSECLI') ?>
				<code>
					<?= $this->escape($this->checkinfo->info->php_path); ?>
					<?= $this->escape($this->checkinfo->altcli->path); ?>
				</code>
			</p>
			<p>
				<span class="badge bg-warning">
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CLIGENERICIMPROTANTINFO') ?>
				</span>
				<?= Text::sprintf('COM_AKEEBABACKUP_SCHEDULE_LBL_CLIGENERICINFO', $this->checkinfo->info->php_path) ?>
			</p>
		<?php endif ?>
	</div>
</div>
