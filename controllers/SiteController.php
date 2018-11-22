<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use yii\web\Response;

class SiteController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }


    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionAjaxLogin() {

        if (Yii::$app->request->isAjax) {

            $model = new LoginForm();

            if ($model->load(Yii::$app->request->post())) {

                if ($model->login()) {

                    return $this->goBack();
                } else {

                    Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

                    return \yii\widgets\ActiveForm::validate($model);
                }
            }
        } else {
            throw new HttpException(404 ,'Page not found');
        }
    }


    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionAddAdmin() {
        $model = User::find()->where(['username' => 'admin'])->one();
        //var_dump($model);
        if (empty($model)) {
            $user = new User();
            $user->username = 'admin';
            $user->email = 'admin@ukr.net';
            $user->setPassword('admin');
            $user->generateAuthKey();
            if ($user->save()) {
                echo 'good';
            }
        }
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
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


    public function actionHoroscope()
    {
      $username = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
      $birthday = User::findByUsername($username); 
      $znak = $this->znakZodiaka($birthday->birthday);
      return $this->render('index', ['birthday' => $birthday->birthday, 'znak' => $znak['znak'],  'horoscope' => $this->horoscope("http://hyrax.ru/cgi-bin/bn_xml2.cgi", $znak['number'])]);

    }

}
