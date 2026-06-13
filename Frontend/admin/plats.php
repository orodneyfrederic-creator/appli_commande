<?php
$page_title = "Gestion des Plats";
include_once 'includes/admin_header.php';

include_once __DIR__ . '/../../Backend/config/database.php';
$database = new Database();
$db = $database->getConnection();

$success_msg = '';
$error_msg = '';

// Traiter l'ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_save'])) {
        $id_plat = isset($_POST['id_plat']) ? intval($_POST['id_plat']) : 0;
        $id_categorie = isset($_POST['id_categorie']) ? intval($_POST['id_categorie']) : 0;
        $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $prix = isset($_POST['prix']) ? floatval($_POST['prix']) : 0.0;
        $photo = isset($_POST['photo_actuelle']) ? trim($_POST['photo_actuelle']) : '';
        $disponible = isset($_POST['disponible']) ? 1 : 0;

        // Gestion de l'upload de l'image
        if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../assets/images/plats/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_info = pathinfo($_FILES['photo_file']['name']);
            $file_extension = strtolower($file_info['extension'] ?? '');
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $safe_nom = preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($nom));
                $new_filename = $safe_nom . '-' . uniqid() . '.' . $file_extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $destination)) {
                    $photo = '/appli_commande/Frontend/assets/images/plats/' . $new_filename;
                } else {
                    $error_msg = "Erreur lors de l'enregistrement de l'image sur le serveur.";
                }
            } else {
                $error_msg = "Format d'image non autorisé (JPG, PNG, GIF, WEBP uniquement).";
            }
        }

        if ($photo === '') {
            $photo = null;
        }

        if (empty($error_msg) && !empty($nom) && $id_categorie > 0 && $prix >= 0) {
            try {
                if ($id_plat > 0) {
                    // Modification
                    $query = "UPDATE plats SET id_categorie=:id_categorie, nom=:nom, description=:description, prix=:prix, photo=:photo, disponible=:disponible WHERE id_plat=:id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(":id", $id_plat);
                } else {
                    // Création
                    $query = "INSERT INTO plats (id_categorie, nom, description, prix, photo, disponible) VALUES (:id_categorie, :nom, :description, :prix, :photo, :disponible)";
                    $stmt = $db->prepare($query);
                }
                
                $stmt->bindParam(":id_categorie", $id_categorie);
                $stmt->bindParam(":nom", $nom);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":prix", $prix);
                $stmt->bindParam(":photo", $photo);
                $stmt->bindParam(":disponible", $disponible);

                if ($stmt->execute()) {
                    $success_msg = "Plat enregistré avec succès.";
                } else {
                    $error_msg = "Une erreur est survenue.";
                }
            } catch (Exception $e) {
                $error_msg = "Erreur SQL : " . $e->getMessage();
            }
        } else {
            $error_msg = "Veuillez remplir les champs obligatoires (Nom, Catégorie, Prix).";
        }
    }
    
    // Toggle disponible
    if (isset($_POST['action_toggle'])) {
        $id_plat = intval($_POST['id_plat']);
        $disponible = intval($_POST['disponible']);
        try {
            $query = "UPDATE plats SET disponible = :disponible WHERE id_plat = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":disponible", $disponible);
            $stmt->bindParam(":id", $id_plat);
            $stmt->execute();
            $success_msg = "Disponibilité du plat mise à jour.";
        } catch (Exception $e) {
            $error_msg = "Erreur : " . $e->getMessage();
        }
    }
}

// Récupérer toutes les catégories pour le formulaire
$query_cat = "SELECT c.id_categorie, c.nom as cat_nom, r.nom as rest_nom 
              FROM categories_menu c 
              LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant 
              ORDER BY r.nom ASC, c.nom ASC";
$stmt_cat = $db->prepare($query_cat);
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les plats avec leur catégorie et restaurant
$query_plats = "SELECT p.*, c.nom as cat_nom, r.nom as rest_nom 
                FROM plats p 
                LEFT JOIN categories_menu c ON p.id_categorie = c.id_categorie 
                LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant 
                ORDER BY r.nom ASC, c.nom ASC, p.nom ASC";
$stmt_plats = $db->prepare($query_plats);
$stmt_plats->execute();
$plats = $stmt_plats->fetchAll(PDO::FETCH_ASSOC);

// Plat pour modification
$edit_plat = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    foreach($plats as $p) {
        if (intval($p['id_plat']) === $id_edit) {
            $edit_plat = $p;
            break;
        }
    }
}
?>

