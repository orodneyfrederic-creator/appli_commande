<?php
include_once __DIR__ . '/session.php';
global $is_logged_in, $current_user;
$page_title = $page_title ?? 'GRS Délices';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?> — GRS Délices Libreville</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/appli_commande/Frontend/assets/css/style.css">
  
  <!-- Theme Init (Prevent FOUC) -->
  <script>
    if (localStorage.getItem('theme') === 'light' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: light)').matches)) {
      document.documentElement.classList.add('light-theme');
    }
  </script>

  
  <!-- User Metadata for JS -->
  <?php if ($is_logged_in): ?>
    <meta name="user-id" content="<?php echo htmlspecialchars($current_user['id_utilisateur']); ?>">
    <meta name="user-role" content="<?php echo htmlspecialchars($current_user['role'] ?? 'client'); ?>">
  <?php endif; ?>
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    <div class="container navbar-inner">
      <a href="/appli_commande/Frontend/index.php" class="navbar-brand">
        <div class="brand-icon">G</div>
        <div class="brand-text">
          <span class="brand-name">GRS Délices</span>
          <span class="brand-tagline">Libreville</span>
        </div>
      </a>
      
      <div class="nav-links">
        <a href="/appli_commande/Frontend/index.php" class="nav-link">Accueil</a>
        <a href="/appli_commande/Frontend/menu.php" class="nav-link">Le Menu</a>
        <?php if ($is_logged_in): ?>
          <a href="/appli_commande/Frontend/profil.php" class="nav-link">Mon Profil</a>
        <?php endif; ?>
      </div>

      <div class="navbar-actions">
        <!-- Cart -->
        <a href="/appli_commande/Frontend/panier.php" class="cart-btn">
          <i class="fa-solid fa-cart-shopping text-primary"></i> Panier
          <span class="cart-badge">0</span>
        </a>

        <!-- Auth Actions & Theme Toggle -->
        <?php if ($is_logged_in): ?>
          <div class="user-menu-wrapper" style="position: relative; display: inline-block;">
            <div class="user-dropdown">
              <button class="user-btn">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['nom'], 0, 1)); ?></div>
                <span style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?php echo htmlspecialchars($current_user['nom']); ?>
                </span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; margin-left: 2px;"></i>
              </button>
              <div class="dropdown-menu">
                <?php if (($current_user['role'] ?? 'client') === 'admin'): ?>
                  <a href="/appli_commande/Frontend/admin/index.php" class="dropdown-item"><i class="fa-solid fa-chart-line"></i> Tableau de bord</a>
                  <div class="dropdown-divider"></div>
                <?php endif; ?>
                <a href="/appli_commande/Frontend/profil.php" class="dropdown-item"><i class="fa-solid fa-user"></i> Profil & Commandes</a>
                <div class="dropdown-divider"></div>
                <a href="/appli_commande/Frontend/deconnexion.php" class="dropdown-item danger"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
              </div>
            </div>
          </div>
          <!-- Theme Toggle positioned to the right of username wrapper -->
          <button class="theme-toggle" aria-label="Changer de thème" title="Changer de thème">
            <i class="fa-solid fa-moon dark-icon"></i>
            <i class="fa-solid fa-sun light-icon" style="display: none;"></i>
          </button>
        <?php else: ?>
          <a href="/appli_commande/Frontend/login.php" class="btn btn-outline btn-sm">Connexion</a>
          <a href="/appli_commande/Frontend/register.php" class="btn btn-primary btn-sm">S'inscrire</a>
          <button class="theme-toggle" aria-label="Changer de thème" title="Changer de thème">
            <i class="fa-solid fa-moon dark-icon"></i>
            <i class="fa-solid fa-sun light-icon" style="display: none;"></i>
          </button>
        <?php endif; ?>

        <!-- Hamburger Menu for Mobile -->
        <button class="hamburger" aria-label="Menu Mobile">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </nav>
