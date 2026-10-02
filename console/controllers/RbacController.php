<?php

namespace app\console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit(): void
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        // Создаём роли
        $student = $auth->createRole('student');
        $teacher = $auth->createRole('teacher');
        $admin   = $auth->createRole('admin');

        $student->description = 'Ученик';
        $teacher->description = 'Учитель';
        $admin->description   = 'Администратор';

        // Создаём права
        $viewPublic      = $auth->createPermission('viewPublic');
        $viewPublic->description = 'Просмотр публичного контента';

        $manageOwnData   = $auth->createPermission('manageOwnData');
        $manageOwnData->description = 'Управление своими данными';

        $teachStudents   = $auth->createPermission('teachStudents');
        $teachStudents->description = 'Обучение учеников';

        $manageContent   = $auth->createPermission('manageContent');
        $manageContent->description = 'Управление контентом';

        $manageTasks     = $auth->createPermission('manageTasks');
        $manageTasks->description = 'Управление задачами';

        $manageUsers     = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Управление пользователями';

        $manageLandings  = $auth->createPermission('manageLandings');
        $manageLandings->description = 'Управление лендингами';

        // Добавляем всё в auth manager
        foreach ([$student, $teacher, $admin] as $role) {
            $auth->add($role);
        }

        foreach ([
            $viewPublic, $manageOwnData, $teachStudents,
            $manageContent, $manageTasks, $manageUsers, $manageLandings
        ] as $permission) {
            $auth->add($permission);
        }

        // Назначаем права ролям
        $auth->addChild($student, $viewPublic);
        $auth->addChild($student, $manageOwnData);

        $auth->addChild($teacher, $viewPublic);
        $auth->addChild($teacher, $manageOwnData);
        $auth->addChild($teacher, $teachStudents);

        // Admin наследует всё от teacher + свои права
        $auth->addChild($admin, $teacher);
        $auth->addChild($admin, $manageContent);
        $auth->addChild($admin, $manageTasks);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $manageLandings);

        echo "✅ RBAC инициализирован\n";
        echo "   Роли: admin, teacher, student\n";
        echo "   Права: viewPublic, manageOwnData, teachStudents,\n";
        echo "          manageContent, manageTasks, manageUsers, manageLandings\n";
    }

    public function actionCreateAdmin(string $name, string $username, string $password): void
    {
        $user               = new \app\models\User();
        $user->role         = \app\models\User::ROLE_ADMIN;
        $user->name         = $name;
        $user->username     = $username;
        $user->status       = \app\models\User::STATUS_ACTIVE;
        $user->generateAuthKey();
        $user->setPassword($password);

        if (!$user->save()) {
            echo "❌ Ошибка: " . implode(', ', $user->getFirstErrors()) . "\n";
            return;
        }

        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('admin'), $user->id);

        echo "✅ Администратор создан\n";
        echo "   Логин: {$username}\n";
        echo "   ID: {$user->id}\n";
    }
}