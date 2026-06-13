<?php
$page_title = "Gestion des Catégories";
include_once 'includes/admin_header.php';

include_once __DIR__ . '/../../Backend/config/database.php';
$database = new Database();
$db = $database->getConnection();

$success_msg = '';
$error_msg = '';

// Récupérer le restaurant par défaut (unique)
$stmt_rest = $db->query("SELECT id_restaurant FROM restaurants ORDER BY id_restaurant ASC LIMIT 1");
$default_restaurant = $stmt_rest->fetchColumn();

// Traitement : ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_save'])) {
        $id_cat = isset($_POST['id_categorie']) ? intval($_POST['id_categorie']) : 0;
        $nom = trim($_POST['nom'] ?? '');
        $id_restaurant = intval($_POST['id_restaurant'] ?? $default_restaurant);

        if (!empty($nom) && $id_restaurant > 0) {
            try {
                if ($id_cat > 0) {
                    $stmt = $db->prepare("UPDATE categories_menu SET nom=:nom, id_restaurant=:id_restaurant WHERE id_categorie=:id");
                    $stmt->bindParam(':id', $id_cat);
                } else {
                    $stmt = $db->prepare("INSERT INTO categories_menu (nom, id_restaurant) VALUES (:nom, :id_restaurant)");
                }
                $stmt->bindParam(':nom', $nom);
                $stmt->bindParam(':id_restaurant', $id_restaurant);
                if ($stmt->execute()) {
                    $success_msg = "Catégorie enregistrée avec succès.";
                } else {
                    $error_msg = "Une erreur est survenue lors de l'enregistrement.";
                }
            } catch (Exception $e) {
                $error_msg = "Erreur SQL : " . $e->getMessage();
            }
        } else {
            $error_msg = "Veuillez remplir tous les champs obligatoires.";
        }
    }

    // Suppression
    if (isset($_POST['action_delete'])) {
        $id_cat = intval($_POST['id_categorie'] ?? 0);
        if ($id_cat > 0) {
            try {
                // Vérifier qu'il n'y a pas de plats liés
                $check = $db->prepare("SELECT COUNT(*) FROM plats WHERE id_categorie = :id");
                $check->bindParam(':id', $id_cat);
                $check->execute();
                $count = $check->fetchColumn();
                if ($count > 0) {
                    $error_msg = "Impossible de supprimer : cette catégorie contient $count plat(s). Supprimez ou déplacez les plats d'abord.";
                } else {
                    $del = $db->prepare("DELETE FROM categories_menu WHERE id_categorie = :id");
                    $del->bindParam(':id', $id_cat);
                    $del->execute();
                    $success_msg = "Catégorie supprimée.";
                }
            } catch (Exception $e) {
                $error_msg = "Erreur : " . $e->getMessage();
            }
        }
    }
}

// Récupérer toutes les catégories avec leur restaurant + nombre de plats
$query = "SELECT c.id_categorie, c.nom as cat_nom, r.nom as rest_nom,
          (SELECT COUNT(*) FROM plats p WHERE p.id_categorie = c.id_categorie) as nb_plats
          FROM categories_menu c
          LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant
          ORDER BY r.nom ASC, c.nom ASC";
$categories = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Catégorie en cours d'édition
$edit_cat = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    foreach ($categories as $c) {
        if (intval($c['id_categorie']) === $id_edit) {
            $edit_cat = $c;
            break;
        }
    }
}
?>

<section class="section" style="background: var(--dark-2); min-height: 80vh;">
  <div class="container">

    <div class="section-header reveal">
      <span class="section-subtitle">Gestion</span>
      <h1 class="section-title">Catégories du Menu</h1>
      <div class="divider"></div>
      <p class="section-desc">Créez et gérez les catégories (Entrées, Plats, Boissons, Desserts…) pour chaque restaurant.</p>
    </div>

    <?php if (!empty($success_msg)): ?>
      <div style="background: rgba(39,174,96,0.15); border: 1px solid var(--success); color: var(--success); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($success_msg); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div style="background: rgba(231,76,60,0.15); border: 1px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <div class="grid-3">
      <!-- Formulaire -->
      <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; height: fit-content;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
          <?php echo $edit_cat ? 'Modifier la Catégorie' : 'Nouvelle Catégorie'; ?>
        </h3>

        <form action="categories.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="id_categorie" value="<?php echo $edit_cat ? $edit_cat['id_categorie'] : '0'; ?>">

          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <label for="nom_cat" style="font-size: 0.8rem; color: var(--text-dim);">Nom de la Catégorie *</label>
            <input type="text" id="nom_cat" name="nom" required
                   value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['cat_nom']) : ''; ?>"
                   placeholder="Ex : Entrées, Plats principaux, Boissons…"
                   style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: var(--text); font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: var(--transition);">
          </div>

          <input type="hidden" name="id_restaurant" value="<?php echo intval($default_restaurant); ?>">

          <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
            <button type="submit" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center; padding: 0.75rem;">
              <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
            <?php if ($edit_cat): ?>
              <a href="categories.php" class="btn btn-dark btn-sm" style="justify-content: center; padding: 0.75rem; text-decoration: none;">Annuler</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Liste des catégories -->
      <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 1.5rem;" class="reveal">
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
          Catégories Actuelles <span style="font-size: 1rem; color: var(--text-muted); font-weight: 400;">(<?php echo count($categories); ?>)</span>
        </h3>

        <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; overflow-x: auto; box-shadow: var(--shadow-lg);">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border); color: var(--text-dim);">
                <th style="padding: 0.75rem;">Nom de la catégorie</th>
                <th style="padding: 0.75rem;">Restaurant</th>
                <th style="padding: 0.75rem; text-align: center;">Nb de plats</th>
                <th style="padding: 0.75rem; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                  <td style="padding: 0.75rem 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                      <div style="width: 34px; height: 34px; background: rgba(232,121,11,0.1); border: 1px solid rgba(232,121,11,0.3); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.9rem;">
                        <i class="fa-solid fa-tag"></i>
                      </div>
                      <strong><?php echo htmlspecialchars($cat['cat_nom']); ?></strong>
                    </div>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; color: var(--text-dim); font-size: 0.9rem;">
                    <?php echo htmlspecialchars($cat['rest_nom'] ?? '—'); ?>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; text-align: center;">
                    <span style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-pill); padding: 0.2rem 0.75rem; font-size: 0.85rem; font-weight: 600; color: var(--primary);">
                      <?php echo $cat['nb_plats']; ?>
                    </span>
                  </td>
                  <td style="padding: 0.75rem 0.5rem; text-align: right; white-space: nowrap;">
                    <a href="categories.php?edit=<?php echo $cat['id_categorie']; ?>" class="btn btn-outline btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; margin-right: 4px;">
                      <i class="fa-solid fa-pen-to-square"></i> Éditer
                    </a>
                    <?php if ($cat['nb_plats'] == 0): ?>
                      <form action="categories.php" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer la catégorie « <?php echo htmlspecialchars($cat['cat_nom']); ?> » ?')">
                        <input type="hidden" name="action_delete" value="1">
                        <input type="hidden" name="id_categorie" value="<?php echo $cat['id_categorie']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                    <?php else: ?>
                      <button class="btn btn-sm" disabled title="Impossible de supprimer : contient des plats" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; opacity: 0.4; cursor: not-allowed; background: var(--dark-3); border: 1px solid var(--border);">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($categories)): ?>
                <tr>
                  <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                    Aucune catégorie trouvée. Créez-en une !
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include_once 'includes/admin_footer.php'; ?>
