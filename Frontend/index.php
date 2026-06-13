<?php
$page_title = "Accueil";
include_once 'includes/header.php';
require_once __DIR__ . '/../Backend/config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT photo, nom FROM plats WHERE disponible=1 AND photo IS NOT NULL AND photo != '' ORDER BY RAND() LIMIT 10");
$slideshow_plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-particles"></div>
  <div class="container d-flex align-center justify-between" style="min-height: 100vh; position: relative; z-index: 2;">
    <div class="hero-content reveal">
      <h1 class="hero-title">
        Savourer les délices de <span>GRS Délices</span> chez vous
      </h1>
      <p class="hero-desc">
        Commandez en quelques clics les meilleurs plats de vos restaurants préférés à Libreville. Livraison ultra-rapide et paiement sécurisé (Mobile Money, Espèces, Carte).
      </p>
      <div class="hero-actions">
        <a href="/appli_commande/Frontend/menu.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-utensils"></i> Commander Maintenant</a>
        <a href="#how-it-works" class="btn btn-outline btn-lg">En savoir plus</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <span class="number">10k+</span>
          <span class="label">Clients Satisfaits</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="number">50+</span>
          <span class="label">Plats Exquis</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="number">15min</span>
          <span class="label">Temps de Livraison</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Hero Slideshow -->
  <div class="hero-slideshow-wrapper" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 55vw; height: 600px; overflow: hidden; z-index: 1; display: flex; align-items: center;
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 22%, black 92%, transparent 100%);
    mask-image: linear-gradient(to right, transparent 0%, black 22%, black 92%, transparent 100%);">
    
    <div class="hero-slideshow-track">
      <?php 
      // Duplicate for infinite scroll
      $infinite_plats = array_merge($slideshow_plats, $slideshow_plats);
      foreach($infinite_plats as $plat): ?>
        <div class="hero-slide">
          <img src="<?php echo htmlspecialchars($plat['photo']); ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>">
          <div class="hero-slide-caption">
            <?php echo htmlspecialchars($plat['nom']); ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- How it works Section -->
<section class="section" id="how-it-works" style="background: var(--dark-3);">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-subtitle">Simplicité</span>
      <h2 class="section-title">Comment ça marche ?</h2>
      <div class="divider"></div>
      <p class="section-desc">Commandez vos plats préférés en 3 étapes simples et rapides.</p>
    </div>
    
    <div class="grid-3">
      <!-- Step 1 -->
      <div class="feature-card reveal" style="text-align: center; padding: 2.5rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--primary);"><i class="fa-solid fa-book-open"></i></div>
        <h3 style="margin-bottom: 1rem;">1. Explorez</h3>
        <p class="text-dim">Découvrez notre carte variée de spécialités locales et internationales faites maison.</p>
      </div>
      <!-- Step 2 -->
      <div class="feature-card reveal" style="text-align: center; padding: 2.5rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--primary);"><i class="fa-solid fa-cart-shopping"></i></div>
        <h3 style="margin-bottom: 1rem;">2. Commandez</h3>
        <p class="text-dim">Ajoutez vos plats préférés au panier, choisissez votre mode de réception et validez.</p>
      </div>
      <!-- Step 3 -->
      <div class="feature-card reveal" style="text-align: center; padding: 2.5rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--primary);"><i class="fa-solid fa-drumstick-bite"></i></div>
        <h3 style="margin-bottom: 1rem;">3. Savourez</h3>
        <p class="text-dim">Faites-vous livrer en moins de 30 minutes ou récupérez votre commande directement au restaurant.</p>
      </div>
    </div>
  </div>
</section>

