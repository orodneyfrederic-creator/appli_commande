/**
 * GRS Délices — main.js
 * Cart management, scroll animations, UI helpers, fetch API
 */

/* ================================================================
   CONFIGURATION
   ================================================================ */
const CONFIG = {
  API_BASE: '/appli_commande/Backend/api',
  CART_KEY: 'grs_cart',
  CART_TS_KEY: 'grs_cart_ts',     // timestamp of last cart activity (guests only)
  CART_EXPIRY_MS: 10 * 60 * 1000, // 10 minutes in milliseconds
  CURRENCY: 'XAF',
};

/* ================================================================
   CART API (localStorage)
   ================================================================ */
const Cart = {
  get() {
    try {
      return JSON.parse(localStorage.getItem(CONFIG.CART_KEY)) || [];
    } catch {
      return [];
    }
  },

  save(items) {
    localStorage.setItem(CONFIG.CART_KEY, JSON.stringify(items));
    // Refresh expiry timestamp only for guest users
    if (!document.querySelector('meta[name="user-id"]')?.content) {
      localStorage.setItem(CONFIG.CART_TS_KEY, Date.now().toString());
    }
    this.updateBadge();
    this.dispatchChange();
  },

  add(item) {
    const items = this.get();
    const existingIndex = items.findIndex(i => i.id_plat === item.id_plat);
    if (existingIndex > -1) {
      items[existingIndex].quantite += item.quantite || 1;
    } else {
      items.push({ ...item, quantite: item.quantite || 1 });
    }
    this.save(items);
    showToast(`${item.nom} ajouté au panier`, 'success');
  },

  remove(id_plat) {
    const items = this.get().filter(i => i.id_plat !== id_plat);
    this.save(items);
  },

  updateQty(id_plat, quantite) {
    const items = this.get();
    const idx = items.findIndex(i => i.id_plat === id_plat);
    if (idx > -1) {
      if (quantite <= 0) {
        items.splice(idx, 1);
      } else {
        items[idx].quantite = quantite;
      }
    }
    this.save(items);
  },

  clear() {
    localStorage.removeItem(CONFIG.CART_KEY);
    localStorage.removeItem(CONFIG.CART_TS_KEY);
    this.updateBadge();
    this.dispatchChange();
  },

  getTotal() {
    return this.get().reduce((sum, item) => sum + item.prix * item.quantite, 0);
  },

  getTotalItems() {
    return this.get().reduce((sum, item) => sum + item.quantite, 0);
  },

  getRestaurantId() {
    const items = this.get();
    return items.length > 0 ? items[0].id_restaurant : null;
  },

  updateBadge() {
    const count = this.getTotalItems();
    
    // Header cart badge
    const badge = document.querySelector('.navbar .cart-badge');
    if (badge) {
      badge.textContent = count;
      badge.classList.toggle('visible', count > 0);
    }
    
    // Floating cart bubble
    const floatCart = document.querySelector('.floating-cart');
    if (floatCart) {
      const floatBadge = floatCart.querySelector('.floating-cart-badge');
      const lastCount = floatBadge ? parseInt(floatBadge.dataset.lastCount || 0) : 0;
      
      if (floatBadge) {
        floatBadge.textContent = count;
        floatBadge.dataset.lastCount = count;
      }
      
      const isVisible = count > 0;
      const wasVisible = floatCart.classList.contains('visible');
      
      floatCart.classList.toggle('visible', isVisible);
      
      // Trigger pop animation if count increases and it's visible
      if (isVisible && (!wasVisible || count > lastCount)) {
        floatCart.classList.remove('pop');
        void floatCart.offsetWidth; // force reflow
        floatCart.classList.add('pop');
      }
    }
  },

  dispatchChange() {
    window.dispatchEvent(new CustomEvent('cartUpdated', { detail: { cart: this.get() } }));
  }
};

/* ================================================================
   FORMAT CURRENCY
   ================================================================ */
function formatPrice(amount) {
  return new Intl.NumberFormat('fr-GA', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0,
  }).format(amount);
}

/* ================================================================
   TOAST NOTIFICATIONS
   ================================================================ */
