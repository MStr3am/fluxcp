<?php
require_once 'Flux/Paginator.php';

/**
 * The template is mostly responsible for the presentation logic of things, but
 * currently it also carries the task of executing the action files, which are
 * responsible for the business logic of the application. Maybe this will
 * change in the future, but I'm not sure yet. As long as the developers are
 * forced to adhere to the separation of business logic and presentation logic
 * then I don't think I'll be motivated enough to change this part.
 *
 * Views are rendered within the scope of the template instance, thus $this can
 * be used to access the template instance's methods, and is also how helpers
 * are currently implemented.
 */
class Flux_Template {
	/**
	 * Default data which gets exposed as globals to the templates, and may be
	 * set with the setDefaultData() method.
	 *
	 * @access private
	 * @var array
	 */
	private $defaultData = array();
	
	/**
	 * Request parameters.
	 *
	 * @access protected
	 * @var Flux_Config
	 */
	protected $params;
	
	/**
	 * Base URI of the entire application.
	 *
	 * @access protected
	 * @var string
	 */
	protected $basePath;
	
	/**
	 * Module path.
	 *
	 * @access protected
	 * @var string
	 */
	protected $modulePath;
	
	/**
	 * Module name.
	 *
	 * @access protected
	 * @var string
	 */
	protected $moduleName;
	
	/**
	 * Theme path. This is the path to the selected theme itself, not the real
	 * theme path which contains several themes.
	 *
	 * @access protected
	 * @var string
	 */
	protected $themePath;
	
	/**
	 * Action name. Actions exist as modulePath/moduleName/actionName.php.
	 *
	 * @access protected
	 * @var string
	 */
	protected $actionName;
	
	/**
	 * Action path, would be the path format documented in $actionName.
	 *
	 * @access protected
	 * @var string
	 */
	protected $actionPath;
	
	/**
	 * View name, this is usually the same as the actionName.
	 *
	 * @access protected
	 * @var string
	 */
	protected $viewName;
	
	/**
	 * View path, follows a similar (or rather, exact) format like actionPath,
	 * except there would be a themePath and viewName instead.
	 *
	 * @access protected
	 * @var string
	 */
	protected $viewPath;
	
	/**
	 * Header name. The header file would exist under the themePath's top level
	 * and the headerName would simply be the file's basename without the .php
	 * extension. This name is usually 'header'.
	 *
	 * @access protected
	 * @var string
	 */	
	protected $headerName;
	
	/**
	 * The actual path to the header file.
	 *
	 * @access protected
	 * @var string
	 */
	protected $headerPath;
	
	/**
	 * The footer name.
	 * Similar to headerName. This name is usually 'footer'.
	 *
	 * @access protected
	 * @var string
	 */
	protected $footerName;
	
	/**
	 * The actual path to the footer file.
	 *
	 * @access protected
	 * @var string
	 */
	protected $footerPath;
	
	/**
	 * Whether or not to use mod_rewrite-powered clean URLs or just plain old
	 * query strings.
	 *
	 * @access protected
	 * @var string
	 */
	protected $useCleanUrls;
	
	/**
	 * URL of the current module/action being viewed.
	 *
	 * @access protected
	 * @var string
	 */
	protected $url;
	
	/**
	 * URL of the current module/action being viewed. (including query string)
	 *
	 * @access protected
	 * @var string
	 */
	protected $urlWithQs;
	
	/**
	 * Module/action for missing action's event.
	 *
	 * @access protected
	 * @var array
	 */
	protected $missingActionModuleAction;
	
	/**
	 * Module/action for missing view's event.
	 *
	 * @access protected
	 * @var array
	 */
	protected $missingViewModuleAction;
	
	/**
	 * Construct new template onbject.
	 *
	 * @param Flux_Config $config
	 * @access public
	 */
	public function __construct(Flux_Config $config)
	{
		$this->params                    = $config->get('params');
		$this->basePath                  = $config->get('basePath');
		$this->modulePath                = $config->get('modulePath');
		$this->moduleName                = $config->get('moduleName');
		$this->themePath                 = $config->get('themePath');
		$this->actionName                = $config->get('actionName');
		$this->viewName                  = $config->get('viewName');
		$this->headerName                = $config->get('headerName');
		$this->footerName                = $config->get('footerName');
		$this->useCleanUrls              = $config->get('useCleanUrls');
		$this->missingActionModuleAction = $config->get('missingActionModuleAction', false);
		$this->missingViewModuleAction   = $config->get('missingViewModuleAction', false);
	}
	