<!-- Featured Restaurants Section -->
<section class="section restaurants-section">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-subtitle">Gastronomie</span>
      <h2 class="section-title">Notre Structure</h2>
      <div class="divider"></div>
      <p class="section-desc">L'adresse exclusive de GRS Délices à Libreville pour des moments culinaires uniques.</p>
    </div>
    
    <div style="background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 3rem; display: flex; gap: 3rem; align-items: center; flex-wrap: wrap;" class="reveal">
      <div style="flex: 1; min-width: 300px;">
        <div style="font-size: 5rem; line-height: 1; margin-bottom: 1.5rem; color: var(--primary);"><i class="fa-solid fa-kitchen-set"></i></div>
        <h3 style="font-family: var(--font-heading); font-size: 2.2rem; margin-bottom: 1rem;">GRS Délices</h3>
        <p class="text-dim" style="line-height: 1.8; margin-bottom: 2rem;">
          Restaurant gastronomique proposant des spécialités africaines et internationales dans un cadre chaleureux au cœur de Libreville. Commandez en ligne et dégustez chez vous nos plats fraîchement préparés.
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem; font-size: 0.95rem;" class="text-muted">
          <span><i class="fa-solid fa-location-dot text-primary" style="margin-right: 8px;"></i> Boulevard du Bord de Mer, Quartier Louis, Libreville</span>
          <span><i class="fa-solid fa-phone text-primary" style="margin-right: 8px;"></i> +241 065010993</span>
          <span><i class="fa-solid fa-envelope text-primary" style="margin-right: 8px;"></i> contact@grsdelices.com</span>
        </div>
        <a href="/appli_commande/Frontend/menu.php" class="btn btn-primary" style="width: fit-content; padding: 0.85rem 2rem;">
          <i class="fa-solid fa-book-open"></i> Découvrir la Carte & Commander
        </a>
      </div>
      <div style="flex: 1; min-width: 300px; display: flex; justify-content: center;">
        <div style="position: relative; width: 100%; max-width: 450px; height: 320px; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
          <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(232, 121, 11, 0.1) 0%, rgba(240, 168, 48, 0.2) 100%);"></div>
          <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; gap: 1rem; text-align: center; padding: 2rem;">
            <div style="font-size: 4rem; color: var(--primary);"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <h4 style="font-family: var(--font-heading); font-size: 1.4rem;">Fraîcheur & Qualité</h4>
            <p class="text-dim" style="font-size: 0.9rem; max-width: 280px;">Tous nos plats sont préparés à la minute avec des ingrédients locaux frais du marché.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="section" style="background: var(--dark-3);">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-subtitle">Avis clients</span>
      <h2 class="section-title">Ce que disent nos clients</h2>
      <div class="divider"></div>
      <p class="section-desc">Découvrez les retours de nos clients fidèles à Libreville.</p>
    </div>
    
    <div class="grid-3">
      <!-- Testimonial 1 -->
      <div class="testimonial-card reveal" style="padding: 2rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="color: var(--gold); font-size: 0.9rem; margin-bottom: 1rem; display: flex; gap: 4px;">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </div>
        <p class="text-dim" style="font-style: italic; margin-bottom: 1.5rem;">
          "Le Ndolé aux crevettes est tout simplement exceptionnel. Livré bien chaud en 20 minutes chrono. Je recommande vivement !"
        </p>
        <div class="d-flex align-center gap-2">
          <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff;">A</div>
          <div>
            <h4 style="font-size: 0.95rem;">Atsame Marc</h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Client Fidèle, Libreville</span>
          </div>
        </div>
      </div>
      <!-- Testimonial 2 -->
      <div class="testimonial-card reveal" style="padding: 2rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="color: var(--gold); font-size: 0.9rem; margin-bottom: 1rem; display: flex; gap: 4px;">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </div>
        <p class="text-dim" style="font-style: italic; margin-bottom: 1.5rem;">
          "Poulet DG succulent ! Le service de commande en ligne est fluide, et les portions sont généreuses. Parfait pour nos repas de famille."
        </p>
        <div class="d-flex align-center gap-2">
          <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff;">M</div>
          <div>
            <h4 style="font-size: 0.95rem;">Moussavou Estelle</h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Client, Quartier Louis</span>
          </div>
        </div>
      </div>
      <!-- Testimonial 3 -->
      <div class="testimonial-card reveal" style="padding: 2rem; background: var(--dark-4); border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div style="color: var(--gold); font-size: 0.9rem; margin-bottom: 1rem; display: flex; gap: 4px;">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </div>
        <p class="text-dim" style="font-style: italic; margin-bottom: 1.5rem;">
          "Première commande et totalement conquise. Le Jus de Bissap maison est excellent. Le retrait sur place s'est fait très rapidement."
        </p>
        <div class="d-flex align-center gap-2">
          <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff;">N</div>
          <div>
            <h4 style="font-size: 0.95rem;">Nguema Christian</h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Client, Libreville</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
include_once 'includes/footer.php';
?>
