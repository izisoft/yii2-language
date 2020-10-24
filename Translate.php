<?php 
namespace izi\language;

use Yii;
use izi\models\SiteConfigs;

class Translate extends \yii\base\Component

{

    /**
     * Translate text with lang code
     */
    public function translate($lang_code, $lang = __LANG__, $options = [])
    {
        return Yii::$app->t->translate($lang_code, $lang, $options);

    }

    /**
     * translate text use google translate by rapidapi
     */
    public function googleTranslate($q, $source, $target){
	    /**
	     *  q: text need translate
	     *  $source: lang code of original language (en, ja, my, ...)
	     *  $target: en, vi, ...
	     */
	    
	    $curl = curl_init();
	    
	    $post = [
	        'source' => $source,
	        'q' => $q,
	        'target' => $target
	    ];
	    
	    curl_setopt_array($curl, array(
	        CURLOPT_URL => "https://google-translate1.p.rapidapi.com/language/translate/v2",
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_FOLLOWLOCATION => true,
	        CURLOPT_ENCODING => "",
	        CURLOPT_MAXREDIRS => 10,
	        CURLOPT_TIMEOUT => 30,
	        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST => "POST",
	        CURLOPT_POSTFIELDS => http_build_query($post),
	        CURLOPT_HTTPHEADER => array(
	            "accept-encoding: application/gzip",
	            "content-type: application/x-www-form-urlencoded",
	            "x-rapidapi-host: google-translate1.p.rapidapi.com",
	            "x-rapidapi-key: 10a59cbe08msh76d6d9edcc375a2p1be6bbjsn2f24db62e291"
	        ),
	    ));
	    
	    $response = curl_exec($curl);
	    $err = curl_error($curl);
	    
	    curl_close($curl);
	    
	    if ($err) {
	        echo "";
	    } else {
	        $t = json_decode($response,1);	        
	        return $t['data']['translations'][0]['translatedText'];
	    }
	}
}