	/**
	 * Any data that gets set here will be available to all templates as global
	 * variables unless they are overridden by variables of the same name set
	 * in the render() method.
	 *
	 * @return array
	 * @access public
	 */
	public function setDefaultData(array &$data)
	{
		$this->defaultData = $data;
		return $data;
	}
	
	/**
	 * Render a template, but before doing so, call the action file and render
	 * the header->view->footer in that order.
	 *
	 * @param arary $dataArr Key=>value pairs of variables to be exposed to the template as globals.
	 * @access public
	 */
	public function render(array $dataArr = array())
	{
		$this->actionPath = sprintf('%s/%s/%s.php', $this->modulePath, $this->moduleName, $this->actionName);
		if (!file_exists($this->actionPath)) {
			$this->moduleName = $this->missingActionModuleAction[0];
			$this->actionName = $this->missingActionModuleAction[1];
			$this->viewName   = $this->missingActionModuleAction[1];
			$this->actionPath = sprintf('%s/%s/%s.php', $this->modulePath, $this->moduleName, $this->actionName);
		}
		
		$this->viewPath = sprintf('%s/%s/%s.php', $this->themePath, $this->moduleName, $this->actionName);
		if (!file_exists($this->viewPath)) {
			$this->moduleName = $this->missingViewModuleAction[0];
			$this->actionName = $this->missingViewModuleAction[1];
			$this->viewName   = $this->missingViewModuleAction[1];
			$this->actionPath = sprintf('%s/%s/%s.php', $this->modulePath, $this->moduleName, $this->actionName);
			$this->viewPath   = sprintf('%s/%s/%s.php', $this->themePath, $this->moduleName, $this->viewName);
		}
		
		$this->headerPath = sprintf('%s/%s.php', $this->themePath, $this->headerName);
		$this->footerPath = sprintf('%s/%s.php', $this->themePath, $this->footerName);
		$this->url        = $this->url($this->moduleName, $this->actionName);
		$this->urlWithQS  = $this->url;
		
		if (!empty($_SERVER['QUERY_STRING'])) {
			$this->urlWithQS .= "?{$_SERVER['QUERY_STRING']}";
		}
		
		// Tidy up!
		if (Flux::config('OutputCleanHTML')) {
			$dispatcher = Flux_Dispatcher::getInstance();
			$tidyIgnore = false;
			if (($tidyIgnores = Flux::config('TidyIgnore')) instanceOf Flux_Config) {
				foreach ($tidyIgnores->getChildrenConfigs() as $ignore) {
					$ignore = $ignore->toArray();
					if (is_array($ignore) && array_key_exists('module', $ignore)) {
						$module = $ignore['module'];
						$action = array_key_exists('action', $ignore) ? $ignore['action'] : $dispatcher->defaultAction;
						if ($this->moduleName == $module && $this->actionName == $action) {
							$tidyIgnore = true;
						}
					}
				}
			}
			if (!$tidyIgnore) {
				ob_start();
			}
		}
		
		// Merge with default data.
		$data = array_merge($this->defaultData, $dataArr);
		
		// Extract data array and make them appear as though they were global
		// variables from the template.
		extract($data, EXTR_REFS);
		
		$preprocessorPath = sprintf('%s/main/preprocess.php', $this->modulePath);
		if (file_exists($preprocessorPath)) {
			include $preprocessorPath;
		}
		
		include $this->actionPath;
		
		if (file_exists($this->headerPath)) {
			include $this->headerPath;
		}
	
		include $this->viewPath;
	
		if (file_exists($this->footerPath)) {
			include $this->footerPath;
		}
		
		// Really, tidy up!
		if (Flux::config('OutputCleanHTML') && !$tidyIgnore && function_exists('tidy_repair_string')) {
			$content = ob_get_clean();
			$content = tidy_repair_string($content, array('indent' => true, 'wrap' => false, 'output-xhtml' => true), 'utf8');
			echo $content;
		}
	}
	