function showToast(message, type = 'info', duration = 3500) {
  const icons = {
    success: '<i class="fa-solid fa-circle-check"></i>',
    error: '<i class="fa-solid fa-circle-xmark"></i>',
    info: '<i class="fa-solid fa-circle-info"></i>',
    warning: '<i class="fa-solid fa-triangle-exclamation"></i>'
  };

  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[type] || icons.info}</span>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('hiding');
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

/* ================================================================
   FETCH API WRAPPER
   ================================================================ */
async function apiFetch(endpoint, options = {}) {
  const url = CONFIG.API_BASE + endpoint;
  const defaults = {
    headers: { 'Content-Type': 'application/json' },
  };
  try {
    const response = await fetch(url, { ...defaults, ...options });
    const data = await response.json();
    return { ok: response.ok, status: response.status, data };
  } catch (err) {
    console.error('API error:', err);
    return { ok: false, status: 0, data: null, error: err };
  }
}

/* ================================================================
   SCROLL ANIMATIONS (Intersection Observer)
   ================================================================ */
function initRevealAnimations() {
  const elements = document.querySelectorAll('.reveal');
  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -50px 0px' });

  elements.forEach(el => observer.observe(el));
}

/* ================================================================
   NAVBAR BEHAVIOR
   ================================================================ */
function initNavbar() {
  const navbar = document.querySelector('.navbar');
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');

  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
  }

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      hamburger.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
      if (!navbar.contains(e.target)) {
        navLinks.classList.remove('open');
        hamburger.classList.remove('active');
      }
    });
  }

  // Active link
  const currentPath = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPath)) {
      link.classList.add('active');
    }
  });
}

/* ================================================================
   HERO PARTICLES
   ================================================================ */
function initParticles() {
  const container = document.querySelector('.hero-particles');
  if (!container) return;

  const positions = [
    { x: 15, y: 20, delay: 0 }, { x: 80, y: 10, delay: 1.5 },
    { x: 60, y: 70, delay: 3 }, { x: 30, y: 80, delay: 0.5 },
    { x: 90, y: 50, delay: 2 }, { x: 10, y: 55, delay: 4 },
  ];

  positions.forEach(({ x, y, delay }) => {
    const particle = document.createElement('div');
    particle.className = 'hero-particle';
    particle.style.cssText = `left:${x}%;top:${y}%;animation-delay:${delay}s;`;
    container.appendChild(particle);
  });
}

/* ================================================================
   MENU PAGE
   ================================================================ */
