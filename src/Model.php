<?php 
namespace izi\language;

class Model extends \yii\db\ActiveRecord

{
    public static function tableName()
    {
        return '{{%languages}}';
    }
    
    public function getDefault()
    {
        return static::find()->where(["code"=> ['vi-VN','en-US']])->asArray()->all();
        
    }
    
    
    public function getItem($id)
    {
        if(is_numeric($id)){
            return static::find()->where(["id"=> $id])->asArray()->one();
        }else{
            return static::find()->where(["code"=> $id])->asArray()->one();
        }
        
    }
    
    
    
    public function getAllLanguage($params = []){
        $query = static::find()->from(Model::tableName());
        
        if(isset($params['not_in']) && !empty($params['not_in'])){
            $query->andWhere(['not in','code',$params['not_in']]);
        }
        $query->andWhere(['>','state',0]);
        
        return $query->asArray()->orderBy(['title'=>SORT_ASC])->all();
    }
    
 
}
