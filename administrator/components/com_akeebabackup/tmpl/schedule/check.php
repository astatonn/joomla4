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
<h2>
    <?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CHECK_BACKUPS') ?>
</h2>

<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_CHECKHEADERINFO') ?>

<?php //  CLI CRON jobs ?>
<?= $this->loadAnyTemplate('schedule/check_cli') ?>

<?php // Alternate CLI CRON jobs (using legacy front-end) ?>
<?= $this->loadAnyTemplate('schedule/check_altcli') ?>

<?php // Legacy front-end backup ?>
<?= $this->loadAnyTemplate('schedule/check_legacy') ?>
