<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Joomla\Plugin\System\AdminTools\Extension;

defined('_JEXEC') or die;

use Akeeba\Component\AdminTools\Administrator\Helper\Storage;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;
use Joomla\Plugin\System\AdminTools\Feature;
use Joomla\Plugin\System\AdminTools\Feature\Base as FeatureBase;
use Joomla\Plugin\System\AdminTools\Utility\BlockedRequestHandler;
use Joomla\Plugin\System\AdminTools\Utility\Cache;
use Joomla\Plugin\System\AdminTools\Utility\RescueUrl;
use Joomla\Registry\Registry;

class AdminTools extends CMSPlugin implements SubscriberInterface, DatabaseAwareInterface
{
	use DatabaseAwareTrait;

	/**
	 * is the Admin Tools component installed and enabled? If not, this plugin can't work!
	 *
	 * @var   bool
	 * @since 7.0.0
	 */
	private static $enabledComponent = false;

	/**
	 * The features to load and in which order to load them.
	 *
	 * @var   string[]
	 * @since 7.0.0
	 */
	private static $featureClasses = [
		Feature\FixApache401::class,
		Feature\AllowedDomains::class,
		Feature\Shield404::class,
		Feature\EmailOnPHPException::class,
		Feature\EnforceIPAutoBan::class,
		Feature\IPDenyList::class,
		Feature\WAFDenyList::class,
		Feature\CustomAdminFolder::class,
		Feature\AdminIPExclusiveAllow::class,
		Feature\AwaySchedule::class,
		Feature\DeleteInactiveUsers::class,
		Feature\TemporarySuperUsers::class,
		Feature\RemoveOldLogEntries::class,
		Feature\DisableObsoleteAdmins::class,
		Feature\ProtectAgainstDeactivation::class,
		Feature\DoNoCreateNewAdmins::class,
		Feature\ConfigurationMonitoring::class,
		Feature\AdminSecretWord::class,
		Feature\EmailOnSuccessfulAdminLogin::class,
		Feature\ProjectHoneypot::class,
		Feature\SQLiShield::class,
		Feature\SessionShield::class,
		Feature\MUAShield::class,
		Feature\RFIShield::class,
		Feature\PHPShield::class,
		Feature\DFIShield::class,
		Feature\BadWordsFiltering::class,
		Feature\TmplSwitch::class,
		Feature\TemplateSwitch::class,
		Feature\URLRedirections::class,
		Feature\SessionOptimiser::class,
		Feature\SessionCleaner::class,
		Feature\CacheCleaner::class,
		Feature\CacheExpiration::class,
		Feature\CleanTemporaryFiles::class,
		Feature\ImportSettings::class,
		Feature\CustomGeneratorMeta::class,
		Feature\TrackFailedLogins::class,
		Feature\EmailOnFailedAdminLogin::class,
		Feature\WarnAboutLeakedPasswords::class,
		Feature\NoFrontendSuperUserLogin::class,
		Feature\PWAuthOnWebAuthn::class,
		Feature\SaveUserSignupIPAsNote::class,
		Feature\ResetJoomlaTFAOnPasswordReset::class,
		Feature\BlockedEmailDomainsOnSignup::class,
		Feature\SuperUsersList::class,
		Feature\BrowserConsoleWarning::class,
		Feature\CriticalFilesMonitoring::class,
		Feature\CustomCriticalFilesMonitoring::class,
		Feature\QuickStartReminder::class,
		Feature\CustomBlockedRequestPage::class,
		Feature\LinkMigration::class,
		Feature\ThirdPartyBlockedRequests::class,
		Feature\DisablePwdReset::class,
		Feature\ItemidShield::class,
		Feature\WarnAboutBlockedUsernames::class
	];

	/**
	 * Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc)
	 *
	 * @var   bool
	 * @since 7.0.0
	 */
	public $skipFiltering = false;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  7.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The security exceptions handler
	 *
	 * @var   BlockedRequestHandler
	 * @since 7.0.0
	 */
	protected $blockedRequestHandler = null;