<section class="section" style="background: var(--dark-2); min-height: 80vh;">
  <div class="container">
    
    <div class="section-header reveal">
      <span class="section-subtitle">Gestion</span>
      <h1 class="section-title">Les Plats du Menu</h1>
      <div class="divider"></div>
      <p class="section-desc">Gérez la carte de vos restaurants, les prix, descriptions et disponibilités.</p>
    </div>

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
      <!-- Formulaire d'ajout / modification (Colonne Gauche) -->
      <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; height: fit-content;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
          <?php echo $edit_plat ? 'Modifier le Plat' : 'Ajouter un Plat'; ?>
        </h3>
        
        <form action="plats.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="id_plat" value="<?php echo $edit_plat ? $edit_plat['id_plat'] : '0'; ?>">
          
          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="id_categorie" style="font-size: 0.8rem; color: var(--text-dim);">Catégorie & Restaurant *</label>
            <select id="id_categorie" name="id_categorie" required 
                    style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text); padding: 0.65rem 0.85rem; font-family: var(--font-body); font-size: 0.9rem; outline: none;">
              <option value="">-- Sélectionner une catégorie --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id_categorie']; ?>" <?php echo ($edit_plat && $edit_plat['id_categorie'] == $cat['id_categorie']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($cat['cat_nom']); ?> (<?php echo htmlspecialchars($cat['rest_nom']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="nom" style="font-size: 0.8rem; color: var(--text-dim);">Nom du Plat *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo $edit_plat ? htmlspecialchars($edit_plat['nom']) : ''; ?>" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="prix" style="font-size: 0.8rem; color: var(--text-dim);">Prix (XAF) *</label>
            <input type="number" id="prix" name="prix" required min="0" step="50" value="<?php echo $edit_plat ? htmlspecialchars($edit_plat['prix']) : ''; ?>" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="description" style="font-size: 0.8rem; color: var(--text-dim);">Description</label>
            <textarea id="description" name="description" rows="4" 
                      style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition); resize: vertical;"><?php echo $edit_plat ? htmlspecialchars($edit_plat['description'] ?? '') : ''; ?></textarea>
          </div>

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="photo_file" style="font-size: 0.8rem; color: var(--text-dim);">Photo du Plat</label>
            <?php if ($edit_plat && !empty($edit_plat['photo'])): ?>
              <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <img src="<?php echo htmlspecialchars($edit_plat['photo']); ?>" alt="Photo actuelle" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <span style="font-size: 0.8rem; color: var(--text-muted);">Photo actuelle conservée si vous n'en sélectionnez pas une nouvelle.</span>
              </div>
            <?php endif; ?>
            <input type="hidden" name="photo_actuelle" value="<?php echo $edit_plat ? htmlspecialchars($edit_plat['photo'] ?? '') : ''; ?>">
            <input type="file" id="photo_file" name="photo_file" accept="image/*" 
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.55rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
            <input type="checkbox" id="disponible" name="disponible" value="1" <?php echo (!$edit_plat || $edit_plat['disponible'] == 1) ? 'checked' : ''; ?> 
                   style="accent-color: var(--primary); width: 18px; height: 18px; cursor: pointer;">
            <label for="disponible" style="font-size: 0.9rem; color: var(--text-dim); cursor: pointer;">Plat Disponible</label>
          </div>

          <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
            <button type="submit" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center; padding: 0.75rem;">
              <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
            <?php if ($edit_plat): ?>
              <a href="plats.php" class="btn btn-dark btn-sm" style="justify-content: center; padding: 0.75rem; text-decoration: none;">
                Annuler
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Liste des Plats (Colonne Droite, col-span-2) -->
      <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 1.5rem;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Plats Actuels</h3>
        
        <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; overflow-x: auto; box-shadow: var(--shadow-lg);">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border); color: var(--text-dim);">
                <th style="padding: 0.75rem;">Nom</th>
                <th style="padding: 0.75rem;">Restaurant & Catégorie</th>
                <th style="padding: 0.75rem;">Prix</th>
                <th style="padding: 0.75rem; text-align: center;">Disponibilité</th>
                <th style="padding: 0.75rem; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($plats as $p): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                  <td style="padding: 0.75rem 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                      <?php if (!empty($p['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($p['photo']); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                      <?php else: ?>
                        <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--dark-3); border-radius: 4px; border: 1px solid var(--border); font-size: 1.2rem; color: var(--primary);">
                          <i class="fa-solid fa-utensils"></i>
                        </div>
                      <?php endif; ?>
                      <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                    </div>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; font-size: 0.85rem;" class="text-dim">
                    <div><?php echo htmlspecialchars($p['rest_nom']); ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($p['cat_nom']); ?></div>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: var(--primary);">
                    <?php echo number_format($p['prix'], 0, ',', ' '); ?> XAF
                  </td>
                  <td style="padding: 0.75rem 0.5rem; text-align: center;">
                    <form action="plats.php" method="POST" style="margin: 0;">
                      <input type="hidden" name="action_toggle" value="1">
                      <input type="hidden" name="id_plat" value="<?php echo $p['id_plat']; ?>">
                      <input type="hidden" name="disponible" value="<?php echo $p['disponible'] == 1 ? '0' : '1'; ?>">
                      <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 0.85rem;" 
                              class="order-status <?php echo $p['disponible'] == 1 ? 'status-livrée' : 'status-annulée'; ?>"
                              title="Cliquez pour changer la disponibilité">
                        <?php echo $p['disponible'] == 1 ? 'Disponible' : 'Épuisé'; ?>
                      </button>
                    </form>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; text-align: right; white-space: nowrap;">
                    <a href="plats.php?edit=<?php echo $p['id_plat']; ?>" class="btn btn-outline btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                      <i class="fa-solid fa-pen-to-square"></i> Éditer
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include_once 'includes/admin_footer.php'; ?>