function initMenuPage() {
  const restaurantId = new URLSearchParams(window.location.search).get('id_restaurant') || '1';
  loadMenuData(restaurantId);

  // Category navigation
  document.addEventListener('click', (e) => {
    const pill = e.target.closest('.menu-category-pill');
    if (!pill) return;
    document.querySelectorAll('.menu-category-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    const targetId = pill.getAttribute('data-category');
    const targetSection = document.getElementById('cat-' + targetId);
    if (targetSection) {
      const offset = 130;
      const top = targetSection.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    }
  });
}

async function loadMenuData(restaurantId) {
  const menuContainer = document.getElementById('menu-container');
  if (!menuContainer) return;

  menuContainer.innerHTML = '<div class="loader"></div>';

  const res = await apiFetch(`/catalogue/plats.php?id_restaurant=${restaurantId}`);
  if (!res.ok || !res.data || !res.data.records) {
    menuContainer.innerHTML = '<p class="text-muted" style="text-align:center;padding:3rem">Aucun plat disponible pour ce restaurant.</p>';
    return;
  }

  const plats = res.data.records;
  const categories = {};
  plats.forEach(plat => {
    const cat = plat.categorie_nom || 'Menu';
    if (!categories[cat]) categories[cat] = [];
    categories[cat].push(plat);
  });

  // Build category pills
  const pillsContainer = document.getElementById('category-pills');
  if (pillsContainer) {
    pillsContainer.innerHTML = Object.keys(categories).map((cat, i) =>
      `<button class="menu-category-pill ${i === 0 ? 'active' : ''}" data-category="${i}">${cat}</button>`
    ).join('');
  }

  // Build menu sections
  menuContainer.innerHTML = Object.entries(categories).map(([cat, plats], i) => `
    <div class="category-section reveal" id="cat-${i}">
      <h2 class="category-title">${cat}</h2>
      <div class="plats-grid">
        ${plats.map(plat => renderPlatCard(plat)).join('')}
      </div>
    </div>
  `).join('');

  initRevealAnimations();
  attachCartListeners();
}

function renderPlatCard(plat) {
  const emoji = getPlatEmoji(plat.nom, plat.categorie_nom);
  return `
    <div class="plat-card reveal" data-plat-id="${plat.id_plat}">
      <div class="plat-card-img">
        ${plat.photo 
          ? `<img src="${plat.photo}" alt="${escAttr(plat.nom)}" loading="lazy">` 
          : `<span>${emoji}</span>`
        }
      </div>
      <div class="plat-card-body">
        <h4>${escHtml(plat.nom)}</h4>
        <p class="plat-card-desc">${escHtml(plat.description || '')}</p>
        <div class="plat-card-footer">
          <div class="plat-price">
            ${formatPrice(plat.prix)}
          </div>
          <button class="add-to-cart-btn" title="Ajouter au panier"
            data-id="${plat.id_plat}"
            data-nom="${escAttr(plat.nom)}"
            data-prix="${plat.prix}"
            data-photo="${plat.photo || ''}"
            data-restaurant="1"
            data-restaurant-nom="GRS Délices"
          >+</button>
        </div>
      </div>
    </div>
  `;
}

function getPlatEmoji(nom, cat) {
  nom = (nom || '').toLowerCase();
  cat = (cat || '').toLowerCase();
  if (nom.includes('poulet') || nom.includes('viande')) return '<i class="fa-solid fa-drumstick-bite"></i>';
  if (nom.includes('poisson') || nom.includes('tilapia') || nom.includes('capitaine')) return '<i class="fa-solid fa-fish"></i>';
  if (nom.includes('crevette') || nom.includes('fruit de mer')) return '<i class="fa-solid fa-shrimp"></i>';
  if (nom.includes('salade')) return '<i class="fa-solid fa-leaf"></i>';
  if (nom.includes('soupe') || nom.includes('bouillon')) return '<i class="fa-solid fa-bowl-food"></i>';
  if (nom.includes('riz')) return '<i class="fa-solid fa-bowl-rice"></i>';
  if (nom.includes('grillade') || nom.includes('bbq')) return '<i class="fa-solid fa-fire"></i>';
  if (nom.includes('tarte') || nom.includes('gâteau') || nom.includes('fondant')) return '<i class="fa-solid fa-cake-candles"></i>';
  if (nom.includes('fruit')) return '<i class="fa-solid fa-apple-whole"></i>';
  if (nom.includes('boisson') || nom.includes('jus') || nom.includes('eau')) return '<i class="fa-solid fa-bottle-water"></i>';
  if (nom.includes('bière')) return '<i class="fa-solid fa-beer-mug-empty"></i>';
  if (cat.includes('dessert')) return '<i class="fa-solid fa-ice-cream"></i>';
  if (cat.includes('boisson')) return '<i class="fa-solid fa-wine-glass"></i>';
  if (cat.includes('entrée')) return '<i class="fa-solid fa-bowl-food"></i>';
  return '<i class="fa-solid fa-utensils"></i>';
}

function attachCartListeners() {
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      Cart.add({
        id_plat: parseInt(this.dataset.id),
        nom: this.dataset.nom,
        prix: parseFloat(this.dataset.prix),
        photo: this.dataset.photo || null,
        id_restaurant: 1,
        restaurant_nom: 'GRS Délices',
      });

      // Animation feedback
      this.textContent = '✓';
      this.style.background = '#27AE60';
      setTimeout(() => {
        this.textContent = '+';
        this.style.background = '';
      }, 1200);
    });
  });
}

/* ================================================================
   PANIER PAGE
   ================================================================ */
function initCartPage() {
  renderCartPage();
  window.addEventListener('cartUpdated', renderCartPage);
}

