<?php
include_once 'includes/session.php';

if ($is_logged_in) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    if (!empty($email) && !empty($password)) {
        // Appeler l'API REST de login
        $api_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/appli_commande/Backend/api/auth/login.php";
        
        $ch = curl_init($api_url);
        $payload = json_encode(['email' => $email, 'mot_de_passe' => $password]);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // timeout court
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            $_SESSION['user'] = $data['utilisateur'];
            
            // Redirection
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: " . $redirect);
            exit();
        } else {
            $data = json_decode($response, true);
            $error_message = isset($data['message']) ? $data['message'] : "Email ou mot de passe incorrect.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}

$page_title = "Connexion";
include_once 'includes/header.php';
?>

<section class="section d-flex align-center justify-center" style="min-height: calc(100vh - 72px); background: var(--dark-2);">
  <div class="container reveal" style="max-width: 450px;">
    <div style="background: var(--dark-4); padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
      <div style="text-align: center; margin-bottom: 2rem;">
        <h2 style="font-size: 2.2rem; margin-bottom: 0.5rem;">Se Connecter</h2>
        <p class="text-dim" style="font-size: 0.9rem;">Accédez à votre compte GRS Délices</p>
      </div>

      <?php if (isset($_GET['registered'])): ?>
        <div style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--success); color: var(--success); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem;">
          <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> Votre compte a été créé avec succès ! Connectez-vous maintenant.
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem;">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label for="email" style="font-size: 0.85rem; font-weight: 500; color: var(--text-dim);">Adresse Email</label>
          <input type="email" id="email" name="email" required placeholder="nom@exemple.com" 
                 style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; color: var(--text); font-family: var(--font-body); font-size: 0.95rem; outline: none; transition: var(--transition);">
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label for="mot_de_passe" style="font-size: 0.85rem; font-weight: 500; color: var(--text-dim);">Mot de passe</label>
          <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="Votre mot de passe" 
                 style="background: var(--dark-3); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; color: var(--text); font-family: var(--font-body); font-size: 0.95rem; outline: none; transition: var(--transition);">
        </div>

        <button type="submit" class="btn btn-primary" style="justify-content: center; width: 100%; padding: 0.85rem; margin-top: 1rem;">
          <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
        </button>
      </form>

      <div style="text-align: center; margin-top: 2rem; font-size: 0.9rem;">
        <span class="text-dim">Pas encore de compte ?</span> 
        <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" style="color: var(--primary); font-weight: 600;">S'inscrire</a>
      </div>
    </div>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
