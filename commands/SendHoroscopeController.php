<?php

namespace app\commands;

use yii\console\Controller;
use app\models\User;

class SendHoroscopeController extends Controller
{

    public function actionIndex()
    {
    	$email_file = @fopen('email_file.txt', 'w');

    	foreach (User::find()->select(['username','birthday', 'email'])->All() as $user)
    	 {

            $znak = $this->znakZodiaka($user->birthday);
            $horoscope = $this->horoscope("http://hyrax.ru/cgi-bin/bn_xml2.cgi", $znak['number']);

    		$message = 'To: '.$user->email.PHP_EOL;
    		$message .= 'From: admin@ukr.net'.PHP_EOL;
    		$message .= 'Subject: Гороскоп для '.$user->username.' на '.date('d-m-Y').PHP_EOL;
    		$message .= 'Message: '.$horoscope.PHP_EOL.PHP_EOL;

            fwrite($email_file, $message);

        }

            fclose($email_file);
    }

    public function horoscope($url, $sign)
    {
     $xml=file_get_contents($url);
     $dom=new \DOMDocument();
     $dom->loadXML($xml);
     $s_dom=simplexml_import_dom($dom);
     if ($sign==""||$sign>12) return " Неверный знак зодиака! ";
     return $s_dom->channel->item[$sign]->description;
    }

    public function znakZodiaka($data)
    {
     $day = str_replace("-","",substr($data,5));
     $zodiak = array('ot' => array('0120','0219','0321','0421','0521','0622','0723','0823','0923','1024','1123','1222','0101'),
                     'do' => array('0218','0320','0420','0520','0621','0722','0822','0922','1023','1122','1221','1231','0119'),
                     'znak' => array('Водолей','Рыбы','Овен','Телец','Близнец','Рак','Лев','Дева','Весы','Скорпион','Стрелец','Козерог','Козерог'));
     $i = 0;
     while (empty($znak) && ($i < 13)){
       $znak = (($zodiak['ot'][$i] <= $day) && ($zodiak['do'][$i] >= $day)) ? $zodiak['znak'][$i] : null;
       ++$i;
     } 
     return array('number' => $i, 'znak' => $znak);
    }

}