function renderCartPage() {
  const container = document.getElementById('cart-items');
  const emptyMsg = document.getElementById('cart-empty');
  const cartSection = document.getElementById('cart-section');
  if (!container) return;

  const items = Cart.get();

  if (items.length === 0) {
    if (emptyMsg) emptyMsg.style.display = 'block';
    if (cartSection) cartSection.style.display = 'none';
    return;
  }

  if (emptyMsg) emptyMsg.style.display = 'none';
  if (cartSection) cartSection.style.display = 'block';

  container.innerHTML = items.map(item => `
    <div class="cart-item" data-id="${item.id_plat}">
      <div class="cart-item-img">
        ${item.photo 
          ? `<img src="${item.photo}" alt="${escAttr(item.nom)}">` 
          : `<span style="font-size:2rem">${getPlatEmoji(item.nom, '')}</span>`
        }
      </div>
      <div class="cart-item-info">
        <h4>${escHtml(item.nom)}</h4>
        <div class="price">${formatPrice(item.prix)}</div>
      </div>
      <div class="cart-item-qty">
        <button class="qty-btn" onclick="Cart.updateQty(${item.id_plat}, ${item.quantite - 1})">−</button>
        <span class="qty-display">${item.quantite}</span>
        <button class="qty-btn" onclick="Cart.updateQty(${item.id_plat}, ${item.quantite + 1})">+</button>
      </div>
      <div style="min-width:90px;text-align:right;font-weight:700;color:var(--primary)">${formatPrice(item.prix * item.quantite)}</div>
      <button class="cart-item-remove" onclick="Cart.remove(${item.id_plat})" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button>
    </div>
  `).join('');

  // Update summary
  const livraison = 2000;
  const sous_total = Cart.getTotal();
  const total = sous_total + livraison;

  const el = (id) => document.getElementById(id);
  if (el('summary-subtotal')) el('summary-subtotal').textContent = formatPrice(sous_total);
  if (el('summary-livraison')) el('summary-livraison').textContent = formatPrice(livraison);
  if (el('summary-total'))    el('summary-total').textContent    = formatPrice(total);
  if (el('checkout-btn')) {
    el('checkout-btn').href = '/appli_commande/Frontend/checkout.php';
  }
}

/* ================================================================
   CHECKOUT PAGE
   ================================================================ */
function initCheckoutPage() {
  renderCheckoutSummary();
  const form = document.getElementById('checkout-form');
  if (form) {
    form.addEventListener('submit', handleCheckout);
    form.querySelectorAll('input[name="type_livraison"]').forEach(radio => {
      radio.addEventListener('change', renderCheckoutSummary);
    });
  }
}

function renderCheckoutSummary() {
  const items = Cart.get();
  const summaryEl = document.getElementById('checkout-items');
  if (!summaryEl) return;

  const type = document.querySelector('input[name="type_livraison"]:checked')?.value || 'livraison';
  const livraison = (type === 'retrait') ? 0 : 2000;
  const sous_total = Cart.getTotal();

  summaryEl.innerHTML = items.map(item => `
    <div class="summary-row">
      <span>${escHtml(item.nom)} × ${item.quantite}</span>
      <span>${formatPrice(item.prix * item.quantite)}</span>
    </div>
  `).join('') + `
    <div class="summary-row" style="border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
      <span class="text-dim">Sous-total</span>
      <span>${formatPrice(sous_total)}</span>
    </div>
    <div class="summary-row">
      <span class="text-dim">Livraison</span>
      <span>${formatPrice(livraison)}</span>
    </div>
  `;

  const totalEl = document.getElementById('checkout-total');
  if (totalEl) totalEl.textContent = formatPrice(sous_total + livraison);
}

