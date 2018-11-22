<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Гороскоп';

?>
<div class="site-index">
   <div class="jumbotron">

      <?php 
         if(!Yii::$app->user->isGuest)
         { 
           echo  Html::a('Гороскоп для  '.ucfirst(Yii::$app->user->identity->username), [\yii\helpers\Url::to('/site/horoscope')], ['class' => 'btn btn-lg btn-success', 'data' => ['method' => 'post', 'params' => ['user' => Yii::$app->user->identity->username]]]);

            Pjax::begin(); 

            if(isset($horoscope))
               echo ucfirst(Yii::$app->user->identity->username).' ('.date("d-m-Y", strtotime($birthday)).' '.$znak.'), Ваш гороскоп на '.date('d-m-Y').' - '.$horoscope;
 
            Pjax::end();  
         }

      ?>

   </div>
  
</div>
