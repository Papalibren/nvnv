<?php

namespace app\controllers\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\Lead;
use app\models\Landing;

class LeadController extends BaseAdminController
{
    public function actionIndex()
    {
        $this->view->title = 'Заявки';

        $landingId = Yii::$app->request->get('landing_id');
        $onlyNew   = Yii::$app->request->get('new');

        $query = Lead::find()->orderBy('created_at DESC');

        if ($landingId) {
            $query->andWhere(['landing_id' => (int) $landingId]);
        }
        if ($onlyNew) {
            $query->andWhere(['is_processed' => false]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'landings'     => Landing::find()->orderBy('title')->all(),
            'newCount'     => Lead::find()->where(['is_processed' => false])->count(),
        ]);
    }

    public function actionToggleProcessed(int $id)
    {
        $lead = $this->findLead($id);
        $lead->is_processed = !$lead->is_processed;
        $lead->save(false);

        return $this->renderPartial('_processed_badge', ['lead' => $lead]);
    }

    private function findLead(int $id): Lead
    {
        $lead = Lead::findOne($id);
        if (!$lead) throw new NotFoundHttpException('Заявка не найдена.');
        return $lead;
    }
}