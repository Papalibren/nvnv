<?php

namespace app\models\forms;

use yii\base\Model;

class ResetPasswordForm extends Model
{
    public string $password         = '';
    public string $password_confirm = '';

    public function rules(): array
    {
        return [
            [['password', 'password_confirm'], 'required',
                'message' => 'Обязательное поле'],
            [['password'], 'string', 'min' => 6,
                'tooShort' => 'Минимум 6 символов'],
            [['password_confirm'], 'compare',
                'compareAttribute' => 'password',
                'message'          => 'Пароли не совпадают'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'password'         => 'Новый пароль',
            'password_confirm' => 'Подтверждение пароля',
        ];
    }
}