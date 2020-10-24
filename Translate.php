<?php 
namespace izi\language;

use Yii;
use izi\models\SiteConfigs;

class Translate extends \yii\base\Component

{

    /**
     * 
     */
    public function translate($lang_code, $lang = __LANG__, $options = [])
    {
        return Yii::$app->t->translate($lang_code, $lang, $options);

    }

    /**
     * 
     */
    public function googleTranslate($q, $source, $target)
    {

    }
}