	/**
	 * Returns an array of menu items that should be diplayed from the theme.
	 * Only menu items the current user (and their level) have access to will
	 * be returned as part of the array;
	 *
	 * @return array
	 */
	public function getMenuItems($adminMenus = false)
	{
		$auth           = Flux_Authorization::getInstance();
		$accountLevel   = Flux::$sessionData->account->level;
		$adminMenuLevel = Flux::config('AdminMenuLevel');
		$defaultAction  = Flux_Dispatcher::getInstance()->defaultAction;
		$menuItems      = Flux::config('MenuItems');
		$allowedItems   = array();
		
		if (!($menuItems instanceOf Flux_Config)) {
			return array();
		}
		
		foreach ($menuItems->toArray() as $menuName => $menuItem) {
			$module = array_key_exists('module', $menuItem) ? $menuItem['module'] : false;
			$action = array_key_exists('action', $menuItem) ? $menuItem['action'] : $defaultAction;
			
			if ($adminMenus) {
				$cond = $auth->config("modules.$module.$action") >= $adminMenuLevel;
			}
			else {
				$cond = $auth->config("modules.$module.$action") < $adminMenuLevel;
			}
			
			if ($auth->actionAllowed($module, $action) && $cond) {
				$allowedItems[] = array('name' => $menuName, 'module' => $module, 'action' => $action);
			}
		}
		
		return $allowedItems;
	}
	
	/**
	 * @see Flux_Template::getMenuItems()
	 */
	public function getAdminMenuItems()
	{
		return $this->getMenuItems(true);
	}
	
	/**
	 * Get sub-menu items for a particular module.
	 *
	 * @param string $moduleName
	 * @return array
	 */
	public function getSubMenuItems($moduleName = null)
	{
		$auth         = Flux_Authorization::getInstance();
		$moduleName   = $moduleName ? $moduleName : $this->moduleName;
		$subMenuItems = Flux::config('SubMenuItems');
		$allowedItems = array();
		
		if (!($subMenuItems instanceOf Flux_Config) || !( ($menus = $subMenuItems->get($moduleName)) instanceOf Flux_Config )) {
			return array();
		}
		
		foreach ($menus->toArray() as $actionName => $menuName) {
			if ($auth->actionAllowed($moduleName, $actionName)) {
				$allowedItems[] = array('name' => $menuName, 'module' => $moduleName, 'action' => $actionName);
			}
		}
		
		return $allowedItems;
	}
	
	/**
	 * Get an array of login server names.
	 *
	 * @return array
	 */
	public function getServerNames()
	{
		return array_keys(Flux::$loginAthenaGroupRegistry);
	}
	
	/**
	 * Determine if more than 1 server exists.
	 *
	 * @return bool
	 */
	public function hasManyServers()
	{
		return count(Flux::$loginAthenaGroupRegistry) > 1;
	}
	
	/**
	 * Obtain the absolute web path of the specified user path. Specify the
	 * path as a relative path.
	 *
	 * @param string $path Relative path from basePath.
	 * @access public
	 */
	public function path($path)
	{
		if (is_array($path)) {
			$path = implode('/', $path);
		}
		return "{$this->basePath}/$path";
	}
	
	/**
	 * Similar to the path() method, but uses the $themePath as the path from
	 * which the user-specified path is relative.
	 *
	 * @param string $path Relative path from themePath.
	 * @access public
	 */
	public function themePath($path)
	{
		if (is_array($path)) {
			$path = implode('/', $path);
		}
		return $this->path("{$this->themePath}/$path");
	}
	
	/**
	 * Create a URI based on the setting of $useCleanUrls. This will determine
	 * whether or not we will create a mod_rewrite-based clean URL or just a
	 * regular query string based one.
	 *
	 * @param string $moduleName
	 * @param string $actionName
	 * @access public
	 */
	public function url($moduleName, $actionName = null, $params = array())
	{
		$defaultAction = Flux_Dispatcher::getInstance()->defaultAction;
		
		if ($params instanceOf Flux_Config) {
			$params = $params->toArray();
		}
		
		$queryString = '';
		
		if (count($params)) {
			$queryString .= Flux::config('UseCleanUrls') ? '?' : '&';
			foreach ($params as $param => $value) {
				$queryString .= sprintf('%s=%s&', $param, urlencode($value));
			}
			$queryString = rtrim($queryString, '&');
		}
		
		if ($this->useCleanUrls) {
			if ($actionName && $actionName != $defaultAction) {
				return sprintf('%s/%s/%s/%s', $this->basePath, $moduleName, $actionName, $queryString);
			}
			else {
				return sprintf('%s/%s/%s', $this->basePath, $moduleName, $queryString);
			}
		}
		else {
			if ($actionName && $actionName != $defaultAction) {
				return sprintf('%s/?module=%s&action=%s%s', $this->basePath, $moduleName, $actionName, $queryString);
			}
			else {
				return sprintf('%s/?module=%s%s', $this->basePath, $moduleName, $queryString);
			}
		}
	}
	
