<?php
include_once 'includes/session.php';
global $current_user;
require_login();

include_once __DIR__ . '/../Backend/config/database.php';
include_once __DIR__ . '/../Backend/models/Utilisateur.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new Utilisateur($db);
$userObj->id_utilisateur = $current_user['id_utilisateur'];

$success_msg = '';
$error_msg = '';

// Mettre à jour le profil si soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    $adresse = isset($_POST['adresse_livraison']) ? trim($_POST['adresse_livraison']) : '';

    if (!empty($nom)) {
        try {
            $query = "UPDATE utilisateurs SET nom = :nom, telephone = :telephone, adresse_livraison = :adresse WHERE id_utilisateur = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":nom", $nom);
            $stmt->bindParam(":telephone", $telephone);
            $stmt->bindParam(":adresse", $adresse);
            $stmt->bindParam(":id", $userObj->id_utilisateur);

            if ($stmt->execute()) {
                $success_msg = "Votre profil a été mis à jour avec succès.";
                // Mettre à jour la session
                $_SESSION['user']['nom'] = $nom;
                $current_user['nom'] = $nom;
            } else {
                $error_msg = "Erreur lors de la mise à jour.";
            }
        } catch (Exception $e) {
            $error_msg = "Erreur : " . $e->getMessage();
        }
    } else {
        $error_msg = "Le nom ne peut pas être vide.";
    }
}

// Récupérer le profil actuel
$profil = $userObj->getProfil();

// Récupérer l'historique des commandes via l'API
$api_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/appli_commande/Backend/api/commandes/historique.php?id_utilisateur=" . $userObj->id_utilisateur;

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

$commandes = json_decode($response, true) ?: [];

$page_title = "Mon Profil";
include_once 'includes/header.php';
?>

<!-- Banner Section -->
<section style="background: var(--page-banner-bg); padding: 4rem 0; text-align: center; border-bottom: 1px solid var(--border);">
  <div class="container reveal">
    <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-family: var(--font-heading); margin-bottom: 0.5rem; color: var(--page-banner-text);">Mon Profil &amp; Commandes</h1>
    <div class="divider"></div>
  </div>
</section>

<!-- Profile Details Section -->
<section class="section" style="background: var(--dark-2);">
  <div class="container">
    
    <?php if (isset($_GET['success'])): ?>
      <div style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--success); color: var(--success); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> Félicitations ! Votre commande a été enregistrée avec succès. Vous recevrez une notification sous peu.
      </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
      <div style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--success); color: var(--success); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($success_msg); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <div class="grid-3">
      <!-- Edit Profile form (Left Column) -->
      <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; height: fit-content;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Mes Informations</h3>
        
        <form action="" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
          <input type="hidden" name="action_update" value="1">
          
          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label style="font-size: 0.8rem; color: var(--text-dim);">Adresse Email</label>
            <input type="email" disabled value="<?php echo htmlspecialchars($profil['email']); ?>" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text-muted); font-family: var(--font-body); font-size: 0.9rem; cursor: not-allowed;">
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="nom" style="font-size: 0.8rem; color: var(--text-dim);">Nom Complet</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($profil['nom']); ?>" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="telephone" style="font-size: 0.8rem; color: var(--text-dim);">Téléphone</label>
            <input type="text" id="telephone" name="telephone" placeholder="+241 XX XX XX XX" value="<?php echo htmlspecialchars($profil['telephone'] ?? ''); ?>" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="adresse_livraison" style="font-size: 0.8rem; color: var(--text-dim);">Adresse de livraison par défaut</label>
            <textarea id="adresse_livraison" name="adresse_livraison" rows="3" placeholder="Votre quartier, rue..." 
                      style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition); resize: vertical;"><?php echo htmlspecialchars($profil['adresse_livraison'] ?? ''); ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-sm" style="justify-content: center; width: 100%; padding: 0.75rem; margin-top: 0.5rem;">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
          </button>
        </form>
      </div>

      <!-- Orders List (Right Columns, col-span-2) -->
      <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 1.5rem;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Historique des commandes</h3>
        
        <?php if (empty($commandes)): ?>
          <div style="text-align: center; padding: 4rem 2rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-muted);"><i class="fa-solid fa-utensils"></i></div>
            <h4>Aucune commande trouvée</h4>
            <p class="text-dim" style="margin-top: 0.5rem; margin-bottom: 1.5rem;">Vous n'avez pas encore passé de commande.</p>
            <a href="menu.php" class="btn btn-primary btn-sm">Découvrir le menu et commander</a>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($commandes as $cmd): ?>
              <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                
                <div class="d-flex justify-between align-center" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem;">
                  <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Commande</span>
                    <h4 style="font-family: var(--font-body); font-size: 1.05rem;">#<?php echo $cmd['id_commande']; ?></h4>
                  </div>
                  <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; display: block; text-align: right;">Date</span>
                    <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-dim);">
                      <?php echo date('d M Y à H:i', strtotime($cmd['date_commande'])); ?>
                    </span>
                  </div>
                </div>

                <div class="d-flex justify-between align-center">
                  <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; display: block;">Restaurant</span>
                    <strong style="font-size: 1rem; color: var(--text);"><?php echo htmlspecialchars($cmd['restaurant_nom'] ?? 'N/A'); ?></strong>
                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.2rem;">
                      Mode : <?php echo $cmd['type_livraison'] === 'retrait' ? '<i class="fa-solid fa-person-walking" style="margin-right: 4px;"></i> Retrait' : '<i class="fa-solid fa-car-side" style="margin-right: 4px;"></i> Livraison'; ?>
                    </span>
                  </div>

                  <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; display: block;">Montant Total</span>
                    <strong class="text-primary" style="font-size: 1.1rem;"><?php echo number_format($cmd['montant_total'], 0, ',', ' '); ?> XAF</strong>
                  </div>
                </div>

                <div class="d-flex justify-between align-center" style="background: var(--dark-3); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid rgba(255,255,255,0.03);">
                  <span style="font-size: 0.85rem; color: var(--text-dim);">Statut de la commande :</span>
                  <span class="order-status status-<?php echo $cmd['statut']; ?>" style="font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.75rem; border-radius: var(--radius-pill);">
                    <?php echo ucfirst(str_replace('_', ' ', $cmd['statut'])); ?>
                  </span>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
