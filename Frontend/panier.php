<?php
$page_title = "Votre Panier";
include_once 'includes/header.php';
?>

<!-- Banner Section -->
<section style="background: var(--page-banner-bg); padding: 4rem 0; text-align: center; border-bottom: 1px solid var(--border);">
  <div class="container reveal">
    <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-family: var(--font-heading); margin-bottom: 0.5rem; color: var(--page-banner-text);">Votre Panier</h1>
    <div class="divider"></div>
  </div>
</section>

<!-- Cart Details Section -->
<section class="section" style="background: var(--dark-2);">
  <div class="container">
    
    <!-- Panier Vide -->
    <div id="cart-empty" style="display: none; text-align: center; padding: 4rem 2rem;" class="reveal">
      <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--text-muted);"><i class="fa-solid fa-cart-shopping"></i></div>
      <h2 style="margin-bottom: 1rem;">Votre panier est vide</h2>
      <p class="text-dim" style="margin-bottom: 2rem;">Vous n'avez pas encore ajouté de plat à votre panier.</p>
      <a href="/appli_commande/Frontend/menu.php" class="btn btn-primary"><i class="fa-solid fa-utensils"></i> Consulter la carte</a>
    </div>
 
    <!-- Panier Actif -->
    <div id="cart-section" style="display: none;" class="reveal">
      <div class="grid-3">
        <!-- Liste des Plats (Colonne Gauche, col-span-2) -->
        <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 1.5rem;">
          <div id="cart-items" style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Rempli par main.js -->
          </div>
          
          <div style="margin-top: 1rem; display: flex; gap: 1rem;">
            <a href="/appli_commande/Frontend/menu.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Retour au menu</a>
            <button onclick="if(confirm('Voulez-vous vraiment vider votre panier ?')) { Cart.clear(); }" class="btn btn-dark"><i class="fa-solid fa-trash-can"></i> Vider le panier</button>
          </div>
        </div>

        <!-- Résumé Sidebar (Colonne Droite) -->
        <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; height: fit-content;">
          <h3 style="border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Résumé de la commande</h3>
          
          <div style="display: flex; justify-content: space-between; font-size: 0.95rem;">
            <span class="text-dim">Sous-total</span>
            <span id="summary-subtotal" style="font-weight: 600;">0 XAF</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.95rem;">
            <span class="text-dim">Frais de livraison</span>
            <span id="summary-livraison" style="font-weight: 600;">2 000 XAF</span>
          </div>
          
          <div style="border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 700;">
            <span>Total</span>
            <span id="summary-total" class="text-primary">0 XAF</span>
          </div>
          
          <a id="checkout-btn" href="#" class="btn btn-primary" style="justify-content: center; width: 100%; padding: 0.85rem; margin-top: 0.5rem;">
            Passer la commande &rarr;
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
