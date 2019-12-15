<?php
use app\models\Prize;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ChoosePrizeForm */
/* @var $prizeType integer */
/* @var $prizeName string */
/* @var $amount integer */
/* @var $item string */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Choose prize';
$this->params['breadcrumbs'][] = $this->title;
$userId = Yii::$app->user->identity->id;
$prizeModel = new Prize();
?>
<div class="site-choose-prize">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('choosePrizeFormSubmitted')): ?>
        <?php $form = ActiveForm::begin(['id' => 'choose-prize-form']); ?>
        <?= $form->field($model, 'userId')
            ->hiddenInput(['value' => $userId])
            ->label(false); ?>
        <?= $form->field($model, 'prizeType')
            ->hiddenInput(['value' => $prizeType])
            ->label(false); ?>
        <div class="alert alert-success">
            Congratulation! You win the prize of <?= $prizeName ?>
                <?php if (isset($amount)): ?>
                    <?php if ($prizeType == Prize::CASH): ?>
                        <?= 'with ' . $amount . ' USD. 
                        You can exchange this cash amount to bonus points for '
                        . ($amount*Prize::EXCHANGE_RATE) . ' bonus points or you can confirm the prize.' ?>
                        <?= $form->field($model, 'cashAmount')
                                ->hiddenInput(['value' => $amount])
                                ->label(false); ?>
                        <div class="form-group">
                            <?= Html::submitButton('Exchange', [
                                    'class' => 'btn btn-primary',
                                    'name' => 'exchange-prize-button',
                                ]) ?>
                        </div>
                    <?php else: ?>
                        <?= ' with ' . $amount . ' points.' ?>
                        <?php if (isset($exchangedByCashLimited) && $exchangedByCashLimited): ?>
                            <p>Points are exchange from <?= $amount / Prize::EXCHANGE_RATE ?>
                                USD because of cash prize limited.
                            </p>
                        <?php endif; ?>
                        <?= $form->field($model, 'bonusPointsAmount')
                            ->hiddenInput(['value' => $amount])
                            ->label(false); ?>
                    <?php endif; ?>
                <?php elseif (isset($item)): ?>
                    <?= 'with ' . $item . '. You can refuse the item if you don\'t want it.'?>
                    <div class="form-group">
                        <?= $form->field($model, 'refuse')
                            ->hiddenInput(['value' => true])
                            ->label(false); ?>
                        <?= $form->field($model, 'item')
                            ->hiddenInput(['value' => $item])
                            ->label(false); ?>
                        <?= Html::submitButton('Refuse the item', [
                                'class' => 'btn btn-primary',
                                'name' => 'refuse-prize-button',
                            ]) ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <?= Html::submitButton('Confirm prize', [
                            'class' => 'btn btn-primary',
                            'name' => 'confirm-prize-button',
                        ]) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    <?php elseif (Yii::$app->session->hasFlash('exchangeCashSubmitted')): ?>
        <p>Congratulation! You have exchanged the prize of cash for <?= $amount ?> bonus points.</p>
        <p>Now your account has <?= Prize::getUserBonusPoints($userId) ?> bonus points.</p>
    <?php elseif (Yii::$app->session->hasFlash('refuseItemSubmitted')): ?>
        <p>You have refused the item <?= $item ?>.</p>
    <?php elseif (Yii::$app->session->hasFlash('confirmPrizeSubmitted')): ?>
        <p>Your chosen prize <?= $prizeName ?> has been succesfully processed.</p>
        <p>Now your account has
            <?php if ($prizeType == Prize::CASH): ?>
                <?php echo Prize::getUserCashAmount($userId) ?> USD.
            <?php elseif ($prizeType == Prize::BONUS_POINTS): ?>
                <?php echo Prize::getUserBonusPoints($userId) ?> bonus points.
            <?php else: ?>
                items: <?php echo implode(', ', Prize::getUserItems($userId)) ?>.
            <?php endif; ?>
        </p>
    <?php else: ?>
        <p>
            Please click the button to choose your price.
            Thank you.
        </p>
        <div class="row">
            <div class="col-lg-5">
                <?php $form = ActiveForm::begin(['id' => 'choose-prize-form']); ?>
                    <?= $form->field($model, 'userId')
                            ->hiddenInput(['value' => $userId])
                            ->label(false);
                    ?>
                    <div class="form-group">
                        <?= Html::submitButton('Submit', [
                                'class' => 'btn btn-primary',
                                'name' => 'choose-prize-button'
                            ]) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
