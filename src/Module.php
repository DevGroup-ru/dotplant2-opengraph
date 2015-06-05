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

                if ($app->requestedAction->controller instanceof \app\backend\components\BackendController) {
                    if ($app->requestedAction->controller instanceof app\modules\page\backend\PageController) {
                        BackendEntityEditEvent::on(
                            app\modules\page\backend\PageController::className(),
                            app\modules\page\backend\PageController::BACKEND_PAGE_EDIT_SAVE,
                            [$this, 'saveHandler']
                        );
                        BackendEntityEditFormEvent::on(
                            View::className(),
                            app\modules\page\backend\PageController::BACKEND_PAGE_EDIT_FORM,
                            [$this, 'renderEditForm']);
                    } elseif ($app->requestedAction->controller instanceof app\modules\shop\controllers\BackendProductController) {
                        BackendEntityEditEvent::on(
                            app\modules\shop\controllers\BackendProductController::className(),
                            app\modules\shop\controllers\BackendProductController::EVENT_BACKEND_PRODUCT_EDIT_SAVE,
                            [$this, 'saveHandler']
                        );
                        BackendEntityEditFormEvent::on(
                            View::className(),
                            app\modules\shop\controllers\BackendProductController::EVENT_BACKEND_PRODUCT_EDIT_FORM,
                            [$this, 'renderEditForm']);
                    } elseif ($app->requestedAction->controller instanceof app\modules\shop\controllers\BackendCategoryController) {
                        BackendEntityEditEvent::on(
                            app\modules\shop\controllers\BackendCategoryController::className(),
                            app\modules\shop\controllers\BackendCategoryController::BACKEND_CATEGORY_EDIT_SAVE,
                            [$this, 'saveHandler']
                        );
                        BackendEntityEditFormEvent::on(
                            View::className(),
                            app\modules\shop\controllers\BackendCategoryController::BACKEND_CATEGORY_EDIT_FORM,
                            [$this, 'renderEditForm']);
                    }
                } else {
                    if (
                        $app->requestedAction->controller instanceof app\modules\shop\controllers\ProductController &&
                        ($app->requestedAction->id == 'show' || $app->requestedAction->id == 'list' )
                    ) {
                        ViewEvent::on(
                            app\modules\shop\controllers\ProductController::className(),
                            app\modules\shop\controllers\ProductController::EVENT_PRE_DECORATOR,
                            [$this, 'registerMeta']
                        );
                    } elseif (
                        $app->requestedAction->controller instanceof app\modules\page\controllers\PageController &&
                        $app->requestedAction->id == 'show'
                    ) {
                        ViewEvent::on(
                            app\modules\page\controllers\PageController::className(),
                            app\modules\page\controllers\PageController::EVENT_PRE_DECORATOR,
                            [$this, 'registerMeta']
                        );
                    }
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
        if (empty($event->params['model'])) {
            return null;
        }

        $model = $event->params['model'];
        if ($openGraph = static::loadModel($model, false)) {
            app\modules\seo\helpers\HtmlTagHelper::registerOpenGraph(
                $openGraph->title,
                Yii::$app->request->absoluteUrl,
                Yii::$app->request->hostInfo . $openGraph->image,
                $openGraph->description
            );
        }
    }


    public static function loadModel($model, $createNew = true)
    {

        $object = app\models\Object::getForClass($model::className());

        if (!$object) {
            return null;
        }

        $openGraph = ObjectOpenGraph::find()
            ->where(
                [
                    'object_id' => $object->id,
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