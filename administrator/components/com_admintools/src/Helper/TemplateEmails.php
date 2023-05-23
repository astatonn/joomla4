<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Helper;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;

/**
 * Manage and send emails with Joomla's email templates component
 */
abstract class TemplateEmails
{
	/**
	 * Email templates known to Admin Tools.
	 */
	private const EMAIL_DEFINITIONS = [
		'com_admintools.troubleshooting'      => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_TROUBLESHOOTING_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_TROUBLESHOOTING_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_TROUBLESHOOTING_BODY_HTML',
			'variables'     => [
				'USERNAME',
				'ACTION',
				'SITENAME',
				'TROUBLESHOOTING_URLS',
				'TROUBLESHOOTING_URLS_HTML',
			],
		],
		'com_admintools.configmonitor'        => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_CONFIGMONITOR_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_CONFIGMONITOR_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_CONFIGMONITOR_BODY_HTML',
			'variables'     => [
				'AREA',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.userreactivate'       => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_USER_REACTIVATE_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_USER_REACTIVATE_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_USER_REACTIVATE_BODY_HTML',
			'variables'     => [
				'ACTIVATE',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.adminloginfail'       => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINFAIL_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINFAIL_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINFAIL_BODY_HTML',
			'variables'     => [
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.adminloginsuccess'    => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINSUCCESS_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINSUCCESS_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_ADMINLOGINSUCCESS_BODY_HTML',
			'variables'     => [
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.ipautoban'            => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_IPAUTOBAN_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_IPAUTOBAN_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_IPAUTOBAN_BODY_HTML',
			'variables'     => [
				'UNTIL',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.criticalfiles'        => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_BODY_HTML',
			'variables'     => [
				'INFO',
				'INFO_HTML',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.criticalfiles_global' => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_GLOBAL_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_GLOBAL_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_CRITICALFILES_GLOBAL_BODY_HTML',
			'variables'     => [
				'INFO',
				'INFO_HTML',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.superuserslist'       => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_SUPERUSERSLIST_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_SUPERUSERSLIST_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_SUPERUSERSLIST_BODY_HTML',
			'variables'     => [
				'INFO',
				'INFO_HTML',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],
		'com_admintools.rescueurl'            => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_RESCUEURL_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_RESCUEURL_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_RESCUEURL_BODY_HTML',
			'variables'     => [
				'RESCUEURL',
				'INFO_HTML',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
			],
		],
		'com_admintools.blockedrequest'       => [
			'subject'       => 'COM_ADMINTOOLS_EMAIL_BLOCKEDREQUEST_SUBJECT',
			'bodyPlaintext' => 'COM_ADMINTOOLS_EMAIL_BLOCKEDREQUEST_BODY',
			'bodyHtml'      => 'COM_ADMINTOOLS_EMAIL_BLOCKEDREQUEST_BODY_HTML',
			'variables'     => [
				'INFO_HTML',
				'USER',
				'SITENAME',
				'DATE',
				'IP',
				'URL',
				'LOOKUP',
				'UA',
				'RESCUEINFO',
				'RESCUE_TRIGGER_URL',
			],
		],

	];

	/**
	 * Checks whether the main email template for the specific key exists in the database.
	 *
	 * It does NOT check if the template is up-to-date.
	 *
	 * @param   string  $key
	 *
	 * @return  bool
	 */
	public static function hasTemplate(string $key): bool
	{
		return self::actOnTemplate($key, 'return');
	}

	/**
	 * Returns the number of the know templates configured in the database.
	 *
	 * Remember that this may include templates which are out-of-date!
	 *
	 * @return  int
	 */
	public static function countTemplates(): int
	{
		return self::actOnTemplates('return');
	}

	/**
	 * Returns the number of the known email templates.
	 *
	 * @return  int
	 */
	public static function countKnownTemplates(): int
	{
		return count(self::EMAIL_DEFINITIONS);
	}

	/**
	 * Returns the keys of the known email templates.
	 *
	 * @return  string[]
	 */
	public static function getKnownTemplatesKeys(): array
	{
		return array_keys(self::EMAIL_DEFINITIONS);
	}

	/**
	 * Updates a specific email template.
	 *
	 * Makes sure that the main email template exists in the database. If it doesn't, it's created. If it exists and its
	 * variables (tags), subject, body (plaintext) or body (HTML) differ it will be updated. Otherwise no further action
	 * is taken.
	 *
	 * @param   string  $key
	 *
	 * @return bool
	 */
	public static function updateTemplate(string $key)
	{
		return self::actOnTemplate($key, 'fix');
	}

	/**
	 * Resets an email template.
	 *
	 * WARNING! THIS ALSO REMOVES THE USER-GENERATED EMAIL TEMPLATES FOR THIS KEY.
	 *
	 * @param   string  $key
	 *
	 * @return  bool
	 */
	public static function resetTemplate(string $key)
	{
		return self::actOnTemplate($key, 'reset');
	}

	/**
	 * Update all email templates we know about.
	 *
	 * This operates only on the mail templates. User–generated templates are kept as-is.
	 *
	 * @return  int
	 */
	public static function updateAllTemplates(): int
	{
		return self::actOnTemplates('fix');
	}

	/**
	 * Resets all email templates we know about.
	 *
	 * WARNING! THIS ALSO REMOVES THE USER-GENERATED EMAIL TEMPLATES FOR ALL KEYS WE KNOW.
	 *
	 * @return  int Number of email template keys affected
	 */
	public static function resetAllTemplates(): int
	{
		return self::actOnTemplates('reset');
	}

	/**
	 * Removes all email templates we know about.
	 *
	 * WARNING! THIS ALSO REMOVES THE USER-GENERATED EMAIL TEMPLATES FOR ALL KEYS WE KNOW.
	 *
	 * @return  int Number of email template keys affected
	 */
	public static function deleteAllTemplates(): int
	{
		return self::actOnTemplates('delete');
	}

	/**
	 * Sends an email using a template.
	 *
	 * WARNING! THIS DOES NOT CHECK IF THE TEMPLATE EXISTS. USE TemplateEmails::updateTemplate($key) FIRST.
	 *
	 * @param   string       $key            The email template key to send
	 * @param   array        $data           The variable/tag associative array to include in the email
	 * @param   User|null    $user           The user to send the email to. NULL for the currently logged in user.
	 * @param   string|null  $forceLanguage  Force a specific language tag instead of using the user's preferences.
	 * @param   bool         $throw          False (default) to return false on error, True to throw the exception back
	 *                                       to you.
	 *
	 * @return  bool True if the email was sent.
	 * @throws  Exception When $throw === true and there's an error sending the email
	 */
	public static function sendMail(string $key, array $data, User $user = null, string $forceLanguage = null, bool $throw = false): bool
	{
		$app  = Factory::getApplication();

		// If mail sending is turned off I cannot send an email
		if ($app->get('mailonline', 1) == 0)
		{
			return false;
		}

		if (empty($user))
		{
			$user = $app->getIdentity();
		}

		// if ($user->guest || $user->block || !$user->sendEmail)
		// {
		// 	return false;
		// }

		try
		{
			/**
			 * We create a custom mailer, setting its priority to normal.
			 *
			 * Even though the Priority is nominally optional, SpamAssassin will reject emails without a priority.
			 * That's a major WTF which even Joomla itself doesn't know about :O
			 */
			$mailer           = Factory::getMailer();
			$mailer->Priority = 3;

			$app              = Factory::getApplication();
			$appLang          = $app->getLanguage() ?? null;
			$appLang          = is_object($appLang) ? $appLang->getTag() : null;
			$userLang         = $app->isClient('administrator') ? $user->getParam('administrator_language') : $user->getParam('language');
			$userFrontendLang = $user->getParam('language');
			$langTag          = $userLang ?: $userFrontendLang ?: $appLang ?: 'en-GB';
			$langTag          = $forceLanguage ?: $langTag;

			/**
			 * Try to get the template. Remember that Joomla looks for the specific language tag or the main template
			 * which defines no language and falls back to translation strings.
			 */
			$template = MailTemplate::getTemplate($key, $langTag);

			if (empty($template))
			{
				// Yeah, well, there's no template. I can't send the email, I'm afraid.
				return false;
			}

			$templateMailer = new MailTemplate($key, $langTag, $mailer);
			$templateMailer->addTemplateData($data);
			$templateMailer->addRecipient(trim($user->email), $user->name);

			return $templateMailer->send();
		}
		catch (Exception $e)
		{
			if ($throw)
			{
				throw $e;
			}

			return false;
		}
	}

	private static function actOnTemplate(string $key, string $action = 'return'): bool
	{
		/**
		 * Note that we are only checking the email template WITHOUT a language. This is considered the "default" email
		 * template from which all the localised email templates are generated. We only care if that email template
		 * exists and is up to date. We don't mess with the user-defined email templates, ever!
		 */
		try
		{
			/** @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__mail_templates'))
				->where($db->quoteName('template_id') . ' = :key')
				->where($db->quoteName('language') . ' = ' . $db->quote(''))
				->order($db->quoteName('language') . ' DESC')
				->bind(':key', $key);

			$templateInDB = $db->setQuery($query)->loadAssoc() ?: [];
			$hasTemplate  = !empty($templateInDB);
		}
		catch (\Exception $e)
		{
			$templateInDB = [];
			$hasTemplate  = false;
		}

		$knownTemplate = array_key_exists($key, self::EMAIL_DEFINITIONS);
		$action        = strtolower($action);

		switch (strtolower($action))
		{
			// Ensures a template exists and its definition is up-to-date
			case 'fix':
				if (!$knownTemplate)
				{
					return false;
				}

				// The template does not exist in the database. Create it.
				if (!$hasTemplate)
				{
					$record = self::EMAIL_DEFINITIONS[$key];
					self::createTemplate($key, $record['subject'], $record['bodyPlaintext'], $record['variables'], $record['bodyHtml'] ?? '');

					return true;
				}

				$record = self::EMAIL_DEFINITIONS[$key];

				// Do I need to update the record? We check the variables, subject and the plaintext and HTML bodies.
				try
				{
					$params         = json_decode($templateInDB['params'], true);
					$variablesInDB  = array_map('strtoupper', (array) $params['tags'][0] ?? []);
					$variablesKnown = array_map('strtoupper', $record['variables'] ?? []);
					$isIdentical    = empty(array_diff($variablesKnown, $variablesInDB));

					$isIdentical = $isIdentical && ($templateInDB['subject'] == $record['subject']);
					$isIdentical = $isIdentical && ($templateInDB['body'] == $record['bodyPlaintext']);
					$isIdentical = $isIdentical && ($templateInDB['htmlbody'] == $record['bodyHtml']);
				}
				catch (\Exception $e)
				{
					// The template is corrupt. We will reset it.
					return self::actOnTemplate($key, 'reset');
				}

				// The template in the DB is up-to-date. Bye-bye!
				if ($isIdentical)
				{
					return true;
				}

				// There were differences. Let's update the template.
				self::updateTemplateInDB($key, $record['subject'], $record['bodyPlaintext'], $record['variables'], $record['bodyHtml'] ?? '');

				return true;
				break;

			// Forcibly update a template if exists
			case 'update':
				if (!$knownTemplate)
				{
					return false;
				}

				if (!$hasTemplate)
				{
					return true;
				}

				$record = self::EMAIL_DEFINITIONS[$key];
				self::updateTemplateInDB($key, $record['subject'], $record['bodyPlaintext'], $record['variables'], $record['bodyHtml'] ?? '');

				return true;
				break;

			// Forcibly reset a template
			case 'reset':
				if (!$knownTemplate)
				{
					return false;
				}

				if ($hasTemplate)
				{
					MailTemplate::deleteTemplate($key);
				}

				$record = self::EMAIL_DEFINITIONS[$key];
				self::createTemplate($key, $record['subject'], $record['bodyPlaintext'], $record['variables'], $record['bodyHtml'] ?? '');

				return true;
				break;

			// Only return whether a template exists
			case 'return':
			default:
				return $hasTemplate;
				break;
		}
	}

	private static function actOnTemplates(string $action = 'return'): int
	{
		$count = 0;

		foreach (array_keys(self::EMAIL_DEFINITIONS) as $key)
		{
			if ($action === 'delete')
			{
				MailTemplate::deleteTemplate($key);

				continue;
			}

			if (self::actOnTemplate($key, $action))
			{
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Fork of MailTemplate::createTemplate WHICH ACTUALLY WORKS WITHOUT THROWING ERRORS.
	 *
	 * Insert a new mail template into the system
	 *
	 * @param   string  $key       Mail template key
	 * @param   string  $subject   A default subject (normally a translatable string)
	 * @param   string  $body      A default body (normally a translatable string)
	 * @param   array   $tags      Associative array of tags to replace
	 * @param   string  $htmlbody  A default htmlbody (normally a translatable string)
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   7.0.0
	 */
	private static function createTemplate(string $key, string $subject, string $body, array $tags, string $htmlbody = ''): bool
	{
		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');

		$template              = new \stdClass;
		$template->template_id = $key;
		$template->language    = '';
		$template->subject     = $subject;
		$template->body        = $body;
		$template->htmlbody    = $htmlbody;
		$template->attachments = '';
		$template->extension   = explode('.', $key, 2)[0];
		$params                = new \stdClass;
		$params->tags          = $tags;
		$template->params      = json_encode($params);

		return $db->insertObject('#__mail_templates', $template);
	}

	/**
	 * Fork of MailTemplate::updateTemplate WHICH ACTUALLY WORKS WITHOUT THROWING ERRORS.
	 *
	 * Update an existing mail template
	 *
	 * @param   string  $key       Mail template key
	 * @param   string  $subject   A default subject (normally a translatable string)
	 * @param   string  $body      A default body (normally a translatable string)
	 * @param   array   $tags      Associative array of tags to replace
	 * @param   string  $htmlbody  A default htmlbody (normally a translatable string)
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   7.0.0
	 */
	private static function updateTemplateInDB($key, $subject, $body, $tags, $htmlbody = '')
	{
		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');

		$template              = new \stdClass;
		$template->template_id = $key;
		$template->language    = '';
		$template->subject     = $subject;
		$template->body        = $body;
		$template->htmlbody    = $htmlbody;
		$params                = new \stdClass;
		$params->tags          = (array) $tags;
		$template->params      = json_encode($params);

		return $db->updateObject('#__mail_templates', $template, ['template_id', 'language']);
	}

}