	/**
	 * Format money strings (note: name soon to be changed).
	 *
	 * @param float $number Amount
	 * @return string Formatted amount
	 */
	public function formatDollar($number)
	{
		$number = (float)$number;
		$amount = number_format(
			$number,
			Flux::config('MoneyDecimalPlaces'),
			Flux::config('MoneyDecimalSymbol'),
			Flux::config('MoneyThousandsSymbol')
		);
		return $amount;
	}
	
	/**
	 * Format a MySQL DATE column according to the DateFormat config.
	 *
	 * @param string $data
	 * @return string
	 * @access public
	 */
	public function formatDate($date = null)
	{
		$ts = $date ? strtotime($date) : time();
		return date(Flux::config('DateFormat'), $ts);
	}
	
	/**
	 * Format a MySQL DATETIME column according to the DateTimeFormat config.
	 *
	 * @param string $dataTime
	 * @return string
	 * @access public
	 */
	public function formatDateTime($dateTime = null)
	{
		$ts = $dateTime ? strtotime($dateTime) : time();
		return date(Flux::config('DateTimeFormat'), $ts);
	}
	
	/**
	 * Create a series of select fields matching a MySQL DATE format.
	 *
	 * @param string $name
	 * @param string $value DATE formatted string.
	 * @return string
	 */
	public function dateField($name, $value = null)
	{
		$ts    = $value ? strtotime($value) : time();
		$year  = ($year =$this->params->get("{$name}_year"))  ? $year  : date('Y', $ts);
		$month = ($month=$this->params->get("{$name}_month")) ? $month : date('m', $ts);
		$day   = ($day  =$this->params->get("{$name}_day"))   ? $day   : date('d', $ts);
		
		// Get years.
		$years = sprintf('<select name="%s_year">', $name);
		for ($i = 2038; $i >= 1970; --$i) {
			if ($year == $i) {
				$years .= sprintf('<option value="%04d" selected="selected">%04d</option>', $i, $i);
			}
			else {
				$years .= sprintf('<option value="%04d">%04d</option>', $i, $i);
			}
		}
		$years .= '</select>';
		
		// Get months.
		$months = sprintf('<select name="%s_month">', $name);
		for ($i = 1; $i <= 12; ++$i) {
			if ($month == $i) {
				$months .= sprintf('<option value="%02d" selected="selected">%02d</option>', $i, $i);
			}
			else {
				$months .= sprintf('<option value="%02d">%02d</option>', $i, $i);
			}
		}
		$months .= '</select>';
		
		// Get days.
		$days = sprintf('<select name="%s_day">', $name);
		for ($i = 1; $i <= 31; ++$i) {
			if ($day == $i) {
				$days .= sprintf('<option value="%02d" selected="selected">%02d</option>', $i, $i);
			}
			else {
				$days .= sprintf('<option value="%02d">%02d</option>', $i, $i);
			}
		}
		$days .= '</select>';
		
		return sprintf('<span class="date-field">%s-%s-%s</span>', $years, $months, $days);
	}
	
	/**
	 * Create a series of select fields matching a MySQL DATETIME format.
	 *
	 * @param string $name
	 * @param string $value DATETIME formatted string.
	 * @return string
	 */
	public function dateTimeField($name, $value = null)
	{
		$dateField = $this->dateField($name, $value);
		$ts        = $value ? strtotime($value) : strtotime('00:00:00');
		//$ts        = strtotime('00:00:00');
		$hour      = date('H', $ts);
		$minute    = date('i', $ts);
		$second    = date('s', $ts);
		
		// Get hours.
		$hours = sprintf('<select name="%s_hour">', $name);
		for ($i = 0; $i <= 23; ++$i) {
			if ($hour == $i) {
				$hours .= sprintf('<option value="%02d" selected="selected">%02d</option>', $i, $i);
			}
			else {
				$hours .= sprintf('<option value="%02d">%02d</option>', $i, $i);
			}
		}
		$hours .= '</select>';
		
		// Get minutes.
		$minutes = sprintf('<select name="%s_minute">', $name);
		for ($i = 0; $i <= 59; ++$i) {
			if ($minute == $i) {
				$minutes .= sprintf('<option value="%02d" selected="selected">%02d</option>', $i, $i);
			}
			else {
				$minutes .= sprintf('<option value="%02d">%02d</option>', $i, $i);
			}
		}
		$minutes .= '</select>';
		
		// Get seconds.
		$seconds = sprintf('<select name="%s_second">', $name);
		for ($i = 0; $i <= 59; ++$i) {
			if ($second == $i) {
				$seconds .= sprintf('<option value="%02d" selected="selected">%02d</option>', $i, $i);
			}
			else {
				$seconds .= sprintf('<option value="%02d">%02d</option>', $i, $i);
			}
		}
		$seconds .= '</select>';
		
		return sprintf('<span class="date-time-field">%s @ %s:%s:%s</span>', $dateField, $hours, $minutes, $seconds);
	}
	
