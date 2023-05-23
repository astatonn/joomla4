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
		<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP') ?>
	</h3>

	<div class="card-body">
		<div class="alert alert-info">
			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_INFO') ?>
			</p>
			<p>
				<a class="btn btn-info"
				   href="https://www.akeeba.com/documentation/akeeba-backup-joomla/automating-your-backup.html"
				   target="_blank">
					<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_GENERICREADDOC') ?>
				</a>
			</p>
		</div>

		<?php if(!$this->croninfo->info->legacyapi): ?>
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
		<?php elseif(!trim($this->croninfo->info->secret)): ?>
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
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_MANYMETHODS') ?>
			</p>

			<h4>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WEBCRON', true) ?>
			</h4>

			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON') ?>
			</p>

			<table class="table table-striped w-100">
				<tr>
					<td></td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_URL') ?>
					</td>
					<td class="text-break">
						<?= $this->escape($this->croninfo->info->root_url) ?>/<?= $this->escape($this->croninfo->frontend->path) ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGIN') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_PASSWORD') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME_INFO') ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS') ?>
					</td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS_INFO') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WEBCRON_THENCLICKSUBMIT') ?>
					</td>
				</tr>
			</table>

			<h4>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WGET', true) ?>
			</h4>

			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_WGET') ?>
				<code>
					wget --max-redirect=10000 "<?= $this->escape($this->croninfo->info->root_url ) ?>/<?= $this->escape($this->croninfo->frontend->path) ?>" -O - 1>/dev/null 2>/dev/null
				</code>
			</p>

			<h4>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_TAB_CURL', true) ?>
			</h4>

			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_CURL') ?>
				<code>
					curl -L --max-redirs 1000 -v "<?= $this->escape($this->croninfo->info->root_url) ?>/<?= $this->escape($this->croninfo->frontend->path) ?>" 1>/dev/null 2>/dev/null
				</code>
			</p>

			<h4>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_TAB_SCRIPT', true) ?>
			</h4>

			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_CUSTOMSCRIPT') ?>
			</p>
			<pre class="text-break">
&lt;?php
$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL,
'<?= $this->escape($this->croninfo->info->root_url) ?>/<?= $this->escape($this->croninfo->frontend->path) ?>');
curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
$buffer = curl_exec($curl_handle);
curl_close($curl_handle);
if (empty($buffer))
  echo "Sorry, the backup didn't work.";
else
  echo $buffer;
?&gt;
				</pre>

			<h4>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTENDBACKUP_TAB_URL', true) ?>
			</h4>

			<p>
				<?= Text::_('COM_AKEEBABACKUP_SCHEDULE_LBL_FRONTEND_RAWURL') ?>
				<code>
					<?= $this->escape($this->croninfo->info->root_url) ?>/<?= $this->escape($this->croninfo->frontend->path) ?>
				</code>
			</p>
		<?php endif ?>
	</div>
</div>