	/**
	 * Component parameters
	 *
	 * @var   Registry
	 * @since 7.0.0
	 */
	private $cParams = null;

	/**
	 * The applicable WAF Exceptions which prevent filtering from taking place
	 *
	 * @var   array
	 * @since 7.0.0
	 */
	private $exceptions = [];

	private $features = [];

	/**
	 * A reference to the global application input object.
	 *
	 * @var Input
	 */
	private $input;

	/** @var   Storage   WAF configuration parameters */
	private $wafConfig = null;

	/**
	 * Initializes the plugin.
	 *
	 * @since  7.2.0
	 */
	public function initalisePlugin()
	{
		$this->loadVersion();
		$this->initialize();
	}

	public static function getSubscribedEvents(): array
	{
		if (RescueUrl::isRescueMode())
		{
			return [];
		}

		if (!self::$enabledComponent)
		{
			return [];
		}

		return [
			'onAfterInitialise'          => 'onAfterInitialise',
			'onAfterApiRoute'            => 'onAfterApiRoute',
			'onAfterRoute'               => 'onAfterRoute',
			'onBeforeRender'             => 'onBeforeRender',
			'onAfterRender'              => 'onAfterRender',
			'onAfterDispatch'            => 'onAfterDispatch',
			'onLoginFailure'             => 'onLoginFailure',
			'onUserAfterLogin'           => 'onUserAfterLogin',
			'onUserLoginFailure'         => 'onUserLoginFailure',
			'onUserLogout'               => 'onUserLogout',
			'onUserAuthorisationFailure' => 'onUserAuthorisationFailure',
			'onUserLogin'                => 'onUserLogin',
			'onUserAfterSave'            => 'onUserAfterSave',
			'onUserBeforeSave'           => 'onUserBeforeSave',
			'onContentPrepareForm'       => 'onContentPrepareForm',
			'onContentPrepareData'       => 'onContentPrepareData',
			'onUserAfterDelete'          => 'onUserAfterDelete',
			'onError'                    => 'onError',
		];
	}

	/**
	 * Work around broken extensions serializing the entire application.
	 *
	 * Caveat: changing the plugin parameters will have no effect in this scenario until the cache expires or is
	 * cleared.
	 *
	 * @return string[]
	 */
	public function __sleep()
	{
		return ['_name', '_type', 'params', 'autoloadLanguage', 'allowLegacyListeners'];
	}

