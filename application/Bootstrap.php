<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initDatabase()
    {
        $file = APPLICATION_PATH . '/settings/database.php';
        $conn = include $file;
        $conn_system = $conn['type'] . '://' . $conn['user'] . ':' . $conn['pass'] . '@' . $conn['host'] . '/' . $conn['base'];

        Engine_Loader_Doctrine::setModelsDirectory(APPLICATION_PATH . '/models');
        Doctrine_Manager::connection($conn_system, 'baza');
        Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh()->query('set names utf8');
        Doctrine_Manager::getInstance()->getCurrentConnection()->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);

        foreach ($conn['attributes'] as $attr => $value) {
            Doctrine_Manager::getInstance()->setAttribute($attr, $value);
        }
    }

    protected function _initBaseUser()
    {
        // pobranie strony dla własnej domeny
        $baseUser = BaseUser::findOneByActive();

        if (!$baseUser) {
            throw new Exception('BaseUser NOT Found (' . DOMAIN . ')');
        }

        Zend_Registry::set('BaseUser', $baseUser);
        if (Zend_Registry::isRegistered('Zend_View')) {
            $view = Zend_Registry::get('Zend_View');
            $this->bootstrap('FrontController');

            // pobranie indywidualnych szablonów dla strony
            if ($baseUser->hasPrivateTemplate()) {
                $view->addScriptPath($baseUser->getPrivateRelativeTemplatePath(false));
            }

            Zend_Registry::set('Zend_View', $view);

            $view->headTitle()->setSeparator(' | ');
            $view->headMeta()->setName('description', $baseUser->getMetatagDesc());
            $view->headMeta()->setName('keywords', $baseUser->getMetatagKey());
        }

        return $baseUser;
    }

    protected function _initSession()
    {
        $configFile = APPLICATION_PATH . DS . 'settings' . DS . 'session.php';
        $options = include $configFile;

        // Session hack for fancy upload
        if (!isset($_COOKIE[session_name()])) {
            if (isset($_POST[session_name()])) {
                Zend_Session::setId($_POST[session_name()]);
            } elseif (isset($_POST['PHPSESSID'])) {
                Zend_Session::setId($_POST['PHPSESSID']);
            }
        }

        Zend_Session::setOptions($options['options']);
        $saveHandler = $options['saveHandler']['class'];
        Zend_Session::setSaveHandler(new $saveHandler($options['saveHandler']['params']['lifetime']));
        Zend_Session::start();
    }

    protected function _initLocale()
    {
        $i18n = include_once APPLICATION_PATH . '/core/i18n.php';
        $loc = Zend_Registry::get('locale');
        setlocale(LC_ALL, [$loc]);
        $locale = new Zend_Locale($loc);
        Zend_Registry::set('Zend_Locale', $locale);
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
    }

    protected function _initCache()
    {
        $this->bootstrap('cachemanager');
        $cacheManager = $this->getResource('cachemanager');
        Service_Cache::setCacheManager($cacheManager);

        $cache = Service_Cache::getCache('default');
        Zend_Registry::set('Zend_Cache', $cache); // Save in registry

        return $cache; // Save in bootstrap
    }

    /**
     * Initializes translator.
     *
     * @return Zend_Translate_Adapter
     */
    public function _initTranslate()
    {
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        $langvar = Zend_Registry::get('langvar');
        $baseUser = Zend_Registry::get('BaseUser');
        $cache = Zend_Registry::get('Zend_Cache');
        $_cache_name = 'translate_' . $baseUser->getId() . '_' . $langvar;
        $_resorces = $cache->load($_cache_name);

        if (false === $_resorces) {
            $modulesEnabled = $frontController->getControllerDirectory();
            $langTranslate = [];

            foreach ($modulesEnabled as $module => $controllerPath) {
                $moduleLangFile = $frontController->getModuleDirectory($module)
                    . DS . 'languages' . DS . $langvar . '.php';
                if (file_exists($moduleLangFile)) {
                    $langTmp = include $moduleLangFile;
                    if ($langTmp && count($langTmp) > 0) {
                        $langTranslate = array_merge($langTranslate, $langTmp);
                    }
                }
            }
            $langTranslate = array_merge($langTranslate, $baseUser->getTranslationsByLanguageCode($langvar));
            $translate = new Zend_Translate('array', $langTranslate);
            if (count($modulesEnabled) > 0) {
                $cache->save($translate, $_cache_name);
            }
        } else {
            $translate = $_resorces;
        }

        Zend_Registry::set('Zend_Translate', $translate);
        if (Zend_Registry::isRegistered('Zend_View')) {
            $view = Zend_Registry::get('Zend_View');
            $view->t = $translate;
        }

        return $translate;
    }

    public static function _initLog()
    {
        $log = new Zend_Log();

        try {
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_TMP . '/logs/main.log'));
        } catch (Exception $e) {
            // Silence ...
        }

        Zend_Registry::set('Zend_Log', $log);

        return $log;
    }

    protected function _initStripSlaeshes()
    {
        $file = $router_dir = APPLICATION_PATH . '/core/stripslaeshes.php';
        include_once $file;
    }

    protected function _initAutoload()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader(['Doctrine', 'autoload']);
        $autoloader->pushAutoloader(['Engine_Loader_Doctrine', 'modelsAutoload']);
        $autoloader->registerNamespace('VoxExpo');
        $autoloader->registerNamespace('Engine');
        $autoloader->registerNamespace('Doctrine');
        $autoloader->registerNamespace('Private');
        Zend_Controller_Action_HelperBroker::addPrefix('Engine_Controller_Action_Helper', 'Engine/Controller/Action/Helper/');

        $resourceLoader = new Zend_Loader_Autoloader_Resource(
            [
                'basePath' => APPLICATION_PATH . '/modules/default',
                'namespace' => '',
            ]
        );

        $resourceLoader->addResourceType('form', 'forms', 'Form');
        $resourceLoader->addResourceType('service', 'services', 'Service');

        return $autoloader;
    }

    protected function _initView()
    {
        // Create view
        $view = new Zend_View();

        // deklarowanie globalnych filtrów i helperów dla widoku
        // ścieżka dostępu do szablonów
        $view->setScriptPath(APPLICATION_PATH . DS . 'templates');

        // docelowo do wyrzucenia
        $view->addScriptPath(APPLICATION_PATH . DS . 'modules/default/templates');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);

        $view->doctype('HTML5');
        $view->setEncoding('UTF-8');
        $view->addHelperPath('Engine/View/Helper', 'Engine_View_Helper_');

        // Setup jquery
        $view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $view->jQuery()->enable()->uiEnable();

        Zend_Controller_Front::getInstance()->registerPlugin(new Engine_Controller_Plugin_Layout());
        Zend_Controller_Front::getInstance()->registerPlugin(new Engine_Controller_Plugin_ActionHandler());

        // Add to local container and registry
        Zend_Registry::set('Zend_View', $view);

        return $view;
    }

    protected function _initModules()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $path = APPLICATION_PATH . DS . 'modules';
        $baseUser = Zend_Registry::get('BaseUser');
        $module_list = ['default' => $path, 'admin' => $path];
        $modules_config = [];

        // zczytanie listy indywidualnych modułów klienta, jeśli takie są podane
        $dir = new DirectoryIterator($path);
        foreach ($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $module = $file->getFilename();

            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' === $module)) {
                continue;
            }

            $module_list[$module] = $file->getPath();
        }

        // wrzucenie do rejestru aktualnych informacji o BaseUser
        // dodane zostały włączone moduły
        // wrzucamy, aby później nie wykopnywać jeszcze raz tego samego zapytania
        // jeśli byłaby nam potrzebna gdziekolwiek
        Zend_Registry::set('BaseUser', $baseUser);

        // zczytanie listy indywidualnych modułów klienta, jeśli takie są podane
        if ($baseUser->hasPrivateModules()) {
            try {
                $dir = new DirectoryIterator($baseUser->getPrivateModulesPath(false));
            } catch (Exception $e) {
                require_once 'Zend/Controller/Exception.php';

                throw new Zend_Controller_Exception("Directory {$path} not readable", 0, $e);
            }

            foreach ($dir as $file) {
                if ($file->isDot() || !$file->isDir()) {
                    continue;
                }

                $module = $file->getFilename();

                // Don't use SCCS directories as modules
                if (preg_match('/^[^a-z]/i', $module) || ('CVS' === $module)) {
                    continue;
                }

                $module_list[$module] = $file->getPath();
            }
        }

        foreach ($module_list as $module => $module_path) {
            $moduleDir = $module_path . DS . $module;
            $configFile = $moduleDir . DS . 'settings' . DS . 'config.php';

            if (file_exists($configFile) && is_dir($moduleDir)) {
                $config = [];
                $config = include_once $configFile;
                if ($config[$module]['enabled']) {
                    $controllerDir = $moduleDir . DS . $frontController->getModuleControllerDirectoryName();
                    $config[$module]['path'] = $controllerDir;
                    $modules_config = array_merge_recursive($modules_config, $config);
                    $frontController->addControllerDirectory($controllerDir, $module);
                    $module_config[$module] = $config;

                    $bootstrapClass = ucfirst($module) . '_Bootstrap';
                    if (!class_exists($bootstrapClass, false)) {
                        $bootstrapPath = $moduleDir . '/Bootstrap.php';
                        if (file_exists($bootstrapPath)) {
                            include_once $bootstrapPath;
                            if (!class_exists($bootstrapClass, false)) {
                                throw new Zend_Application_Resource_Exception('Bootstrap file found for module "' . $module . '" but bootstrap class "' . $bootstrapClass . '" not found');
                            }
                        } else {
                            continue;
                        }

                        $moduleBootstrap = new $bootstrapClass($this);
                        $moduleBootstrap->bootstrap();
                        $bootstraps[$module] = $moduleBootstrap;
                    }
                }
            } else {
                // Maybe we should log modules that fail to load?
                if (APPLICATION_ENV === 'development') {
                    throw new Zend_Exception('failed to load module "' . $module . '"');
                }
            }
        }

        $baseUserConfig = $baseUser->getSettingsFromFile('config', true);
        $modules_config = array_merge_recursive($modules_config, $baseUserConfig);

        Zend_Registry::set('ModulesConfig', $modules_config);

        return $bootstraps;
    }

    protected function _initRouter()
    {
        include_once 'Zend/Controller/Front.php';
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->getRouter()->removeDefaultRoutes();
        $baseUser = Zend_Registry::get('BaseUser');

        $this->bootstrap('modules');
        $modulesEnabled = $frontController->getControllerDirectory();
        $modulesLoadOrder['default'] = $modulesEnabled['default'];
        $modulesLoadOrder = array_merge($modulesLoadOrder, $modulesEnabled);

        $cache = Zend_Registry::get('Zend_Cache');
        $_router_cache_name = 'Router_' . $baseUser->getId() . '_' . Zend_Registry::get('langvar');
        $_router_resorces = $cache->load($_router_cache_name);

        if (false === $_router_resorces) {
            foreach ($modulesLoadOrder as $module => $path) {
                $dir = $frontController->getModuleDirectory($module) . DS . 'router';
                if (file_exists($dir) && is_dir($dir)) {
                    foreach (scandir($dir) as $file) {
                        $full_path_file = $dir . DS . $file;
                        if ('.' !== $file[0] && true === is_file($full_path_file) && '.php' === mb_substr($file, mb_strrpos($file, '.'))) {
                            require_once $full_path_file;
                        }
                    }
                }
            }

            $cache->save($frontController->getRouter(), $_router_cache_name);
        } else {
            $frontController->setRouter($_router_resorces);
        }
    }

    protected function _initAcl()
    {
        $acl = Engine_Acl::getInstance();

        if (!$acl->isLoadedFromCache()) {
            $acl->loadFromBaseUser();

            $cache = Zend_Registry::get('Zend_Cache');
            $baseUser = Zend_Registry::get('BaseUser');

            $cache->save($acl, 'Enigne_Acl_Resources_' . $baseUser->getId());
        }

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($acl);
        Zend_Controller_Front::getInstance()->registerPlugin(new Engine_Controller_Plugin_Acl());
        Zend_Registry::set('Engine_Acl', $acl);

        $view = Zend_Registry::get('Zend_View');
        $view->acl = $acl;

        // Wymuszenie logowania
        $isAllowedPage = $this->isAllowedPage($_SERVER['REQUEST_URI'], [
            "/",
            "/admin",
            "/api/user/create",
            "/api/user/update",
            "/wydarzenie/targipracyposl/login",
        ]);
        if (!$isAllowedPage && !Zend_Auth::getInstance()->hasIdentity() && time() <= strtotime('18-10-2021 06:00:00')) {
            header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']);
            exit;
        }

        return $acl;
    }


        // Wymuszenie logowania
        protected function isAllowedPage(string $string, array $array)
        {
            $path = parse_url($string);

            $query = [];
            if (isset($path['query'])) {
                parse_str($path['query'], $query);
            }

            if (in_array($path['path'], $array) || (isset($query['token']) && $query['token'] == 'nfjiaoph73ef90ds9af')) {
                return true;
            }

            return false;
        }

########################################################################
//   protected function _initZFDebug()
//    {
//        if( APPLICATION_ENV != 'development' && true ){
//            return;
//        }
//
//        $autoloader = Zend_Loader_Autoloader::getInstance();
//        $autoloader->registerNamespace('ZFDebug');
//
//        $options = array(
//            'plugins' => array(
//                'Variables',
//                'Html',
//                'ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine',
////                'Database' => array('adapter' => array( 'standard' => Zend_Registry::get('db') ) ),
//                'File' => array('basePath' => APPLICATION_PATH ),
//                'Memory',
//                'Time',
//                'Registry',
////                'Cache' => array('backend' => Zend_Registry::get('Zend_Cache') ),
//                'Exception')
//        );
//
//        $debug = new ZFDebug_Controller_Plugin_Debug($options);
//
//        $this->bootstrap('frontController');
//        $frontController = $this->getResource('frontController');
//        $frontController->registerPlugin($debug);
//    }
}
