<?php
use \app\backend\widgets\BackendWidget;

/**
 * @var $form \app\backend\components\ActiveForm
 * @var $openGraph \DotPlant\OpenGraph\models\ObjectOpenGraph
 */

?>
<div class="row">
    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <?php BackendWidget::begin(
            ['title' => Yii::t('app', 'Open Graph'), 'footer' => $this->blocks['submit']]
        ); ?>

        <?= $form->field(
            $openGraph,
            'title',
            [
                'copyFrom' => [
                    "#page-title",
                    "#page-breadcrumbs_label",
                ]
            ]
        ) ?>
        <?= $form->field(
            $openGraph,
            'image',
            [
                'copyFrom' => [
                    '[name="file[]"]'
                ]

            ]
        ) ?>
        <?= $form->field(
            $openGraph,
            'description',
            [
                'copyFrom' => [
                    "#page-content",
                    "#page-annonce",
                ]
            ]
        )->textarea() ?>

        <?php BackendWidget::end(); ?>
    </article>
</div>
<div class="clearfix"></div>
