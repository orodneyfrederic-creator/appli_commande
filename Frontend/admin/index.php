<?php
$page_title = "Vue d'ensemble";
include_once 'includes/admin_header.php';
?>

<!-- Section Admin Dashboard Base -->
<section class="section" id="admin-stats" style="background: var(--dark-2); min-height: 80vh;">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-subtitle">Administration</span>
      <h1 class="section-title">Tableau de bord</h1>
      <div class="divider"></div>
      <p class="section-desc">Statistiques générales et gestion globale des commandes et des restaurants de GRS Délices.</p>
    </div>

    <!-- KPIs Grid -->
    <div class="grid-3" style="margin-bottom: 3rem;">
      <!-- Card 1: Revenus -->
      <div class="reveal" style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 0.5rem; border-left: 4px solid var(--primary);">
        <span class="text-dim" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Revenu Total</span>
        <h2 id="stat-revenus" class="text-primary" style="font-size: 2rem; font-family: var(--font-body); font-weight: 700;">0 XAF</h2>
      </div>

      <!-- Card 2: Commandes -->
      <div class="reveal" style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 0.5rem; border-left: 4px solid var(--gold);">
        <span class="text-dim" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Commandes</span>
        <h2 id="stat-commandes" style="font-size: 2rem; font-family: var(--font-body); font-weight: 700;">0</h2>
      </div>

      <!-- Card 3: Clients -->
      <div class="reveal" style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 0.5rem; border-left: 4px solid var(--success);">
        <span class="text-dim" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Clients</span>
        <h2 id="stat-utilisateurs" style="font-size: 2rem; font-family: var(--font-body); font-weight: 700;">0</h2>
      </div>
    </div>

    <!-- Recent Orders Table Section -->
    <div class="reveal" style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; overflow-x: auto; box-shadow: var(--shadow-lg);">
      <h3 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Commandes Récentes</h3>
      
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
        <thead>
          <tr style="border-bottom: 2px solid var(--border); color: var(--text-dim);">
            <th style="padding: 1rem 0.75rem;">ID</th>
            <th style="padding: 1rem 0.75rem;">Client</th>
            <th style="padding: 1rem 0.75rem;">Restaurant</th>
            <th style="padding: 1rem 0.75rem;">Date</th>
            <th style="padding: 1rem 0.75rem;">Montant</th>
            <th style="padding: 1rem 0.75rem;">Statut</th>
            <th style="padding: 1rem 0.75rem;">Changer Statut</th>
          </tr>
        </thead>
        <tbody id="orders-table-body">
          <!-- Rempli par JS loadAdminOrders() -->
          <tr>
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Chargement en cours...</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</section>

<?php
include_once 'includes/admin_footer.php';
?>
