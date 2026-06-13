  <!-- Footer -->
  <footer class="footer">
    <div class="container footer-inner">
      <div class="footer-col">
        <h4 class="footer-title">GRS Délices</h4>
        <p class="footer-text">
          Le meilleur de la gastronomie locale et internationale livrée directement chez vous à Libreville. Saveur, rapidité et qualité garanties.
        </p>
      </div>
      
      <div class="footer-col">
        <h4 class="footer-title">Liens Rapides</h4>
        <ul class="footer-links">
          <li><a href="/appli_commande/Frontend/index.php">Accueil</a></li>
          <li><a href="/appli_commande/Frontend/menu.php">Le Menu</a></li>
          <li><a href="/appli_commande/Frontend/panier.php">Mon Panier</a></li>
          <?php if ($is_logged_in): ?>
            <li><a href="/appli_commande/Frontend/profil.php">Profil & Commandes</a></li>
          <?php else: ?>
            <li><a href="/appli_commande/Frontend/login.php">Se Connecter</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer-col">
        <h4 class="footer-title">Contactez-nous</h4>
        <ul class="footer-contact">
          <li><i class="fa-solid fa-location-dot" style="margin-right: 8px; opacity: 0.9;"></i> Boulevard du Bord de Mer, Quartier Louis, Libreville</li>
          <li><i class="fa-solid fa-phone" style="margin-right: 8px; opacity: 0.9;"></i> +241 065010993</li>
          <li><i class="fa-solid fa-envelope" style="margin-right: 8px; opacity: 0.9;"></i> contact@grsdelices.com</li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <div class="container footer-bottom-inner">
        <p>&copy; <?php echo date('Y'); ?> GRS Délices.</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="/appli_commande/Frontend/assets/js/main.js"></script>
</body>
</html>
