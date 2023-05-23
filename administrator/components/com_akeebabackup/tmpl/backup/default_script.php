<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * This file passes parameters to the Backup.js script using Joomla's script options API
 *
 * @var  $this  \Akeeba\Component\AkeebaBackup\Administrator\View\Backup\HtmlView
 */

$escapedBaseURL = addslashes(Uri::base());

// Initialization
$this->document->addScriptOptions('akeebabackup.Backup.defaultDescription', addslashes($this->defaultDescription));
$this->document->addScriptOptions('akeebabackup.Backup.currentDescription', addslashes(empty($this->description) ? $this->defaultDescription : $this->description));
$this->document->addScriptOptions('akeebabackup.Backup.currentComment', addslashes($this->comment));
$this->document->addScriptOptions('akeebabackup.Backup.hasAngieKey', $this->hasANGIEPassword);

// Auto-resume setup
$this->document->addScriptOptions('akeebabackup.Backup.resume.enabled', (bool) $this->autoResume);
$this->document->addScriptOptions('akeebabackup.Backup.resume.timeout', (int) $this->autoResumeTimeout);
$this->document->addScriptOptions('akeebabackup.Backup.resume.maxRetries', (int) $this->autoResumeRetries);

// The return URL
$this->document->addScriptOptions('akeebabackup.Backup.returnUrl', addcslashes($this->returnURL, "'\\"));

// Used as parameters to start_timeout_bar()
$this->document->addScriptOptions('akeebabackup.Backup.maxExecutionTime', (int) $this->maxExecutionTime);
$this->document->addScriptOptions('akeebabackup.Backup.runtimeBias', (int) $this->runtimeBias);

// Notifications
$this->document->addScriptOptions('akeebabackup.System.notification.iconURL', sprintf("%s../media/com_akeebabackup/icons/logo-48.png", $escapedBaseURL));
$this->document->addScriptOptions('akeebabackup.System.notification.hasDesktopNotification', (bool) $this->desktopNotifications);

// Domain keys
$this->document->addScriptOptions('akeebabackup.Backup.domains', $this->domains);

// AJAX proxy, View Log and ALICE URLs
$this->document->addScriptOptions('akeebabackup.System.params.AjaxURL', 'index.php?option=com_akeebabackup&view=Backup&task=ajax');
$this->document->addScriptOptions('akeebabackup.Backup.URLs.LogURL', sprintf("%sindex.php?option=com_akeebabackup&view=Log", $escapedBaseURL));
$this->document->addScriptOptions('akeebabackup.Backup.URLs.AliceURL', sprintf("%sindex.php?option=com_akeebabackup&view=Alice", $escapedBaseURL));

// Behavior triggers
$this->document->addScriptOptions('akeebabackup.Backup.autostart', (!$this->unwriteableOutput && $this->autoStart) ? 1 : 0);

// Push language strings to Javascript
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_LASTRESPONSE');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPSTARTED');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPFINISHED');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPHALT');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPRESUME');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPHALT_DESC');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPFAILED');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_BACKUPWARNING');
Text::script('COM_AKEEBABACKUP_BACKUP_TEXT_AVGWARNING');
