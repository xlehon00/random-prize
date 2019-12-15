<?php

namespace app\controllers;

use app\models\ChoosePrizeForm;
use app\models\Prize;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
     * @return Response|string
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

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
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

    /**
     * Say hello
     */
    public function actionSay($message = 'Hello')
    {
        return $this->render('Say', ['message' => $message]);
    }

    /**
     * Choose the prize
     */
    public function actionChoosePrize()
    {
        $model = new ChoosePrizeForm();
        $prizeModel = new Prize();
        $exchange = false;
        $refuse = false;
        $confirm = false;
        $exchangeByCashLimited = false;
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();
            $formValues = $post['ChoosePrizeForm'];
            if (isset($formValues['cashAmount']) && isset($post['exchange-prize-button'])) {
                Yii::$app->session->setFlash('exchangeCashSubmitted');
                $prizeType = Prize::BONUS_POINTS;
                $amount = $formValues['cashAmount'] * Prize::EXCHANGE_RATE;
                $item = null;
                $exchange = true;
                $prizeModel->processChosenPrize($formValues);
            } elseif (isset($formValues['refuse']) && isset($post['refuse-prize-button'])) {
                Yii::$app->session->setFlash('refuseItemSubmitted');
                $prizeType = Prize::PHYSICAL_ITEMS;
                $amount = null;
                $item = $formValues['item'];
                $refuse = true;
            } elseif (isset($post['confirm-prize-button'])) {
                Yii::$app->session->setFlash('confirmPrizeSubmitted');
                $confirm = true;
                $prizeType = $formValues['prizeType'];
                $amount = isset($formValues['amount'])? $formValues['amount'] : null;
                $item = isset($formValues['item'])? $formValues['item'] : null;
                $prizeModel->processChosenPrize($formValues);
            } else {
                Yii::$app->session->setFlash('choosePrizeFormSubmitted');
                $values = $model->chooseRandomPrize(Yii::$app->user->id);
                if (isset($values['exchangedByCashLimited'])) {
                    $exchangeByCashLimited = true;
                }
                $prizeType = $values['prizeType'];
                $amount = isset($values['amount']) ? $values['amount'] : null;
                $item = isset($values['item']) ? $values['item'] : null;
            }
            return $this->render('ChoosePrize', [
                'model' => $model,
                'prizeType' => $prizeType,
                'prizeName' => $prizeModel->getPrizeNames()[$prizeType],
                'amount' => $amount,
                'exchangedByCashLimited' => $exchangeByCashLimited,
                'item' => $item,
                'exchange' => $exchange,
                'refuse' => $refuse,
                'confirm' => $confirm,
            ]);
        }
        return $this->render('ChoosePrize', [
            'model' => $model,
        ]);
    }
}
