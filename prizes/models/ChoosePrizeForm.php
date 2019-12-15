<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ChoosePrizeForm is a model behind choose prize form
 */
class ChoosePrizeForm extends Model
{
    public $userId;
    public $cashAmount;
    public $prizeType;
    public $amount;
    public $bonusPointsAmount;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['userId','default','value' => Yii::$app->user->identity->id]
        ];
    }

    public function chooseRandomPrize($userId)
    {
        $prizeModel = new Prize();
        $values = array(); //in the array we store infos about the type of prize and its amount or item
        $prizeType = rand(Prize::CASH, Prize::PHYSICAL_ITEMS);
        if ($prizeType == Prize::CASH) {
            $cashRange = $prizeModel->getCashRange();
            $cashAmount = rand($cashRange[0], $cashRange[1]);
            $userCashPrizesCnt = Prize::getUserCashPrizeCnt($userId);
            $userCashPrizesCnt = $userCashPrizesCnt + 1;
            if ($userCashPrizesCnt > Prize::CASH_PRIZE_LIMIT) {
                Prize::setUserCashPrizeCnt($userId, Prize::CASH_PRIZE_LIMIT);
                $bonusPointsAmount = Prize::exchangeCashToBonusPoints($cashAmount, Prize::EXCHANGE_RATE);
                $values['exchangedByCashLimited'] = true;
                $values['prizeType'] = Prize::BONUS_POINTS;
                $values['amount'] = $bonusPointsAmount;
            } else {
                Prize::setUserCashPrizeCnt($userId, $userCashPrizesCnt);
                $values['prizeType'] = Prize::CASH;
                $values['amount'] = $cashAmount;
            }
        } elseif ($prizeType == Prize::BONUS_POINTS) {
            $bonusPointsRange = $prizeModel->getBonusPointsRange();
            $values['prizeType'] = Prize::BONUS_POINTS;
            $values['amount'] = rand($bonusPointsRange[0], $bonusPointsRange[1]);
        } else {
            $physicalItems = $prizeModel->getPhysicalItems();
            $values['prizeType'] = Prize::PHYSICAL_ITEMS;
            $itemKey = rand(0, count($physicalItems) - 1);
            $values['item'] = $prizeModel->getPhysicalItems()[$itemKey];
        }
        return $values;
    }
}