<?php
/**
 * ============================================================
 * PANEL ORGANISATEUR - NOVA Événements
 * ============================================================
 */

$title = 'Panel Organisateur - NOVA';
$nonce = cspNonce();

$tags = [
    'sport' => 'Sport',
    'culture' => 'Culture',
    'soiree' => 'Soirée',
    'conference' => 'Conférence',
    'festival' => 'Festival',
    'autre' => 'Autre'
];

// Mode édition
$edit_event = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($events as $e) {
        if ($e['id_event'] == $edit_id) {
            $edit_event = $e;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <?php partial('header', ['isLoggedIn' => $isLoggedIn, 'userName' => $userName, 'userRole' => $userRole]); ?>

    <main>
        <section class="admin-section">
            <div class="admin-bg-gradient"></div>
            
            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-container">
                <!-- Sidebar -->
                <aside class="admin-sidebar">
                    <div class="admin-profile">
                        <div class="admin-avatar"><?= e(strtoupper(substr($userName, 0, 1))) ?></div>
                        <h3><?= e($userName) ?></h3>
                        <span class="role-badge">Organisateur</span>
                    </div>
                    
                    <nav class="admin-nav">
                        <a href="<?= url('/organisateur') ?>" class="admin-nav-link active">
                            <span class="nav-icon">-</span>
                            Mes Événements
                        </a>
                        <a href="<?= url('/organisateur') ?>#create" class="admin-nav-link">
                            <span class="nav-icon">+</span>
                            Créer un événement
                        </a>
                        <a href="<?= url('/profil') ?>" class="admin-nav-link">
                            <span class="nav-icon">-</span>
                            Mon Profil
                        </a>
                    </nav>
                    
                    <div class="admin-stats">
                        <div class="stat-box">
                            <span class="stat-value"><?= count($events) ?></span>
                            <span class="stat-label">Événements</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value"><?= array_sum(array_column($events, 'nb_inscrits')) ?></span>
                            <span class="stat-label">Inscrits total</span>
                        </div>
                    </div>
                </aside>

                <!-- Contenu principal -->
                <div class="admin-content">
                    <!-- Formulaire de création/édition avec WIZARD -->
                    <div class="admin-card wizard-card-container" id="create">
                        <div class="wizard-header">
                            <h3 style="margin-bottom: 20px; font-size: 1.2rem;">
                                <?= $edit_event ? 'Modifier votre événement' : 'Initialisation de l\'Événement' ?>
                            </h3>
                            <div class="progress-container">
                                <div class="progress-line-bg"></div>
                                <div class="progress-line-fill" id="progress-fill"></div>
                                
                                <div class="step-item active" id="step-indicator-1">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">Type</div>
                                </div>
                                <div class="step-item" id="step-indicator-2">
                                    <div class="step-circle">2</div>
                                    <div class="step-label">Détails</div>
                                </div>
                                <div class="step-item" id="step-indicator-3">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">Image</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="<?= url('/organisateur/creer') ?>" enctype="multipart/form-data" class="wizard-body-form" id="event-wizard-form">
                            <?= csrf_field() ?>
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="id_event" value="<?= intval($edit_event['id_event']) ?>">
                            <?php endif; ?>

                            <!-- STEP 1: TYPE D'ÉVÉNEMENT -->
                            <div class="step-content active" id="step-1">
                                <h2>Quel est le type de votre événement ?</h2>
                                <p class="step-desc">Sélectionnez la catégorie qui correspond à votre événement.</p>
                                
                                <div class="type-grid">
                                    <label>
                                        <input type="radio" name="tag" value="sport" class="type-radio" hidden <?= (!$edit_event || ($edit_event['tag'] ?? '') == 'sport') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">S</span>
                                            <h4>Sport</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Compétitions, matchs, tournois.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="culture" class="type-radio" hidden <?= ($edit_event && ($edit_event['tag'] ?? '') == 'culture') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">C</span>
                                            <h4>Culture</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Spectacles, expositions, théâtre.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="soiree" class="type-radio" hidden <?= ($edit_event && ($edit_event['tag'] ?? '') == 'soiree') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">G</span>
                                            <h4>Soirée / Gala</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Cocktails, dîners, célébrations.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="conference" class="type-radio" hidden <?= ($edit_event && ($edit_event['tag'] ?? '') == 'conference') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">K</span>
                                            <h4>Conférence</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Séminaires, présentations, talks.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="festival" class="type-radio" hidden <?= ($edit_event && ($edit_event['tag'] ?? '') == 'festival') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">F</span>
                                            <h4>Festival</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Concerts, public, outdoor.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="autre" class="type-radio" hidden <?= ($edit_event && ($edit_event['tag'] ?? '') == 'autre') ? 'checked' : '' ?>>
                                        <div class="type-card">
                                            <span class="type-icon">+</span>
                                            <h4>Autre</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Autre type d'événement.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- STEP 2: DÉTAILS LOGISTIQUES -->
                            <div class="step-content" id="step-2">
                                <h2>Logistique & Détails</h2>
                                <p class="step-desc">Donnez-nous les informations essentielles de votre événement.</p>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Nom de l'événement *</label>
                                        <input type="text" name="name" required placeholder="Ex: Festival Electro 2025"
                                               value="<?= $edit_event ? e($edit_event['name']) : '' ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Date *</label>
                                        <input type="date" name="event_date" required
                                               value="<?= $edit_event ? e($edit_event['event_date']) : '' ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Heure *</label>
                                        <input type="time" name="hour" required
                                               value="<?= $edit_event ? e(substr($edit_event['hour'] ?? '', 0, 5)) : '' ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Lieu *</label>
                                        <input type="text" name="lieu" required placeholder="Ex: Paris, Palais des Congrès"
                                               value="<?= $edit_event ? e($edit_event['lieu']) : '' ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Capacité *</label>
                                        <input type="number" name="capacite" required min="1" placeholder="Nombre de places"
                                               value="<?= $edit_event ? e($edit_event['capacite']) : '' ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Prix (€)</label>
                                        <input type="number" name="prix" min="0" placeholder="0 = Gratuit"
                                               value="<?= $edit_event ? e($edit_event['prix']) : '0' ?>">
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label>Description</label>
                                    <textarea name="description" rows="5" placeholder="Décrivez votre événement, l'atmosphère, les détails..."><?= $edit_event ? e($edit_event['description']) : '' ?></textarea>
                                </div>
                            </div>

                            <!-- STEP 3: IMAGE & FINITIONS -->
                            <div class="step-content" id="step-3">
                                <h2>Image & Finitions</h2>
                                <p class="step-desc">Ajoutez une image représentative de votre événement.</p>

                                <div class="input-group">
                                    <label>Image de l'événement</label>
                                    <div class="image-upload-container">
                                        <div class="upload-option">
                                            <label class="upload-label">
                                                <input type="file" name="image" accept="image/*" class="file-input">
                                                <span class="upload-btn">📁 Choisir un fichier</span>
                                            </label>
                                        </div>
                                        <div class="upload-divider">ou</div>
                                        <div class="upload-option">
                                            <input type="text" name="image_url" placeholder="URL de l'image (https://...)"
                                                   value="<?= ($edit_event && strpos($edit_event['image'] ?? '', 'http') === 0) ? e($edit_event['image']) : '' ?>">
                                        </div>
                                    </div>
                                    <?php if ($edit_event && !empty($edit_event['image'])): ?>
                                        <div class="current-image">
                                            <p>Image actuelle :</p>
                                            <img src="<?= (strpos($edit_event['image'], 'http') === 0) ? e($edit_event['image']) : asset($edit_event['image']) ?>" 
                                                 alt="Image actuelle" style="max-width: 200px; border-radius: 8px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </form>

                        <!-- FOOTER NAVIGATION -->
                        <div class="wizard-footer">
                            <button type="button" class="btn-text" id="btn-prev" style="visibility: hidden;">← Retour</button>
                            <button type="button" class="btn-next" id="btn-next">Suivant →</button>
                        </div>

                    </div>

                    <!-- Liste des événements -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Mes Événements</h2>
                            <span class="events-total"><?= count($events) ?> événement(s)</span>
                        </div>
                        
                        <?php if (empty($events)): ?>
                            <div class="empty-state">
                                <span class="empty-icon">-</span>
                                <h3>Aucun événement</h3>
                                <p>Créez votre premier événement ci-dessus !</p>
                            </div>
                        <?php else: ?>
                            <div class="events-table-container">
                                <table class="events-table">
                                    <thead>
                                        <tr>
                                            <th>Événement</th>
                                            <th>Date</th>
                                            <th>Lieu</th>
                                            <th>Inscrits</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td>
                                                    <div class="event-cell">
                                                        <?php if (!empty($event['image'])): ?>
                                                            <img src="<?= (strpos($event['image'], 'http') === 0) ? e($event['image']) : asset($event['image']) ?>" 
                                                                 alt="" class="event-thumb">
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= e($event['name']) ?></strong>
                                                            <span class="tag-small tag-<?= e($event['tag']) ?>">
                                                                <?= e($tags[$event['tag']] ?? $event['tag']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($event['event_date'])) ?><br>
                                                    <small><?= e(substr($event['hour'] ?? '', 0, 5)) ?></small>
                                                </td>
                                                <td><?= e($event['lieu']) ?></td>
                                                <td>
                                                    <span class="inscrits-badge">
                                                        <?= e($event['nb_inscrits']) ?> / <?= e($event['capacite']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?= str_replace(' ', '-', $event['status']) ?>">
                                                        <?= e($event['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="<?= url('/organisateur?edit=' . intval($event['id_event'])) ?>#create" 
                                                           class="btn-action btn-edit" title="Modifier">Modifier</a>
                                                        <form method="POST" action="<?= url('/organisateur/supprimer/' . intval($event['id_event'])) ?>" style="display: inline;" 
                                                              onsubmit="return confirm('Supprimer cet événement ?');">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn-action btn-delete" title="Supprimer">X</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php partial('footer'); ?>

    <script nonce="<?= $nonce ?>">
        // ========== WIZARD LOGIC ==========
        let currentStep = 1;
        const totalSteps = 3;

        const btnNext = document.getElementById('btn-next');
        const btnPrev = document.getElementById('btn-prev');
        const progressFill = document.getElementById('progress-fill');
        const form = document.getElementById('event-wizard-form');

        function updateUI() {
            // Update Form Visibility
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById(`step-${currentStep}`)?.classList.add('active');

            // Update Header Indicators
            for(let i=1; i<=totalSteps; i++) {
                const indicator = document.getElementById(`step-indicator-${i}`);
                if (!indicator) continue;
                indicator.classList.remove('active', 'completed');
                if(i < currentStep) indicator.classList.add('completed');
                if(i === currentStep) indicator.classList.add('active');
            }

            // Update Progress Bar Line
            const percentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
            progressFill.style.width = `${percentage}%`;

            // Update Buttons
            btnPrev.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            
            if(currentStep === totalSteps) {
                btnNext.textContent = 'Créer l\'événement →';
                btnNext.style.background = '#FF9900';
            } else {
                btnNext.textContent = 'Suivant →';
                btnNext.style.background = 'linear-gradient(135deg, #FF00CC 0%, #FF9900 100%)';
            }
        }

        function validateStep(stepNum) {
            const step = document.getElementById(`step-${stepNum}`);
            if (!step) return true;

            // Étape 1: Valider la sélection du type
            if (stepNum === 1) {
                const tagSelected = form.querySelector('input[name="tag"]:checked');
                if (!tagSelected) {
                    alert('Veuillez sélectionner un type d\'événement');
                    return false;
                }
            }

            // Étape 2: Valider les champs obligatoires
            if (stepNum === 2) {
                const name = form.querySelector('input[name="name"]').value.trim();
                const date = form.querySelector('input[name="event_date"]').value;
                const hour = form.querySelector('input[name="hour"]').value;
                const lieu = form.querySelector('input[name="lieu"]').value.trim();
                const capacite = form.querySelector('input[name="capacite"]').value;

                if (!name || !date || !hour || !lieu || !capacite) {
                    alert('Veuillez remplir tous les champs obligatoires (nom, date, heure, lieu, capacité)');
                    return false;
                }
                if (capacite <= 0) {
                    alert('La capacité doit être supérieure à 0');
                    return false;
                }
            }

            return true;
        }

        btnNext.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if(currentStep < totalSteps) {
                    currentStep++;
                    updateUI();
                } else {
                    // Soumettre le formulaire
                    form.submit();
                }
            }
        });

        btnPrev.addEventListener('click', () => {
            if(currentStep > 1) {
                currentStep--;
                updateUI();
            }
        });

        // Preview du nom de fichier sélectionné
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Aucun fichier';
                const label = this.closest('.upload-label').querySelector('.upload-btn');
                label.textContent = '📁 ' + fileName;
            });
        });

        // Initialiser l'UI du wizard
        updateUI();
    </script>

    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
