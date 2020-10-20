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
<<<<<<< HEAD
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
        
        view($router,1,1);
        
    }
    
    
    public function parseRequest($request)
    {
        $this->beforeRequest($request);
        
        $parentRequest = parent::parseRequest($request);
        
        return $parentRequest;
=======
        $d = \izi\models\DomainPointer::findOne(['domain' => __DOMAIN__]);

        $s = $d->getS();

>>>>>>> 44278b9814da97d00202a57c7b5817697b27445f
    }

    public function validateUrl($url)
    {

    }    

}