<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use yii\web\Cookie;

class Prize extends \yii\base\BaseObject
{
    const CASH = 1;
    const BONUS_POINTS = 2;
    const PHYSICAL_ITEMS = 3;
    const CASH_PRIZE_LIMIT = 5;
    const PHYSICAL_ITEMS_LIMIT = 5;
    const EXCHANGE_RATE = 10;  //exchangeRate 1 cash unit = 10 bonus points

    protected $cashRange = [1, 100]; //cash amount is in the interval 1 to 100

    /** @var array $prizeNames */
    protected $prizeNames = [
        self::CASH => 'Cash',
        self::BONUS_POINTS => 'Bonus points',
        self::PHYSICAL_ITEMS => 'Physical items',
    ];

    /** @var int */
    protected $exchangeRate = 10;  //exchangeRate 1 cash unit = 10 bonus points

    /** @var array  */
    protected $bonusPointsRange = [1, 1000];

    /** @var array  */
    protected $physicalItems = ['item1', 'item2', 'item3', 'item4']; //list of items which can be chosen

    /**
     * Prize constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public static function getUserCashPrizeCnt($userId)
    {
        //Here we use cookies for saving limit for simple example but this info should be saved in database
        if (!isset(Yii::$app->request->cookies[$userId . '-cashPrizeCnt']->value)) {
            Yii::$app->request->cookies->getValue($userId . '-cashPrizeCnt', 0);
        }
        return Yii::$app->request->cookies->getValue($userId . '-cashPrizeCnt');
    }

    /**
     * @param $userId
     * @param $cnt
     */
    public static function setUserCashPrizeCnt($userId, $cnt)
    {
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => $userId . '-cashPrizeCnt',
            'value' => $cnt,
        ]));
    }

    public static function exchangeCashToBonusPoints($cashAmount, $exchangeRate = self::EXCHANGE_RATE)
    {
        return $cashAmount * $exchangeRate;
    }

    public function getCashRange()
    {
        return $this->cashRange;
    }
    /**
     * @param $cashRange
     */
    public function setCashRange($cashRange)
    {
        $this->cashRange = $cashRange;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * @param $exchangeRate
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return array
     */
    public function getBonusPointsRange()
    {
        return $this->bonusPointsRange;
    }

    /**
     * @param array $bonusPointsRange
     */
    public function setBonusPointsRange($bonusPointsRange)
    {
        $this->bonusPointsRange = $bonusPointsRange;
    }

    /**
     * @return array
     */
    public function getPhysicalItems()
    {
        return $this->physicalItems;
    }

    /**
     * @param array $physicalItems
     */
    public function setPhysicalItems($physicalItems)
    {
        $this->physicalItems = $physicalItems;
    }

    /**
     * @return array
     */
    public function getPrizeNames()
    {
        return $this->prizeNames;
    }

    /**
     * @param array $prizeNames
     */
    public function setPrizeNames($prizeNames)
    {
        $this->prizeNames = $prizeNames;
    }

    public function processChosenPrize($values)
    {
        $userId = !empty($values['userId'])? $values['userId'] : Yii::$app->user->identity->id;
        if ($values['prizeType'] == Prize::CASH) {
            $amount = Yii::$app->request->cookies->getValue($values['userId'] . '-cashAmount', 0);
            $amount = $amount + $values['cashAmount'];
            Yii::$app->response->cookies->remove($values['userId'] . '-cashAmount');
            self::setUserCashAmount($userId, $amount);
        } elseif ($values['prizeType'] == Prize::BONUS_POINTS) {
            $amount = Yii::$app->request->cookies->getValue($values['userId'] . '-bonusPointsAmount', 0);
            $amount = $amount + $values['bonusPointsAmount'];
            Yii::$app->response->cookies->remove($values['userId'] . '-bonusPointsAmount');
            self::setUserBonusPoints($userId, $amount);
        } else {
            $items = Yii::$app->request->cookies->getValue($values['userId'] . '-items', array());
            if (!empty($items)) {
                $items = json_decode($items);
            }
            if (!in_array($values['item'], $items)) {
                array_push($items, $values['item']);
            }
            Yii::$app->response->cookies->remove($values['userId'] . '-items');
            self::setUserItems($userId, json_encode($items));
        }
        return;
    }

    /**
     * @param $userId
     * @return int
     */
    public static function getUserCashAmount($userId)
    {
        return Yii::$app->response->cookies->getValue($userId . '-cashAmount');
    }

    /**
     * @param $userId
     * @param $userCashAmount
     */
    public static function setUserCashAmount($userId, $userCashAmount)
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $userId . '-cashAmount',
            'value' => $userCashAmount,
        ]));
    }

    /**
     * @param $userId
     * @return int
     */
    public static function getUserBonusPoints($userId)
    {
        return Yii::$app->response->cookies->getValue($userId . '-bonusPointsAmount');
    }

    /**
     * @param int $userId
     * @param int $userBonusPoints
     */
    public static function setUserBonusPoints($userId, $userBonusPoints)
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $userId . '-bonusPointsAmount',
            'value' => $userBonusPoints,
        ]));
    }

    /**
     * @param $userId
     * @return array
     */
    public static function getUserItems($userId)
    {
        $items = array();
        $userItems = json_decode(Yii::$app->response->cookies->getValue($userId . '-items'));
        foreach ($userItems as $item) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param $userId
     * @param $userItems
     */
    public static function setUserItems($userId, $userItems)
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $userId . '-items',
            'value' => $userItems,
        ]));
    }

}