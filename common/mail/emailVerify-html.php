<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl([
    '/site/verify-email',
    'token' => $user->verification_token,
]);
?>
<div style="font-family: Arial, sans-serif; max-width: 560px; margin: 0 auto; color: #333;">

    <div style="background: #1a1a2e; padding: 24px 32px; border-radius: 8px 8px 0 0;">
        <h2 style="color: #fff; margin: 0; font-size: 20px;">
            Instituto Tecnológico Superior de Valladolid
        </h2>
        <p style="color: #aaa; margin: 4px 0 0; font-size: 13px;">Sistema Gestor de Expedientes</p>
    </div>

    <div style="background: #f9f9f9; padding: 32px; border-radius: 0 0 8px 8px; border: 1px solid #e0e0e0; border-top: none;">
        <p style="margin: 0 0 12px;">Hola <strong><?= Html::encode($user->username) ?></strong>,</p>

        <p style="margin: 0 0 20px; line-height: 1.6;">
            Gracias por registrarte. Para activar tu cuenta y acceder al sistema,
            haz clic en el botón de abajo:
        </p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="<?= Html::encode($verifyLink) ?>"
               style="background: #2d6a4f; color: #fff; padding: 14px 32px;
                      border-radius: 6px; text-decoration: none; font-size: 15px;
                      font-weight: bold; display: inline-block;">
                Verificar mi correo
            </a>
        </div>

        <p style="margin: 20px 0 0; font-size: 13px; color: #666; line-height: 1.6;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
            <a href="<?= Html::encode($verifyLink) ?>" style="color: #2d6a4f; word-break: break-all;">
                <?= Html::encode($verifyLink) ?>
            </a>
        </p>

        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 24px 0;">

        <p style="margin: 0; font-size: 12px; color: #999;">
            Si no creaste esta cuenta, puedes ignorar este correo con seguridad.<br>
            Este enlace expirará en 24 horas.
        </p>
    </div>

</div>
