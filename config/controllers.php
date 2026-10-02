<?php
/**
 * Авторегистрация контроллеров из подпапок admin/, teacher/, student/
 * Yii2 basic не поддерживает вложенные неймспейсы из коробки.
 */
return [
    // Admin
    'admin/dashboard'  => 'app\controllers\admin\DashboardController',
    'admin/task'       => 'app\controllers\admin\TaskController',
    'admin/user'       => 'app\controllers\admin\UserController',
    'admin/content'    => 'app\controllers\admin\ContentController',
    'admin/landing'    => 'app\controllers\admin\LandingController',
    'admin/lead'       => 'app\controllers\admin\LeadController',

    // Teacher
    'teacher/dashboard' => 'app\controllers\teacher\DashboardController',
    'teacher/group'     => 'app\controllers\teacher\GroupController',
    'teacher/homework'  => 'app\controllers\teacher\HomeworkController',
    'teacher/exam'      => 'app\controllers\teacher\ExamController',
    'teacher/lesson'    => 'app\controllers\teacher\LessonController',
    'teacher/slide'     => 'app\controllers\teacher\SlideController',

    // Student
    'student/dashboard' => 'app\controllers\student\DashboardController',
    'student/homework'  => 'app\controllers\student\HomeworkController',
    'student/exam'      => 'app\controllers\student\ExamController',
    'student/profile'   => 'app\controllers\student\ProfileController',
];