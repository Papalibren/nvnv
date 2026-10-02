<?php

namespace app\models\forms;

use yii\base\Model;

class RegisterForm extends Model
{
    public string $username = '';
    public string $password = '';
    public string $password_confirm = '';
    public string $email    = '';

    public function rules(): array
    {
        return [
            [['username', 'password', 'password_confirm'], 'required',
                'message' => 'Обязательное поле'],

            [['username'], 'string', 'min' => 3, 'max' => 50],
            [['username'], 'match',
                'pattern' => '/^[a-zA-Z0-9_-]+$/',
                'message' => 'Только латинские буквы, цифры, _ и -'],
            [['username'], 'unique',
                'targetClass'     => \app\models\User::class,
                'targetAttribute' => 'username',
                'message'         => 'Этот логин уже занят'],

            [['password'], 'string', 'min' => 6,
                'tooShort' => 'Минимум 6 символов'],
            [['password_confirm'], 'compare',
                'compareAttribute' => 'password',
                'message'          => 'Пароли не совпадают'],

            [['email'], 'email', 'message' => 'Неверный формат email'],
            [['email'], 'unique',
                'targetClass'     => \app\models\User::class,
                'targetAttribute' => 'email',
                'message'         => 'Этот email уже используется'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username'         => 'Логин',
            'password'         => 'Пароль',
            'password_confirm' => 'Подтверждение пароля',
            'email'            => 'Email (для восстановления пароля)',
        ];
    }
}