async function handleCheckout(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('[type="submit"]');
  const items = Cart.get();

  if (items.length === 0) {
    showToast('Votre panier est vide', 'error');
    return;
  }

  const userId = document.querySelector('meta[name="user-id"]')?.content;
  if (!userId) {
    window.location.href = '/appli_commande/Frontend/login.php?redirect=checkout.php';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Traitement en cours...';

  const type = form.type_livraison?.value || 'livraison';
  const isRetrait = (type === 'retrait');
  const livraisonFee = isRetrait ? 0 : 2000;

  const payload = {
    id_utilisateur: parseInt(userId),
    id_restaurant: Cart.getRestaurantId(),
    montant_total: Cart.getTotal() + livraisonFee,
    type_livraison: type,
    adresse_livraison: isRetrait ? 'Retrait en restaurant' : (form.adresse_livraison?.value || ''),
    notes: form.notes?.value || '',
    lignes_panier: items.map(item => ({
      id_plat: item.id_plat,
      quantite: item.quantite,
      prix_unitaire: item.prix,
    }))
  };

  const res = await apiFetch('/commandes/creer.php', {
    method: 'POST',
    body: JSON.stringify(payload)
  });

  if (res.ok && res.data) {
    Cart.clear();
    showToast('Commande passée avec succès !', 'success', 5000);
    setTimeout(() => {
      window.location.href = '/appli_commande/Frontend/profil.php?success=1';
    }, 1800);
  } else {
    showToast(res.data?.message || 'Erreur lors de la commande', 'error');
    btn.disabled = false;
    btn.textContent = 'Confirmer la commande';
  }
}

/* ================================================================
   RESTAURANTS PAGE
   ================================================================ */
async function loadRestaurants(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = '<div class="loader"></div>';

  const res = await apiFetch('/catalogue/restaurants.php');
  if (!res.ok || !res.data?.records) {
    container.innerHTML = '<p class="text-muted" style="text-align:center;padding:3rem">Aucun restaurant disponible.</p>';
    return;
  }

  container.innerHTML = `
    <div class="grid-3" style="margin-top:0">
      ${res.data.records.map(r => renderRestaurantCard(r)).join('')}
    </div>
  `;
  initRevealAnimations();
}

function renderRestaurantCard(r) {
  return `
    <div class="restaurant-card reveal">
      <div class="restaurant-card-img">
        <div class="restaurant-card-img-placeholder"><i class="fa-solid fa-utensils"></i></div>
        ${r.actif ? '<span class="restaurant-card-badge"><i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Ouvert</span>' : ''}
      </div>
      <div class="restaurant-card-body">
        <h3>${escHtml(r.nom)}</h3>
        <div class="restaurant-card-meta">
          <span><i class="fa-solid fa-location-dot text-primary" style="margin-right: 6px;"></i> ${escHtml(r.adresse || '')}</span>
          <span><i class="fa-solid fa-city text-primary" style="margin-right: 6px;"></i> ${escHtml(r.ville || 'Libreville')}</span>
          ${r.telephone ? `<span><i class="fa-solid fa-phone text-primary" style="margin-right: 6px;"></i> ${escHtml(r.telephone)}</span>` : ''}
        </div>
        <a href="/appli_commande/Frontend/menu.php?id_restaurant=${r.id_restaurant}" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-utensils"></i> Voir le menu
        </a>
      </div>
    </div>
  `;
}

/* ================================================================
   ADMIN HELPERS
   ================================================================ */
async function loadAdminStats() {
  const res = await apiFetch('/admin/stats.php');
  if (!res.ok || !res.data || !res.data.stats) return;
  const stats = res.data.stats;
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('stat-restaurants', stats.total_restaurants ?? 0);
  set('stat-commandes',   stats.total_orders ?? 0);
  set('stat-utilisateurs', stats.total_clients ?? 0);
  set('stat-revenus',     formatPrice(stats.total_revenue ?? 0));
}

async function loadAdminOrders() {
  const container = document.getElementById('orders-table-body');
  if (!container) return;

  const res = await apiFetch('/admin/commandes.php');
  if (!res.ok || !res.data?.records) {
    container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">Aucune commande trouvée.</td></tr>';
    return;
  }

  container.innerHTML = res.data.records.map(c => `
    <tr>
      <td><strong>#${c.id_commande}</strong></td>
      <td>${escHtml(c.utilisateur_nom || 'N/A')}</td>
      <td>${escHtml(c.restaurant_nom || 'N/A')}</td>
      <td>${formatDate(c.date_commande)}</td>
      <td><strong style="color:var(--primary)">${formatPrice(c.montant_total)}</strong></td>
      <td><span class="order-status status-${c.statut}">${c.statut}</span></td>
      <td>
        <select class="form-control" style="padding:0.35rem;font-size:0.8rem" onchange="updateOrderStatus(${c.id_commande}, this.value)">
          ${['en_attente','confirmée','en_préparation','en_livraison','livrée','annulée'].map(s =>
            `<option value="${s}" ${c.statut === s ? 'selected' : ''}>${s}</option>`
          ).join('')}
        </select>
      </td>
    </tr>
  `).join('');
}

async function updateOrderStatus(id, statut) {
  const res = await apiFetch('/admin/update_statut.php', {
    method: 'POST',
    body: JSON.stringify({ id_commande: id, statut })
  });
  if (res.ok) showToast('Statut mis à jour', 'success');
  else showToast('Erreur lors de la mise à jour', 'error');
}

/* ================================================================
   UTILITIES
   ================================================================ */
function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function escAttr(str) {
  if (!str) return '';
  return String(str).replace(/"/g, '&quot;');
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-GA', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ================================================================
   THEME TOGGLE
   ================================================================ */
function initThemeToggle() {
  const toggleBtns = document.querySelectorAll('.theme-toggle');
  if (toggleBtns.length === 0) return;

  function updateIcons() {
    const isLight = document.documentElement.classList.contains('light-theme');
    toggleBtns.forEach(btn => {
      const darkIcon = btn.querySelector('.dark-icon');
      const lightIcon = btn.querySelector('.light-icon');
      if (isLight) {
        if (darkIcon) darkIcon.style.display = 'none';
        if (lightIcon) lightIcon.style.display = 'inline-block';
      } else {
        if (darkIcon) darkIcon.style.display = 'inline-block';
        if (lightIcon) lightIcon.style.display = 'none';
      }
    });
  }

  // Initial update
  updateIcons();

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      document.documentElement.classList.toggle('light-theme');
      if (document.documentElement.classList.contains('light-theme')) {
        localStorage.setItem('theme', 'light');
      } else {
        localStorage.setItem('theme', 'dark');
      }
      updateIcons();
    });
  });
}