	/**
	 * Returns "up" or "down" in a span HTML element with either the class
	 * .up or .down, based on the value of $bool. True returns up, false
	 * returns down.
	 *
	 * @param bool $bool True/false value
	 * @return string Up/down
	 */
	public function serverUpDown($bool)
	{
		$class = $bool ? 'up' : 'down';
		return sprintf('<span class="%s">%s</span>', $class, $bool ? 'Up' : 'Down');
	}
	
	/**
	 * Redirect client to another location. Script execution is terminated
	 * after instructing the client to redirect.
	 *
	 * @param string $location
	 */
	public function redirect($location = null)
	{
		if (is_null($location)) {
			$location = $this->basePath;
		}
		
		header("Location: $location");
		exit;
	}
	
	/**
	 * Guess the HTTP server's current full URL.
	 *
	 * @param bool $withRequest True to include REQUEST_URI, false if not.
	 * @return string URL
	 */
	public function entireUrl($withRequest = true)
	{
		$proto    = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
		$hostname = $_SERVER['HTTP_HOST'];
		$request  = $_SERVER['REQUEST_URI'];
		
		if ($withRequest) {
			return $proto.$hostname.$request;
		}
		else {
			return $proto.$hostname.'/';
		}
	}
	
	/**
	 * Convenience method for retrieving a paginator instance.
	 *
	 * @param int $total Total number of records.
	 * @param array $options Paginator options.
	 * @return Flux_Paginator
	 * @access public
	 */
	public function getPaginator($total, array $options = array())
	{
		$paginator = new Flux_Paginator($total, $options);
		return $paginator;
	}
	
	/**
	 * Link to an account view page.
	 *
	 * @param int $accountID
	 * @param string $text
	 * @return mixed
	 * @access public
	 */
	public function linkToAccount($accountID, $text)
	{
		if ($accountID) {
			$url = $this->url('account', 'view', array('id' => $accountID));
			return sprintf('<a href="%s" class="link-to-account">%s</a>', $url, htmlentities($text));
		}
		else {
			return false;
		}
	}
	
	/**
	 * Deny entry to a page if called. This method should be used from a module
	 * script, and no where else.
	 */
	public function deny()
	{
		$location = $this->url('unauthorized');
		$this->redirect($location);
	}
	
	/**
	 * Get the full gender string from a gender letter (e.g. M for Male).
	 *
	 * @param string $gender
	 * @return string
	 * @access public
	 */
	public function genderText($gender)
	{
		switch (strtoupper($gender)) {
			case 'M':
				return 'Male';
				break;
			case 'F':
				return 'Female';
				break;
			case 'S':
				return 'Server';
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Get the account state name corresponding to the state number.
	 *
	 * @param int $state
	 * @return mixed State name or false.
	 * @access public
	 */
	public function accountStateText($state)
	{
		$text  = false;
		$state = (int)$state;
		
		switch ($state) {
			case 0:
				$text  = 'Normal';
				$class = 'state-normal';
				break;
			case 5:
				$text  = 'Permanently Banned';
				$class = 'state-permanently-banned';
				break;
		}
		
		if ($text) {
			$text = htmlspecialchars($text);
			return sprintf('<span class="account-state %s">%s<span>', $class, $text);
		}
		else {
			return false;
		}
	}
	
	/**
	 * Get the job class name from a job ID.
	 *
	 * @param int $id
	 * @return mixed Job class or false.
	 * @access public
	 */
	public function jobClassText($id)
	{
		return Flux::getJobClass($id);
	}
	
	/**
	 * Return hidden input fields containing module and action names based on
	 * the setting of UseCleanUrls.
	 *
	 * @param string $moduleName
	 * @param string $actionName
	 * @return string
	 * @access public
	 */
	public function moduleActionFormInputs($moduleName, $actionName = null)
	{	
		$inputs = '';
		if (!Flux::config('UseCleanUrls')) {
			if (!$actionName) {
				$dispatcher = Flux_Dispatcher::getInstance();
				$actionName = $dispatcher->defaultAction;
			}
			$inputs .= sprintf('<input type="hidden" name="module" value="%s" />', htmlspecialchars($moduleName))."\n";
			$inputs .= sprintf('<input type="hidden" name="action" value="%s" />', htmlspecialchars($actionName));
		}
		return $inputs;
	}
}
?>