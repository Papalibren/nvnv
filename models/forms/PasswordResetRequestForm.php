<?php

namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class PasswordResetRequestForm extends Model
{
    public string $email = '';

    public function rules(): array
    {
        return [
            [['email'], 'required', 'message' => 'Введите email'],
            [['email'], 'email',    'message' => 'Неверный формат email'],
            [['email'], 'exist',
                'targetClass'     => User::class,
                'targetAttribute' => 'email',
                'filter'          => ['status' => User::STATUS_ACTIVE],
                'message'         => 'Пользователь с таким email не найден'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['email' => 'Email'];
    }
}