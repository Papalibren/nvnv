<?php use yii\helpers\Html; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предпросмотр</title>
</head>
<body style="margin:0; font-family: Inter, sans-serif;">

    <div style="background:#FEF3C7; color:#92400E; padding:10px 20px; text-align:center; font-size:13px; font-weight:600;">
        РЕЖИМ ПРЕДПРОСМОТРА — форма заявки не активна
    </div>

    <?= $content ?>

    <div style="padding:64px 20px; background:#F8FAFC; text-align:center;">
        <div style="max-width:440px; margin:0 auto; background:white; border:1px solid #E2E8F0; border-radius:16px; padding:24px;">
            <h2 style="font-size:20px; font-weight:700; margin:0 0 16px;"><?= Html::encode($formTitle) ?></h2>
            <p style="color:#94A3B8; font-size:13px;">Здесь будет форма заявки</p>
        </div>
    </div>

</body>
</html>