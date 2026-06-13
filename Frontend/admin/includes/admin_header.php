<?php
include_once __DIR__ . '/admin_session.php';
global $is_logged_in, $current_user;
$page_title = $page_title ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?> — Administration GRS Délices</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/appli_commande/Frontend/assets/css/style.css">
  
  <!-- Metadata -->
  <?php if ($is_logged_in): ?>
    <meta name="user-id" content="<?php echo htmlspecialchars($current_user['id_utilisateur']); ?>">
    <meta name="user-role" content="<?php echo htmlspecialchars($current_user['role'] ?? 'client'); ?>">
  <?php endif; ?>
</head>
<body>

  <!-- Admin Navbar -->
  <nav class="navbar" style="border-bottom: 2px solid var(--primary);">
    <div class="container navbar-inner">
      <a href="/appli_commande/Frontend/admin/index.php" class="navbar-brand">
        <div class="brand-icon" style="background: linear-gradient(135deg, var(--gold), var(--primary));"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="brand-text">
          <span class="brand-name">GRS Admin</span>
          <span class="brand-tagline">Tableau de bord</span>
        </div>
      </a>
      
      <div class="nav-links">
        <a href="/appli_commande/Frontend/admin/index.php" class="nav-link">Vue d'ensemble</a>
        <a href="/appli_commande/Frontend/admin/commandes.php" class="nav-link">Commandes</a>
        <a href="/appli_commande/Frontend/admin/plats.php" class="nav-link">Plats</a>
        <a href="/appli_commande/Frontend/admin/categories.php" class="nav-link">Catégories</a>
        <a href="/appli_commande/Frontend/index.php" class="nav-link text-primary" style="font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Retour au site</a>
      </div>

      <div class="navbar-actions" style="gap: 1rem;">
        <div class="d-flex align-center gap-2">
          <div class="user-avatar" style="background: var(--primary);"><?php echo strtoupper(substr($current_user['nom'], 0, 1)); ?></div>
          <span style="font-size: 0.9rem; font-weight: 600; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <?php echo htmlspecialchars($current_user['nom']); ?>
          </span>
        </div>
        <a href="/appli_commande/Frontend/deconnexion.php" class="btn btn-dark btn-sm" style="padding: 0.45rem 0.85rem;" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></a>
      </div>
    </div>
  </nav>
