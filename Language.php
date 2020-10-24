<?php 
namespace izi\language;

use Yii;
use izi\models\SiteConfigs;

class Language extends \yii\base\Component

{
    public $identity = 'LANGUAGE2';
    
    private $_model ;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject(Model::class);
        }
        return $this->_model;
    }
    
    public function getDefault(){
        
        $l = SiteConfigs::getConfigs($this->identity, null, __SID__);
        
        if(!empty($l)){
            foreach ($l as $v){
                if(isset($v['is_default']) && $v['is_default'] == 1){
                    return $v;
                }
            }
        }
        
    }
	
	/**
	*	get language by code
	*/
	
	public function getItem($code, $fetchArray = false){
		$l = SiteConfigs::getConfigs($this->identity, null, __SID__);
 
		
        if(!empty($l)){
            foreach ($l as $v){
                 
                
                if(is_numeric($code) && isset($v['id']) && $v['id'] == $code){
                    return $fetchArray ? $v : (object) $v;
                }
                
                if(isset($v['code']) && $v['code'] == $code){
                    
                    
                    return $fetchArray ? $v : (object) $v;
                }
            }
        }
        
        $v = ( $this->getModel()->getItem($code));
         
        return $fetchArray ? $v : (object) $v;
	}
	
	// return attr
	
	
	public function getId($lang = __LANG__){
		return $this->getItem($lang)->id;
	}
	
	public function getCode($lang = __LANG__){
		return $this->getItem($lang)->code;
	}
	
	public function getLangCode($lang = __LANG__){
	     
	    
	    if(!empty($item = $this->getItem($lang)))
	    return $item->code;
	}
	public function getTranslateCode($lang = __LANG__){
	     
	    
	    if(!empty($item = $this->getItem($lang)))
	        return $item->lang_code;
	}
	
	
	public function getLangTitle($lang = __LANG__){
	    if(!empty($item = $this->getItem($lang)))
	        return $item->title;
	}
	
	public function getIso2($lang = __LANG__){
		return $this->getItem($lang)->iso2;
	}
	
	public function getIso639($lang = __LANG__){
		return $this->getItem($lang)->iso639;
	}
	
	
	public function getIso639a2($lang = __LANG__){
		return $this->getItem($lang)->iso639a2;
	}
	
	public function getLanguage($lang = __LANG__){
	    if(!empty($item = $this->getItem($lang)))
	        return $item->language; 
	}
	
	
	public function getLocale($lang = __LANG__){
	    if(!empty($item = $this->getItem($lang))){
	        
	         
	        return $item->locale;
	    }
	}
    public function getRegion($lang = __LANG__){
		
		if(!empty($item = $this->getItem($lang)))
		    return $item->region;
	}
	
	/**
	 * get all defined language for single site
	 */
	public function getDefinedLanguage(){ 
	    return SiteConfigs::getConfigs($this->identity, null, __SID__, false);
	}
    
	/**
	 * Add languge for single site
	 */
	public function add($language){
	    $items = $this->getDefinedLanguage();
	    
	    $item = $this->getModel()->getItem($language);
	     
	    if(!empty($item)){
	        
	        foreach ($items as $k=>$v){
	            if($v['code'] == $item['code']){
	                unset($items[$k]);
	                $items = array_values($items);
	            }
	        }
	        
	        $item['is_active'] = 1;
	        $item['root_active'] = 1;
	        $item['is_default'] = 0;
	        
	        $items[] = $item;
	        
	        $conditions = [
	            'code'=>$this->identity,
	            'sid'=>__SID__,
	            'lang'=>SYSTEM_LANG
	        ];
	        
	        
	        SiteConfigs::updateData($items, $conditions, true);
	    }
	    
	    
	    
	    
	}
	
	public function update($language, $field, $value){
	    $items = $this->getDefinedLanguage();
	    $item = $this->getItem($language, true);
	    
	    
	    if(!empty($items)){
	        foreach ($items as $k=>$v){ 
	            if($v['code'] == $item['code']){
	                
	                
	                $v[$field] =   $value;
	                
	                $items[$k] = $v;
	                
	                if($field == 'is_default'){
	                    $items[$k]['is_default'] = 1;
	                    continue;
	                }else{
	                
	                   break;
	                }
	            }else{
	                if($field == 'is_default'){
	                    $items[$k]['is_default'] = 0;
	                    continue;
	                }
	            }
	        }
	    }
	    
	    $conditions = [
	        'code'=>$this->identity,
	        'sid'=>__SID__,
	        'lang'=>SYSTEM_LANG
	    ];
	    
	   
	    
	    SiteConfigs::updateData($items, $conditions, true);
	}
	
	/**
	 * Remove languge for single site
	 */
	public function remove($language){
	    $items = $this->getDefinedLanguage();
	    
	    $item = $this->getModel()->getItem($language);
	    
	    if(!empty($item)){
	        
	        foreach ($items as $k => $v){
	            if($v['code'] == $item['code']){
	                unset($items[$k]);
	                $items = array_values($items);
	            }
	                
	        }
	        
	        $conditions = [
	            'code'=>$this->identity,
	            'sid'=>__SID__,
	            'lang'=>SYSTEM_LANG
	        ];
	        
	        
	        SiteConfigs::updateData($items, $conditions, true);
	    }
	    
	    
	    
	    
	}
	
	/**
	 * refresh languge (updated)
	 */
	public function refresh(){
	    $l = SiteConfigs::getConfigs($this->identity, null, __SID__, false);
	   
	    $items = [];
	    $df = 0;
	    foreach ($l as $k => $item) {
	        if(is_numeric($k)){
	            
	            $item = $this->getModel()->getItem($item['id']);
	            
	            $i2 = $this->getItem($item['code'],true);
	             
// 	            if(($item['code']) == SYSTEM_LANG){
// 	                $item['is_default'] = $item['root_active'] = $item['is_active'] = 1;
// 	            }
	            
	            $item['is_default'] = isset($i2['is_default'] ) ? $i2['is_default']  : 0;
	            $item['root_active'] = isset($i2['root_active'] ) ? $i2['root_active']  : 0;
	            $item['is_active'] = isset($i2['is_active'] ) ? $i2['is_active']  : 0;
	            
	            if($df == 0 && $item['is_default'] == 1){ 
	                $df = 1;
	            }else{
	                $item['is_default'] = 0;
	            }
	            
	            
// 	            $item['root_active'] = $item['is_active'] = 1;
	            
	            $items[$k] = $item;
	        }
	    }
	    
	     
	    if($df = 0){
	        foreach ($items as $k => $item) {
	            if(($item['code']) == SYSTEM_LANG){
	                $item['is_default'] = $item['root_active'] = $item['is_active'] = 1;
	                $items[$k] = $item;
	                break;
	            }
	        }
	    }
	    
	    $conditions = [
	        'code'=>$this->identity,
	        'sid'=>__SID__,
	        'lang'=>SYSTEM_LANG
	    ];
	     
	    
	    SiteConfigs::updateData($items, $conditions, true);
	}
	
	
	/**
	 * set default language (first setup)
	 * @return string
	 */
    public function initDefaultLanguage(){
        
        $items = Yii::$app->l->model->getDefault();
        
        foreach ($items as $k => $item) {
            if(($item['code']) == SYSTEM_LANG){
                $item['is_default'] = $item['root_active'] = $item['is_active'] = 1;
                
                $items[$k] = $item;
            }
        }
        
        $conditions = [
            'code'=>$this->identity,
            'sid'=>__SID__,
            'lang'=>SYSTEM_LANG
        ];
         
        
        SiteConfigs::updateData($items, $conditions);
        
        
        return SYSTEM_LANG;
    }
    
 
    public function getUserLanguage()
    {
        $r = Yii::$app->getConfigs($this->identity,false,__SID__,false);
        
        if(!empty($r)){
            foreach ($r as $k=>$v){
                if(isset($v['root_active']) && $v['root_active'] == 1 && isset($v['is_active']) && $v['is_active'] == 1){}else{unset($r[$k]);}
            }
        }
        
        return $r;
    }
    
    /**
     * old function
     */
    public function getAllLanguage($params = [])
    {
         
        return $this->getModel()->getAllLanguage($params);
        
    }
    
    
    /**
     * Update 10/20
     */
        
    
    
    
    
    
    
}
