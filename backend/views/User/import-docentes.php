<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $baseUrl */
/** @var string $apiKey */
/** @var bool $resetExistingPasswords */
/** @var array|null $result */

$this->title = 'Importar docentes desde API';
$this->params['breadcrumbs'][] = ['label' => 'Gestion de usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-import-docentes">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-0">
                Crea o actualiza cuentas docentes usando la ruta publica <code>/docentes</code>.
            </p>
        </div>
        <?= Html::a('Volver a usuarios', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="alert alert-info d-flex gap-2 align-items-start mb-4">
        <span style="font-size:1.3rem;">*</span>
        <div>
            <strong>Contrasenas unicas por docente.</strong>
            Cada cuenta nueva recibe una contrasena aleatoria de 12 caracteres.
            Las contrasenas generadas se muestran solo en esta pantalla para que puedas copiarlas.
            El envio de correo esta desactivado temporalmente.
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <?= Html::beginForm(['import-docentes'], 'post', ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <label class="form-label" for="baseUrl">URL de la API</label>
                    <?= Html::textInput('baseUrl', $baseUrl, [
                        'id' => 'baseUrl',
                        'class' => 'form-control',
                        'placeholder' => 'https://servidor.ngrok-free.dev o https://servidor.ngrok-free.dev/docentes',
                    ]) ?>
                    <div class="form-text">Puedes pegar la URL base o la ruta completa <code>/docentes</code>.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="apiKey">API key</label>
                    <?= Html::textInput('apiKey', $apiKey, [
                        'id' => 'apiKey',
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <?= Html::checkbox('resetExistingPasswords', $resetExistingPasswords, [
                            'id' => 'resetExistingPasswords',
                            'class' => 'form-check-input',
                            'value' => '1',
                        ]) ?>
                        <label class="form-check-label" for="resetExistingPasswords">
                            Regenerar contrasena para docentes ya existentes
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <?= Html::submitButton('Importar docentes', [
                        'class' => 'btn btn-success',
                        'data' => ['confirm' => 'Iniciar importacion desde la API?'],
                    ]) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php if ($result): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Resultado</h2>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-success fs-6"><?= (int)$result['created'] ?> creados</span>
                    <span class="badge bg-primary fs-6"><?= (int)$result['updated'] ?> actualizados</span>
                    <span class="badge bg-secondary fs-6"><?= (int)$result['skipped'] ?> omitidos</span>
                </div>

                <?php if (!empty($result['errors'])): ?>
                    <div class="alert alert-warning mb-0">
                        <strong>Observaciones:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($result['errors'] as $error): ?>
                                <li><?= Html::encode($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($result['passwords'])): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h2 class="h5 mb-0">Contrasenas generadas</h2>
                        <p class="text-muted small mb-0">Guarda esta informacion. No volvera a mostrarse si recargas.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copiarTablaPasswords()">
                        Copiar todo
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tabla-passwords">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Contrasena temporal</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['passwords'] as $passwordRow): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= Html::encode($passwordRow['username']) ?></td>
                                        <td class="text-muted small"><?= Html::encode($passwordRow['email']) ?></td>
                                        <td><code class="fs-6 text-danger fw-bold"><?= Html::encode($passwordRow['password']) ?></code></td>
                                        <td>
                                            <?php if ($passwordRow['nuevo']): ?>
                                                <span class="badge bg-success">Nuevo</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Actualizado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-muted small">
                    Esta tabla solo aparece una vez. El docente debera cambiar su contrasena al iniciar sesion.
                </div>
            </div>

            <script>
            function copiarTablaPasswords() {
                const rows = document.querySelectorAll('#tabla-passwords tbody tr');
                let texto = 'Usuario\tCorreo\tContrasena\n';
                rows.forEach((row) => {
                    const cols = row.querySelectorAll('td');
                    texto += cols[0].textContent.trim() + '\t'
                        + cols[1].textContent.trim() + '\t'
                        + cols[2].textContent.trim() + '\n';
                });
                navigator.clipboard.writeText(texto).then(() => {
                    alert('Copiado al portapapeles');
                });
            }
            </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
