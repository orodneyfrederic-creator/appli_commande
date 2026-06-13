<?php
include_once 'includes/session.php';
global $current_user;
require_login();

// Récupérer le profil utilisateur pour préremplir l'adresse de livraison par défaut
include_once __DIR__ . '/../Backend/config/database.php';
include_once __DIR__ . '/../Backend/models/Utilisateur.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new Utilisateur($db);
$userObj->id_utilisateur = $current_user['id_utilisateur'];
$profil = $userObj->getProfil();

$page_title = "Finaliser la commande";
include_once 'includes/header.php';
?>

<!-- Banner Section -->
<section style="background: var(--page-banner-bg); padding: 4rem 0; text-align: center; border-bottom: 1px solid var(--border);">
  <div class="container reveal">
    <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-family: var(--font-heading); margin-bottom: 0.5rem; color: var(--page-banner-text);">Validation de la Commande</h1>
    <div class="divider"></div>
  </div>
</section>

<!-- Checkout Form Section -->
<section class="section" style="background: var(--dark-2);">
  <div class="container">
    
    <div class="grid-3">
      <!-- Formulaire de Livraison (Colonne Gauche, col-span-2) -->
      <div style="grid-column: span 2; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 3rem;" class="reveal">
        <h2 style="font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Informations de Livraison</h2>
        
        <form id="checkout-form" style="display: flex; flex-direction: column; gap: 1.5rem;">
          <!-- Choix Livraison / Retrait -->
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <label style="font-size: 0.85rem; font-weight: 500; color: var(--text-dim);">Mode de Livraison</label>
            <div style="display: flex; gap: 2rem; margin-top: 0.25rem;">
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="radio" name="type_livraison" value="livraison" checked onchange="toggleAddressField(this.value)" 
                       style="accent-color: var(--primary); width: 18px; height: 18px;">
                <span>Livraison à Domicile (+2 000 XAF)</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="radio" name="type_livraison" value="retrait" onchange="toggleAddressField(this.value)"
                       style="accent-color: var(--primary); width: 18px; height: 18px;">
                <span>Retrait sur place (Gratuit)</span>
              </label>
            </div>
          </div>

          <!-- Champ Adresse de Livraison (affiché uniquement si Livraison) -->
          <div id="address-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
            <label for="adresse_livraison" style="font-size: 0.85rem; font-weight: 500; color: var(--text-dim);">Adresse de Livraison</label>
            <textarea id="adresse_livraison" name="adresse_livraison" required rows="3" placeholder="Quartier, Rue, Indications de livraison à Libreville..." 
                      style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; color: var(--text); font-family: var(--font-body); font-size: 0.95rem; outline: none; transition: var(--transition); resize: vertical;"><?php echo htmlspecialchars($profil['adresse_livraison'] ?? ''); ?></textarea>
          </div>

          <!-- Notes / Instructions spéciales -->
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <label for="notes" style="font-size: 0.85rem; font-weight: 500; color: var(--text-dim);">Instructions Spéciales / Notes</label>
            <textarea id="notes" name="notes" rows="2" placeholder="Ex: Appeler à l'arrivée, sauce à part, etc." 
                      style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; color: var(--text); font-family: var(--font-body); font-size: 0.95rem; outline: none; transition: var(--transition); resize: vertical;"></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="justify-content: center; width: 100%; padding: 0.85rem; margin-top: 1rem;">
            <i class="fa-solid fa-circle-check"></i> Confirmer et payer à la livraison
          </button>
        </form>
      </div>

      <!-- Résumé Sidebar (Colonne Droite) -->
      <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; height: fit-content;" class="reveal">
        <h3 style="border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Ma Commande</h3>
        
        <div id="checkout-items" style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
          <!-- Rempli par main.js -->
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 700;">
          <span>Total (avec livraison)</span>
          <span id="checkout-total" class="text-primary">0 XAF</span>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
function toggleAddressField(val) {
  const addressGroup = document.getElementById('address-group');
  const addressInput = document.getElementById('adresse_livraison');
  if (val === 'retrait') {
    addressGroup.style.display = 'none';
    addressInput.removeAttribute('required');
  } else {
    addressGroup.style.display = 'flex';
    addressInput.setAttribute('required', 'required');
  }
}
</script>

<?php include_once 'includes/footer.php'; ?>
