<?php

namespace app\services;

use Yii;
use app\models\Lead;
use app\models\User;

class LandingService
{
    /**
     * Создать заявку с лендинга
     */
    public function createLead(int $landingId, array $data): Lead
    {
        $lead              = new Lead();
        $lead->landing_id  = $landingId;
        $lead->name        = trim($data['name'] ?? '');
        $lead->phone       = trim($data['phone'] ?? '');
        $lead->email       = trim($data['email'] ?? '') ?: null;
        $lead->message     = trim($data['message'] ?? '') ?: null;
        $lead->utm_source  = $data['utm_source']   ?? null;
        $lead->utm_medium  = $data['utm_medium']   ?? null;
        $lead->utm_campaign = $data['utm_campaign'] ?? null;
        $lead->is_processed = false;

        if (!$lead->save()) {
            throw new \RuntimeException(implode(', ', $lead->getFirstErrors()));
        }

        $this->notifyAboutLead($lead);

        return $lead;
    }

    /**
     * Уведомляем админов о новой заявке — email + уведомление в ЛК
     */
    private function notifyAboutLead(Lead $lead): void
    {
        $notifService = new NotificationService();
        $admins       = User::find()->where(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE])->all();

        foreach ($admins as $admin) {
            $notifService->create(
                $admin->id,
                'lead_received',
                'Новая заявка с лендинга',
                $lead->name . ($lead->phone ? ', ' . $lead->phone : ''),
                'lead',
                $lead->id
            );
        }

        // Email
        try {
            Yii::$app->mailer->compose()
                ->setTo(Yii::$app->params['adminEmail'])
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['siteName']])
                ->setSubject('Новая заявка: ' . $lead->name)
                ->setTextBody(
                    "Имя: {$lead->name}\n" .
                    "Телефон: {$lead->phone}\n" .
                    "Email: {$lead->email}\n" .
                    "Сообщение: {$lead->message}\n" .
                    "Источник: {$lead->utm_source} / {$lead->utm_medium} / {$lead->utm_campaign}\n" .
                    "Лендинг: " . ($lead->landing->title ?? '—')
                )
                ->send();
        } catch (\Exception $e) {
            Yii::error('Ошибка отправки email о заявке: ' . $e->getMessage());
        }
    }
}