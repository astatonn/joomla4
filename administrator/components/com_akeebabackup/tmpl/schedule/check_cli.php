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
		<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CLICRON') ?>
	</h3>

	<div class="card-body">
		<?php if (!$this->isConsolePluginEnabled): ?>
			<div class="alert alert-danger">
				<h3 class="alert-header">
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CONSOLEPLUGINDISALBED_HEAD') ?>
				</h3>
				<p>
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CONSOLEPLUGINDISALBED_BODY') ?>
				</p>
			</div>
		<?php else: ?>
			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_GENERICUSECLI') ?>
				<code>
					<?= $this->escape($this->checkinfo->info->php_path); ?>
					<?= $this->escape($this->checkinfo->cli->path); ?>
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
