<?php
$id_restaurant = 1; // GRS Délices unique structure

include_once __DIR__ . '/../Backend/config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT nom, adresse, ville, telephone, description FROM restaurants WHERE id_restaurant = :id LIMIT 0,1";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $id_restaurant);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: index.php");
    exit();
}

$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Menu " . $restaurant['nom'];
include_once 'includes/header.php';
?>

<!-- Restaurant Banner Section -->
<section style="background: var(--menu-banner-bg); padding: 6rem 0 4rem 0; border-bottom: 1px solid var(--border); position: relative;">
  <div class="container reveal">
    <!-- Back link removed for single-restaurant model -->
    <h1 class="restaurant-name" style="font-size: clamp(2.2rem, 6vw, 4rem); font-family: var(--font-heading); margin-bottom: 1rem;">
      <?php echo htmlspecialchars($restaurant['nom']); ?>
    </h1>
    <p class="text-dim" style="max-width: 700px; margin-bottom: 1.5rem; line-height: 1.8;">
      <?php echo htmlspecialchars($restaurant['description'] ?? 'Découvrez les plats raffinés préparés par nos chefs.'); ?>
    </p>
    <div class="d-flex flex-wrap gap-3" style="font-size: 0.9rem; color: var(--text-muted);">
      <span><i class="fa-solid fa-location-dot text-primary" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($restaurant['adresse']); ?>, <?php echo htmlspecialchars($restaurant['ville']); ?></span>
      <?php if (!empty($restaurant['telephone'])): ?>
        <span><i class="fa-solid fa-phone text-primary" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($restaurant['telephone']); ?></span>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Category Selection Pills Nav sticky -->
<section style="position: sticky; top: 72px; z-index: 90; background: var(--menu-nav-bg); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: 1rem 0;">
  <div class="container d-flex align-center gap-2 overflow-x-auto" id="category-pills" style="padding-bottom: 0.25rem; white-space: nowrap;">
    <!-- Pills loaded by JS -->
  </div>
</section>

<!-- Menu Cards List -->
<section class="section" style="background: var(--dark-2);">
  <div class="container" id="menu-container">
    <!-- Menu categories and items loaded by JS -->
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