	/**
	 * Work around broken extensions serializing the entire application.
	 *
	 * Caveat: changing the plugin parameters will have no effect in this scenario until the cache expires or is
	 * cleared.
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		$this->loadLanguage();

		$this->initialize();
	}

	/**
	 * Executes right after Joomla! has dispatched the application to the relevant component
	 *
	 * @return  mixed
	 */
	public function onAfterDispatch(Event $event): void
	{
		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onAfterDispatch'))
		);
	}

	public function onAfterInitialise(Event $event): void
	{
		// We check for a Rescue URL before processing any other security rules.
		$this->blockedRequestHandler->checkRescueURL();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onAfterInitialise'))
		);
	}

	public function onAfterRender(Event $event): void
	{
		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onAfterRender'))
		);
	}

	public function onAfterRoute(Event $event): void
	{
		// We re-evaluate WAF Exceptions now that SEF routes have been parsed
		$this->loadWAFExceptions();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onAfterRoute'))
		);
	}

	public function onAfterApiRoute(Event $event): void
	{
		// We re-evaluate WAF Exceptions now that SEF routes have been parsed
		$this->loadWAFExceptions();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onAfterApiRoute'))
		);
	}

	public function onBeforeRender(Event $event): void
	{
		/**
		 * This is used by Admin Tools. It is the last even to run in the onAfterRender processing chain.
		 *
		 * We achieve that by registering a custom onAfterRender handler which calls the onAfterRenderLatebound methods
		 * of our Feature objects.
		 */
		$dispatcher = $this->getApplication()->getDispatcher();
		$dispatcher->addListener('onAfterRender', function (Event $event) {
			$event->setArgument('result', array_merge(
					$event->getArgument('result', []),
					$this->runFeature('onAfterRenderLatebound'))
			);
		}, PHP_INT_MAX - 1);

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onBeforeRender'))
		);
	}

	public function onContentPrepareData(Event $event)
	{
		[$context, $data] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onContentPrepareData', $context, $data))
		);
	}

	public function onContentPrepareForm(Event $event)
	{
		[$form, $data] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onContentPrepareForm', $form, $data))
		);
	}

	public function onError(Event $event): void
	{
		$this->runFeature('onError', $event);
	}

	/**
	 * Alias for onUserLoginFailure
	 *
	 * @param   AuthenticationResponse  $response
	 *
	 * @return mixed
	 *
	 * @deprecated 3.2.0
	 */
	public function onLoginFailure(Event $event): void
	{
		[$response] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserLoginFailure', $response))
		);
	}

	public function onUserAfterDelete(Event $event)
	{
		[$user, $success, $msg] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserAfterDelete', $user, $success, $msg))
		);
	}

	public function onUserAfterLogin(Event $event): void
	{
		[$options] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserAfterLogin', $options))
		);
	}

	public function onUserAfterSave(Event $event): void
	{
		[$user, $isnew, $success, $msg] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserAfterSave', $user, $isnew, $success, $msg))
		);
	}

	public function onUserAuthorisationFailure(Event $event): void
	{
		[$authorisation] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserAuthorisationFailure', $authorisation))
		);
	}

	public function onUserBeforeSave(Event $event): void
	{
		[$olduser, $isnew, $user] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserBeforeSave', $olduser, $isnew, $user))
		);
	}

	public function onUserLogin(Event $event): void
	{
		[$user, $options] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserLogin', $user, $options))
		);
	}

	/**
	 * Called when a user fails to log in
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function onUserLoginFailure(Event $event): void
	{
		[$response] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserLoginFailure', $response))
		);
	}

	public function onUserLogout(Event $event): void
	{
		[$parameters, $options] = $event->getArguments();

		$event->setArgument('result', array_merge(
				$event->getArgument('result', []),
				$this->runFeature('onUserLogout', $parameters, $options))
		);
	}

	public function runBooleanFeature(string $name, ...$arguments): bool
	{
		$result = false;

		foreach ($this->features as $o)
		{
			if (!method_exists($o, $name))
			{
				continue;
			}

			$result = $result && $o->{$name}(...$arguments);
		}

		return $result;
	}

	/**
	 * Load the applicable WAF exceptions for this request after parsing the Joomla! SEF rules
	 */
	protected function loadWAFExceptionsSEF()
	{
		/**
		 * We have seen (ticket #25473) such a thing as a host which does not set the HTTP_HOST and SCRIPT_NAME server
		 * variables. On this kind of server we cannot reliably process WAF Exceptions.
		 */
		$httpHost   = $this->getApplication()->input->server->getString('HTTP_HOST', null);
		$scriptName = $this->getApplication()->input->server->getString('SCRIPT_NAME', null);

		if (is_null($httpHost) && is_null($scriptName))
		{
			return;
		}

		// Get the SEF URI path
		$uriPath = ltrim(Uri::getInstance()->getPath() ?: '', '/');

		// Do I have an index.php prefix?
		if (substr($uriPath, 0, 10) == 'index.php/')
		{
			$uriPath = substr($uriPath, 10);
		}

		// Get the URI path without the language prefix
		$uriPathNoLanguage = $uriPath;

		// Remove the language code from a front-end SEF path.
		if ($this->getApplication()->isClient('site') && Multilanguage::isEnabled())
		{
			$languages = LanguageHelper::getLanguages('lang_code');

			foreach ($languages as $lang)
			{
				$langSefCode = $lang->sef . '/';

				if (strpos($uriPath, $langSefCode) === 0)
				{
					$uriPathNoLanguage = substr($uriPath, strlen($langSefCode));
				}
			}
		}

		$uriPathNoLanguage = '/' . ltrim($uriPathNoLanguage, '/');

		/**
		 * Load all WAF exceptions for the current SEF URL i.e. it has no option set, the view contains a leading slash
		 * and partially matches the current SEF path.
		 */
		$this->exceptions = array_filter(Cache::getCache('wafexceptions'), function ($record) use ($uriPathNoLanguage) {
			// Check for empty option
			if (!empty($record['option']))
			{
				return false;
			}

			// Check for leading slash in the view
			if (substr($record['view'], 0, 1) !== '/')
			{
				return false;
			}

			// Make sure the matching path is shorter or equal in length to the current path.
			$currentPathLength = strlen($uriPathNoLanguage);

			if (strlen($record['view']) > $currentPathLength)
			{
				return false;
			}

			// Does the path match?
			return substr($record['view'], 0, $currentPathLength) === $uriPathNoLanguage;
		});
	}

	/**
	 * Get the view declared in the application input. It recognizes both view=viewName and task=controllerName.taskName
	 * variants supported by Joomla's MVC.
	 *
	 * @return  string
	 *
	 * @since   6.0.0
	 */
	private function getCurrentView()
	{
		$fallbackView = $this->input->getCmd('controller', '');
		$view         = $this->input->getCmd('view', $fallbackView);
		$task         = $this->input->getCmd('task', '');

		if (empty($view) && (strpos($task, '.') !== false))
		{
			[$view, $task] = explode('.', $task, 2);
		}

		return $view;
	}

	/**
	 * Initialize the plugin.
	 *
	 * Kept separately because of bad developers who serialise the entire application. This caused a pre-initialized
	 * plugin instance being woken up which caused problems and didn't provide correct protection.
	 */
	private function initialize()
	{
		self::$enabledComponent = ComponentHelper::isEnabled('com_admintools');

		if (!self::$enabledComponent)
		{
			return;
		}

		// Store a reference to the global input object
		$this->input = $this->getApplication()->input;

		// We need to boot the Admin Tools component so that its autoloader is registered throughout the request.
		$this->getApplication()->bootComponent('com_admintools');

		// Load the WAF configuration parameters
		$this->wafConfig = Storage::getInstance();

		// Load the component parameters
		$this->cParams = ComponentHelper::getParams('com_admintools');

		// Preload the security exceptions handler object
		$this->blockedRequestHandler = new BlockedRequestHandler($this->params, $this->wafConfig, $this->cParams);

		// Load the WAF Exceptions
		$this->loadWAFExceptions();

		// Load and register the plugin features
		$this->loadFeatures();
	}

	private function loadFeatures()
	{
		foreach (self::$featureClasses as $className)
		{
			if (!class_exists($className))
			{
				continue;
			}

			/** @var FeatureBase $o */
			$o = new $className($this->getApplication(), $this->getDatabase(), $this->params, $this->wafConfig, $this->input, $this->blockedRequestHandler, $this->exceptions, $this->skipFiltering, $this);

			if (!$o->isEnabled())
			{
				continue;
			}

			$this->features[] = $o;
		}
	}

	/**
	 * Loads a menu item and returns the effective option and view
	 *
	 * @param   int     $Itemid  The menu item ID to load
	 * @param   string  $option  The currently set option
	 * @param   string  $view    The currently set view
	 *
	 * @return  array  The new option and view as array($option, $view)
	 */
	private function loadMenuItem($Itemid, $option, $view)
	{
		// Option and view already set, they will override the Itemid
		if (!empty($option) && !empty($view))
		{
			return [$option, $view];
		}

		// Load the menu item
		$menu = $this->getApplication()->getMenu()->getItem($Itemid);

		// Menu item does not exist, nothign to do
		if (!is_object($menu))
		{
			return [$option, $view];
		}

		// Remove "index.php?" and parse the link
		parse_str(str_replace('index.php?', '', $menu->link), $menuquery);

		// We use the option and view from the menu item only if they are not overridden in the request
		if (empty($option))
		{
			$option = array_key_exists('option', $menuquery) ? $menuquery['option'] : $option;
		}

		if (empty($view))
		{
			$view = array_key_exists('view', $menuquery) ? $menuquery['view'] : $view;
		}

		// Return the new option and view
		return [$option, $view];
	}

	/**
	 * Load the applicable WAF exceptions for this request
	 */
	private function loadWAFExceptions()
	{
		// Joomla 4 loads system plugins in CLI applications too
		if (!$this->getApplication()->isClient('site') && !$this->getApplication()->isClient('administrator'))
		{
			return;
		}

		$isSEF = $this->getApplication()->get('sef', 0);

		$option = $this->input->getCmd('option', '');
		$view   = $this->getCurrentView();

		/**
		 * If we have SEF URLs enabled and an empty $option (SEF not yet parsed) OR we have an option that does not
		 * start with com_ we need to a different kind of processing.
		 *
		 * NB! If an option in the form of com_something is provided we have a non-SEF URL running on a site with SEF
		 * URLs enabled.
		 */
		if (($isSEF && empty($option)) || (!empty($option) && substr($option, 0, 4) != 'com_'))
		{
			$this->loadWAFExceptionsSEF();
		}
		else
		{
			$Itemid = $this->input->getInt('Itemid', null);

			if (!empty($Itemid))
			{
				[$option, $view] = $this->loadMenuItem($Itemid, $option, $view);
			}

			$this->loadWAFExceptionsByOption($option, $view);
		}

		if (empty($this->exceptions))
		{
			$this->exceptions = [];
		}

		/**
		 * When we have at least one WAF Exceptions rule with an empty query parameter which matches the component and
		 * view name / SEF path of the request we have a Group B exception which means that we need to set the
		 * skipFiltering flag. This will be communicated to the Feature classes
		 */
		$this->skipFiltering = false;

		foreach ($this->exceptions as $record)
		{
			// Group B rules have an empty option and query.
			if (!empty($record['option']) || !empty($record['query']))
			{
				continue;
			}

			/**
			 * Since the rule's view is already guaranteed to match the current view OR SEF path (depending on context)
			 * if I am still here it means that I have a Group B rule. Therefore the flag must be set to true.
			 */
			$this->skipFiltering = true;

			break;
		}

		/**
		 * Finally, $this->exceptions must only contain the query string parameters to be exempted from filtering. I
		 * will also remove duplicates and sort them to make array searches in the Features' matchArray() method a
		 * little bit faster.
		 */
		$this->exceptions = array_map(function ($record) {
			return $record['query'];
		}, $this->exceptions);
		$this->exceptions = array_unique($this->exceptions);
		sort($this->exceptions);
	}

	/**
	 * Loads WAF Exceptions by option and view (non-SEF URLs)
	 *
	 * @param   string  $option  Component, e.g. com_something
	 * @param   string  $view    View, e.g. foobar
	 *
	 * @return  void
	 */
	private function loadWAFExceptionsByOption($option, $view)
	{
		$option = $option ?: '';
		$view   = $view ?: '';

		$this->exceptions = array_filter(Cache::getCache('wafexceptions'), function ($record) use ($option, $view) {
			if (!empty($record['option']) && ($record['option'] != $option))
			{
				return false;
			}

			if (!empty($record['view']) && ($record['view'] != $view))
			{
				return false;
			}

			return true;
		});
	}

	private function runFeature(string $name, ...$arguments)
	{
		$result = [];

		foreach ($this->features as $o)
		{
			if (!method_exists($o, $name))
			{
				continue;
			}

			$result[] = $o->{$name}(...$arguments);
		}

		return $result;
	}

	private function loadVersion(): void
	{
		$filePath = JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';

		if (@file_exists($filePath) && is_file($filePath))
		{
			include_once $filePath;
		}

		if (!defined('ADMINTOOLS_VERSION'))
		{
			define('ADMINTOOLS_VERSION', 'dev');
		}

		if (!defined('ADMINTOOLS_DATE'))
		{
			define('ADMINTOOLS_DATE', gmdate('Y-m-d'));
		}

		if (!defined('ADMINTOOLS_PRO'))
		{
			$isPro = @file_exists(JPATH_ADMINISTRATOR . '/components/com_admintools/src/Controller/ScansController.php');

			define('ADMINTOOLS_PRO', $isPro ? '1' : '0');
		}
	}
}