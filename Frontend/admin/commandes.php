<?php
$page_title = "Gestion des Commandes";
include_once 'includes/admin_header.php';

include_once __DIR__ . '/../../Backend/config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.id_commande, c.id_utilisateur, c.id_restaurant, c.date_commande, c.montant_total, c.statut, c.type_livraison, c.adresse_livraison, c.notes, 
                 u.nom as utilisateur_nom, u.email as client_email, u.telephone as client_telephone, r.nom as restaurant_nom
          FROM commandes c 
          LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur 
          LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant 
          ORDER BY c.date_commande DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section" style="background: var(--dark-2); min-height: 80vh;">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-subtitle">Gestion</span>
      <h1 class="section-title">Les Commandes</h1>
      <div class="divider"></div>
      <p class="section-desc">Suivez les statuts de toutes les commandes passées sur l'application.</p>
    </div>

    <!-- Liste des commandes -->
    <div class="reveal" style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; overflow-x: auto; box-shadow: var(--shadow-lg);">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
        <thead>
          <tr style="border-bottom: 2px solid var(--border); color: var(--text-dim);">
            <th style="padding: 1rem 0.75rem;">ID</th>
            <th style="padding: 1rem 0.75rem;">Client</th>
            <th style="padding: 1rem 0.75rem;">Restaurant</th>
            <th style="padding: 1rem 0.75rem;">Date</th>
            <th style="padding: 1rem 0.75rem;">Détails Livraison</th>
            <th style="padding: 1rem 0.75rem;">Montant</th>
            <th style="padding: 1rem 0.75rem;">Statut</th>
            <th style="padding: 1rem 0.75rem;">Changer Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($commandes)): ?>
            <tr>
              <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucune commande trouvée.</td>
            </tr>
          <?php else: ?>
            <?php foreach($commandes as $c): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="padding: 1rem 0.75rem;"><strong>#<?php echo $c['id_commande']; ?></strong></td>
                <td style="padding: 1rem 0.75rem;">
                  <div><strong><?php echo htmlspecialchars($c['utilisateur_nom'] ?? 'N/A'); ?></strong></div>
                  <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($c['client_email'] ?? ''); ?></div>
                  <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($c['client_telephone'] ?? ''); ?></div>
                </td>
                <td style="padding: 1rem 0.75rem;"><?php echo htmlspecialchars($c['restaurant_nom'] ?? 'N/A'); ?></td>
                <td style="padding: 1rem 0.75rem; white-space: nowrap;">
                  <?php echo date('d M Y H:i', strtotime($c['date_commande'])); ?>
                </td>
                <td style="padding: 1rem 0.75rem; font-size: 0.85rem; max-width: 250px;">
                  <div>Mode: <strong><?php echo ucfirst($c['type_livraison']); ?></strong></div>
                  <?php if ($c['type_livraison'] === 'livraison'): ?>
                    <div class="text-dim" style="font-size: 0.8rem;"><?php echo htmlspecialchars($c['adresse_livraison']); ?></div>
                  <?php endif; ?>
                  <?php if (!empty($c['notes'])): ?>
                    <div style="font-size: 0.8rem; color: var(--gold); font-style: italic;">Note: <?php echo htmlspecialchars($c['notes']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding: 1rem 0.75rem; font-weight: 700;" class="text-primary">
                  <?php echo number_format($c['montant_total'], 0, ',', ' '); ?> XAF
                </td>
                <td style="padding: 1rem 0.75rem;">
                  <span class="order-status status-<?php echo $c['statut']; ?>" style="font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.75rem; border-radius: var(--radius-pill); display: inline-block;">
                    <?php echo ucfirst(str_replace('_', ' ', $c['statut'])); ?>
                  </span>
                </td>
                <td style="padding: 1rem 0.75rem;">
                  <select class="form-control" style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text); padding:0.45rem; font-size:0.85rem; outline: none;" 
                          onchange="updateOrderStatus(<?php echo $c['id_commande']; ?>, this.value); this.parentNode.previousElementSibling.firstElementChild.className = 'order-status status-' + this.value; this.parentNode.previousElementSibling.firstElementChild.textContent = this.value.charAt(0).toUpperCase() + this.value.slice(1).replace('_', ' ');">
                    <?php foreach(['en_attente','confirmée','en_préparation','en_livraison','livrée','annulée'] as $s): ?>
                      <option value="<?php echo $s; ?>" <?php echo $c['statut'] === $s ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $s)); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include_once 'includes/admin_footer.php'; ?>
