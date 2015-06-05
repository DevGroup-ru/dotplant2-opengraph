<?php

namespace DotPlant\OpenGraph;

use app;
use app\components\ExtensionModule;
use app\backend\components\BackendController;
use app\backend\events\BackendEntityEditFormEvent;
use DotPlant\OpenGraph\models\ObjectOpenGraph;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\ViewEvent;
use yii\db\ActiveRecord;
use app\backend\events\BackendEntityEditEvent;
use app\modules\page\models\Page;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * Class Module represents twitter cards module for DotPlant2 CMS
 *
 * @package DotPlant\OpenGraph
 */
class Module extends ExtensionModule implements BootstrapInterface
{
    public static $moduleId = 'OpenGraph';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@OpenGraph/views/configurable/_config',
                'configurableModel' => 'DotPlant\OpenGraph\components\ConfigurationModel',
            ]
        ];
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_BEFORE_ACTION,
            function () use ($app) {
                if ($app->requestedAction->controller instanceof app\modules\page\backend\PageController) {
                    BackendEntityEditEvent::on(
                        app\modules\page\backend\PageController::className(),
                        'backend-page-edit-save',
                        [$this, 'saveHandler']
                    );
                    BackendEntityEditFormEvent::on(
                        'yii\web\View',
                        'backend-page-edit-form',
                        [$this, 'renderEditForm']);
                } elseif (
                    $app->requestedAction->controller instanceof app\modules\page\controllers\PageController &&
                    $app->requestedAction->id == 'show'
                ) {
                    ViewEvent::on(
                        'yii\web\View',
                        View::EVENT_BEFORE_RENDER,
                        [$this, 'registerMeta']
                    );
                }
            }
        );


    }

    public function saveHandler($event)
    {
        if (!isset($event->model)) {
            return null;
        }

        $model = $event->model;
        $openGraph = static::loadModel($model);

        if ($openGraph->save()) {
            Yii::$app->session->setFlash('info', 'Open Graph Save');
        } else {
            $model->errors = ArrayHelper::merge($model->errors, $openGraph->errors);
        }


    }

    public function renderEditForm(BackendEntityEditFormEvent $event)
    {
        if (!isset($event->model)) {
            return null;
        }
        /** @var \yii\web\View $view */
        $view = $event->sender;

        $model = $event->model;

        $openGraph = static::loadModel($model);

        echo $view->render('@OpenGraph/views/_edit', [
            'form' => $event->form,
            'model' => $event->model,
            'openGraph' => $openGraph
        ]);
    }

    public function registerMeta(ViewEvent $event)
    {
        if (empty($event->params['model'])  || ! $event->params['model'] instanceof Page ) {
            return null;
        }

        $model = $event->params['model'];
        if ($openGraph = static::loadModel($model, false)) {
            app\modules\seo\helpers\HtmlTagHelper::registerOpenGraph(
                $openGraph->title,
                Yii::$app->request->absoluteUrl,
                $openGraph->image,
                $openGraph->description
            );
        }
    }


    public static function loadModel($model, $createNew = true)
    {

        $openGraph = ObjectOpenGraph::find()
            ->where(
                [
                    'object_id' => $model->object->id,
                    'object_model_id' => $model->id
                ]
            )
            ->one();
        if ($createNew) {
            if (!$openGraph) {
                $openGraph = new ObjectOpenGraph();
                $openGraph->object_id = $model->object->id;
                $openGraph->object_model_id = $model->id;
            }

            $openGraph->load(Yii::$app->request->post());
        }

        return $openGraph;
    }

}