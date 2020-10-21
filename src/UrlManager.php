<?php
namespace izi\router;

use Yii;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * array [
     *  'module',
     *  'controller',
     *  'action'
     * ]
     */
    private $_router;

    /**
     * {@inheritDoc}
     * @see \yii\web\UrlManager::init()
     */
    
    public function init()
    {
        parent::init();

        /**
         * Defined global variable
         */
        foreach($this->getServerInfo() as $k=>$v){
            defined($k) or define($k,$v);
        }
        
        /**
         * Set default theme
         */
        Yii::$app->view->theme = new \izi\theme\Theme([
            'basePath'   =>  "@app/web",
            'viewPath'   =>  "@app/views",
        ]);
         
    }

    /**
     * return all module in config file
     * array = [
     *      'admin',
     *      'api',
     *      ...
     * ]
     */

    private $_moduleNames;
    public function getModuleNames(){
        if($this->_moduleNames == null){
            $this->_moduleNames = array_keys(Yii::$app->getModules());
        }
        return $this->_moduleNames;
    }

    /**
     * get server info
     */

    protected function getServerInfo(){

        $s = $_SERVER;  $ssl = false;
        
        if(isset($s['HTTPS']) && $s['HTTPS'] == 'on'){
            $ssl = true;
        }elseif(isset($s['HTTP_X_FORWARDED_PROTO']) && strtolower($s['HTTP_X_FORWARDED_PROTO']) == 'https'){
            $ssl = true;
        }else{
            $ssl = (isset($s['SERVER_PORT']) && $s['SERVER_PORT'] == 443) ? true: false;
        }        
        
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        
        $SERVER_PORT = isset($s['SERVER_PORT']) ? $s['SERVER_PORT'] : 80;
        
        $port = $SERVER_PORT;
        $port = in_array($SERVER_PORT , ['80','443']) ? '' : ':'.$port;        
        
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME']);
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['HTTP_X_ORIGINAL_URL']) ? $_SERVER['HTTP_X_ORIGINAL_URL'] : $_SERVER['QUERY_STRING']);
        $url = $protocol . '://' . $host . $port . $path;
        $pattern = ['/index\.php\//','/index\.php/'];
        $replacement = ['',''];
        $url = preg_replace($pattern, $replacement, $url);
        $a = parse_url($url);
        $a['host'] = strtolower($a['host']);
                 
        return [
            'FULL_URL'=>$url,
            'URL_NO_PARAM'=> $a['scheme'].'://'.$a['host'].$port.$a['path'],
            'URL_WITH_PATH'=>$a['scheme'].'://'.$a['host'].$port.$a['path'],
            'URL_NOT_SCHEME'=>$a['host'].$port.$a['path'],
            'ABSOLUTE_DOMAIN'=>$a['scheme'].'://'.$a['host'],
            'URL_QUERY'=>isset($a['query']) ? $a['query'] : '',
            'DYNAMIC_SCHEME_DOMAIN'  =>  '//'.$a['host'].$port,
            'SITE_ADDRESS'=>$a['scheme'].'://'.$a['host'].$port,
            'SCHEME'=>$a['scheme'],
            'DOMAIN'=>$a['host'],
            "__DOMAIN__"=>$a['host'],
            'DOMAIN_NOT_WWW'=>preg_replace('/www./i','',$a['host'],1),
            'URL_NON_WWW'=>preg_replace('/www./i','',$a['host'],1),
            'URL_PORT'=>$port,
            'URL_PATH'=>$a['path'],
            '__TIME__'=>time(),
            'DS' => DIRECTORY_SEPARATOR,
            'ROOT_USER'=>'root',
            'ADMIN_USER'=>'admin',
            'DEV_USER'=>'dev',
            'DEMO_USER'=>'demo',
            'USER'=>'user'
        ];
    }
    
     

    /**
     * init data from current domain
     * => get sid/website_id/store_id from domain
     */

    public function parseDomain($domain = __DOMAIN__)
    {
        $params = [
            __CLASS__,
            __FUNCTION__,
            $domain,
            date('H')
        ];
        
        $config = Yii::$app->icache->getCache($params);
        
        if(!YII_DEBUG && !empty($config)){
            return $config;
        }else{
            
        
            $d = \izi\models\DomainPointer::findOne(['domain' => $domain]);
    
            $s = $d->getS()->one();
            
            if(!empty($s)){
                $config = [
                    'sid' => $s->id,
                    'code' => $s->code,
                    'is_hidden' => $d->is_hidden,
                    'module' => $d->module,
                    'store_id' => isset($d->store_id) ? $d->store_id : 0,
                    'store_group_id' => isset($d->store_group_id) ? $d->store_group_id : 0,
                    'store_website_id' => isset($d->store_website_id) ? $d->store_website_id : 0,
                ];
                
                Yii::$app->icache->store($config, $params);
                
                
                return $config;
            }
        
        }
    }
    
    
    /**
     * modify request
     */
    
    public function beforeRequest($request)
    {
        // Parse domain
        $s = $this->parseDomain();
        
        $DOMAIN_HIDDEN =  $domain_module = false; $domain_module_name = '';
        if(!empty($s)){
            
            define ('__SID__', $s['sid']);
        
            define ('__SITE_NAME__', $s['code']);                                   
            
            if($s['module'] != "" && in_array($s['module'], $this->getModuleNames())){
                $this->_router['module'] = $domain_module_name = $s['module'];
                $domain_module = true;
                
            }
            
            $DOMAIN_HIDDEN = $s['is_hidden'];
            
        }
        
        //
        defined('DOMAIN_HIDDEN') or define('DOMAIN_HIDDEN', $DOMAIN_HIDDEN);
        defined('__DOMAIN_MODULE__') or define('__DOMAIN_MODULE__', $domain_module);
        defined('__DOMAIN_MODULE_NAME__') or define('__DOMAIN_MODULE_NAME__', $domain_module_name);
        
        
        // Parse router
        
        $router = array_filter(explode(DIRECTORY_SEPARATOR, trim(URL_PATH, DIRECTORY_SEPARATOR)));
        
        if(!empty($router)){
            if(!isset($this->_router['module']) && in_array($router[0], $this->getModuleNames())){
                $this->_router['module'] = array_shift($router);
            }
            
            if(!empty($router)){
                foreach ($router as $v) {
                    
                    if(isset(Yii::$app->view->specialPage) && !(DOMAIN_LAYOUT != "" && in_array(DOMAIN_LAYOUT, Yii::$app->view->specialPage))
                        && in_array($v, Yii::$app->view->specialPage)){
                            define('SPECIAL_LAYOUT', array_shift($router));
                    }
                    
                    break;
                }
                foreach ($router as $k=>$v) {
                    switch ($k) {
                        case 0: // controller
                            $this->_router['controller'] = $v;
                            break;
                        case 1: // action
                            $this->_router['action'] = $v;
                            break;
                            
                        default:
                            $this->_router["param" . ($k-1)] = $v;
                            break;
                    }
                }
            }
            
        }
        
        if(isset($this->_router['module']) && $this->_router['module'] != ""){
        
            $this->addRules([                
                '/'=>$this->_router['module'] . "/default/index",
                '<module:\w+>/<alias:login|logout|forgot>'=>'<module>/default/<alias>',
            ]);
            
            // set rule for module
            define('__IS_MODULE__',true);
            
            $method_name = "parse". ucfirst($this->_router['module'])."Request";
            
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', $this->_router['module']);
            
            defined('__DOMAIN_ADMIN__') || define('__DOMAIN_ADMIN__',__DOMAIN_MODULE__);
            
            defined('MODULE_ADDRESS') || define('MODULE_ADDRESS', __DOMAIN_MODULE__ ? cu(['/']) : cu(['/' . __MODULE_NAME__]));
            
            
            
            Yii::$app->user->loginUrl = [
                
                (defined('__DOMAIN_MODULE__') && __DOMAIN_MODULE__ ? '' : __MODULE_NAME__) . '/login'
                
            ];
            $request->router = $this->_router;
            $moduleClass = "\\app\\modules\\{$this->_router['module']}\\Module";
            
            
            if(method_exists($moduleClass, 'parseRequest')){
                $moduleClass::parseRequest($request, $this);
                
                
            }else{
                
                if(method_exists($this, $method_name)){
                    $this->$method_name($request);
                }
            }
            
            
            //  Setup language
            $this->setLanguage($this->_slug);
            // Setup template
            $this->setTemplate($this->_router);
        
        }else{
            $this->addRules([
                '/'=>Yii::$app->defaultRoute . "/index",
            ]);
            
            // set rule for frontend
            define('__IS_MODULE__',false);
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', 'app-frontend');
            
            $this->parseFrontendRequest($request);
            
            
        }
        
         

        // Pause
        
    }
            
    /**
     * 
     * {@inheritDoc}
     * @see \yii\web\UrlManager::parseRequest()
     */
    
    public function parseRequest($request)
    {
        $this->beforeRequest($request);
        
        $parentRequest = parent::parseRequest($request);
        
        return $parentRequest;
    }


    /**
     * frontend request
     */
    public function parseFrontendRequest($request)
    {
        
        $is_validate_url = false;
        
        $fp = dirname(Yii::$app->view->theme->getPath('')) . DIRECTORY_SEPARATOR . '/rule.custom.php';
        
        if(file_exists($fp)){
            
            $rule = require_once $fp;
            
            if(!empty($rule)){
                $this->addRules($rule);
            }
            
        }
        
        
        // parse slug
        
        $detail_url = '';
        
        $isDetail = false;
        if(!empty($this->_router)){
            foreach ($this->_router as $k=>$v) {
                
                $detail_url = $v;
                
                if($is_validate_url) break;
                
                $br = false;
                switch ($k) {
                    case 'controller':
                        
                        
                        break;
                }
            }
        }
        
        view($this->_router,1,1);
        
        /////////////////////////////////////
        
    }
    
    
    
    
    
    
}