/* ================================================================
   INIT & FLOATING CART BUBBLE (Phase 11)
   ================================================================ */
function initFloatingCart() {
  // Check if we are on an admin page (exclude admin directory)
  if (window.location.pathname.includes('/admin/')) return;
  
  // Check if it already exists
  if (document.querySelector('.floating-cart')) return;

  const btn = document.createElement('a');
  btn.href = '/appli_commande/Frontend/panier.php';
  btn.className = 'floating-cart';
  btn.title = 'Voir mon panier';
  btn.innerHTML = `
    <i class="fa-solid fa-cart-shopping"></i>
    <span class="floating-cart-badge">0</span>
  `;
  document.body.appendChild(btn);
}

document.addEventListener('DOMContentLoaded', () => {
  initThemeToggle();
  initNavbar();
  initRevealAnimations();
  initParticles();
  initFloatingCart();

  // --- Guest cart expiry check (10 minutes) ---
  const isGuest = !document.querySelector('meta[name="user-id"]')?.content;
  if (isGuest) {
    const ts = parseInt(localStorage.getItem(CONFIG.CART_TS_KEY) || '0');
    const cartItems = JSON.parse(localStorage.getItem(CONFIG.CART_KEY) || '[]');
    if (ts > 0 && cartItems.length > 0) {
      const elapsed = Date.now() - ts;
      if (elapsed > CONFIG.CART_EXPIRY_MS) {
        Cart.clear();
        showToast(
          'Votre panier a été réinitialisé après 10 minutes d\'inactivité. Connectez-vous pour garder votre panier.',
          'warning',
          6000
        );
      } else {
        // Schedule the auto-clear for the remaining time
        const remaining = CONFIG.CART_EXPIRY_MS - elapsed;
        setTimeout(() => {
          const stillGuest = !document.querySelector('meta[name="user-id"]')?.content;
          if (stillGuest && Cart.get().length > 0) {
            Cart.clear();
            showToast(
              'Votre panier a été réinitialisé après 10 minutes d\'inactivité.',
              'warning',
              6000
            );
          }
        }, remaining);
      }
    }
  }
  // --- End guest cart expiry ---

  Cart.updateBadge();

  const page = window.location.pathname.split('/').pop();
  if (page === 'menu.php')     initMenuPage();
  if (page === 'panier.php')   initCartPage();
  if (page === 'checkout.php') initCheckoutPage();
  if (page === 'index.php' && document.getElementById('admin-stats')) {
    loadAdminStats();
    loadAdminOrders();
  }
});

// Exposer les objets et fonctions globaux pour les gestionnaires d'événements HTML inline
window.Cart = Cart;
window.updateOrderStatus = updateOrderStatus;
