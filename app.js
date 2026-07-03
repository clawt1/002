const KEY = "olab004_tablet_local";

const seed = {
  role: null,
  selectedClientId: "c1",
  config: {
    pins: { admin: "0420", staff: "2024" },
    theme: "#79c93c",
    shopName: "O'LAB CBD",
    currency: "€",
    sounds: true,
    ranks: [
      { name: "Bronze", min: 0, discount: 0, color: "#cd7f32" },
      { name: "Silver", min: 500, discount: 5, color: "#c0c0c0" },
      { name: "Gold", min: 1500, discount: 10, color: "#ffd700" },
      { name: "Galaxy", min: 5000, discount: 15, color: "#e0f0ff" }
    ]
  },
  clients: [
    { id: "c1", name: "Martin Lemoine", phone: "06 12 34 56 78", email: "martin@email.fr", stamps: 7, ltv: 120, notes: "", dob: "1990-05-15" },
    { id: "c2", name: "Nadia Résines", phone: "06 01 01 01 01", email: "nadia@olab.local", stamps: 9, ltv: 1650, notes: "Préférence résines.", dob: "1985-11-20" },
    { id: "c3", name: "Sofia Fleurs", phone: "06 03 03 03 03", email: "sofia@olab.local", stamps: 4, ltv: 600, notes: "Nouveautés fleurs.", dob: "1995-02-10" },
  ],
  products: [
    {
      id: "p1", name: "Amnesia CBD", category: "Fleurs", unit: "g", tva: 20,
      price: 10, stock: 76.5, alert: 15,
      img: "https://www.olabcbd.fr/wp-content/uploads/2024/01/Screenshot_20230422-175952_Chrome-300x300.jpg",
      variants: [
        { id: "v1", label: "1g", price: 10, weight: 1 },
        { id: "v2", label: "5g", price: 45, weight: 5 },
        { id: "v3", label: "10g", price: 80, weight: 10 }
      ],
      batches: [
        { id: "B001", qty: 40, expiry: Date.now() + 86400000 * 180 },
        { id: "B002", qty: 36.5, expiry: Date.now() + 86400000 * 240 }
      ]
    },
    {
      id: "p2", name: "3x Filtré Lemon", category: "Résines", unit: "g", tva: 20,
      price: 12, stock: 45, alert: 12,
      img: "https://www.olabcbd.fr/wp-content/uploads/2024/01/Screenshot_20230422-140841_Chrome-2-300x300.jpg",
      variants: []
    },
    {
      id: "p3", name: "3x Filtré Critical", category: "Résines", unit: "g", tva: 20,
      price: 12, stock: 32, alert: 10,
      img: "https://www.olabcbd.fr/wp-content/uploads/20250127_155410_Chrom-300x300.jpg",
      variants: []
    },
    {
      id: "p5", name: "Huile Nateava 20%", category: "Huiles", unit: "pièce", tva: 5.5,
      price: 35, stock: 2, alert: 3, variants: []
    },
  ],
  sales: [],
  expenses: [
    { id: "e1", label: "Loyer Mai", amount: 1200, category: "Fixe", ts: Date.now() - 86400000 * 5 },
    { id: "e2", label: "Achat Stock Fleurs", amount: 450, category: "Stock", ts: Date.now() - 86400000 * 2 }
  ],
  history: [],
  pendingSales: []
};

let db = JSON.parse(localStorage.getItem(KEY) || "null") || structuredClone(seed);

if (!db.config) db.config = structuredClone(seed.config);
if (!db.expenses) db.expenses = [];
if (!db.pendingSales) db.pendingSales = [];
if (!db.config.theme) db.config.theme = "#79c93c";

let selectedRole = "admin";
let currentScreen = "home";
let sale = {
  id: crypto.randomUUID(),
  step: 0,
  clientId: null,
  items: [],
  curProd: db.products[0]?.id || "p1",
  curVariant: null,
  curQty: 1,
  useReward: false,
  manualDiscountType: "%",
  manualDiscountValue: 0
};

const $ = (q) => document.querySelector(q);
const $$ = (q) => [...document.querySelectorAll(q)];
const save = () => {
  localStorage.setItem(KEY, JSON.stringify(db));
  applyTheme();
};
const product = (id) => db.products.find((p) => p.id === id);
const client = (id) => db.clients.find((c) => c.id === id);
const getRank = (c) => {
  if (!c) return db.config.ranks[0];
  const ltv = c.ltv || 0;
  return [...db.config.ranks].reverse().find(r => ltv >= r.min) || db.config.ranks[0];
};
const currentClient = () => client(db.selectedClientId) || db.clients[0];
const esc = (s) => (s == null ? "" : String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"));

const initials = (name) => {
  if (!name || typeof name !== "string") return "??";
  const parts = name.trim().split(/\s+/);
  if (!parts[0]) return "??";
  return parts.map((p) => p[0]).join("").slice(0, 2).toUpperCase();
};

const fmt = (ts) => {
  if (!ts) return "Date inconnue";
  const d = new Date(ts);
  return d.toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" });
};

const isToday = (ts) => {
  const d = new Date(ts);
  const now = new Date();
  return d.getDate() === now.getDate() && d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
};

function applyTheme() {
  const root = document.documentElement;
  const color = db.config.theme || "#79c93c";
  root.style.setProperty('--green', color);
  root.style.setProperty('--green2', adjustColor(color, -30));
}

function adjustColor(hex, amt) {
  let usePound = false;
  if (hex[0] === "#") { hex = hex.slice(1); usePound = true; }
  let num = parseInt(hex, 16);
  let r = (num >> 16) + amt;
  if (r > 255) r = 255; else if (r < 0) r = 0;
  let g = ((num >> 8) & 0x00FF) + amt;
  if (g > 255) g = 255; else if (g < 0) g = 0;
  let b = (num & 0x0000FF) + amt;
  if (b > 255) b = 255; else if (b < 0) b = 0;
  return (usePound ? "#" : "") + (b | (g << 8) | (r << 16)).toString(16).padStart(6, '0');
}

function playSound(type) {
  if (navigator.vibrate) {
    if (type === 'success') navigator.vibrate(20);
    else if (type === 'error') navigator.vibrate([50, 100, 50]);
  }
  if (!db.config.sounds) return;
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    if (type === 'success') {
      osc.frequency.setValueAtTime(523.25, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(1046.50, ctx.currentTime + 0.1);
      gain.gain.setValueAtTime(0.1, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
    } else if (type === 'error') {
      osc.frequency.setValueAtTime(150, ctx.currentTime);
      gain.gain.setValueAtTime(0.1, ctx.currentTime);
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3);
    } else {
      osc.frequency.setValueAtTime(800, ctx.currentTime);
      gain.gain.setValueAtTime(0.05, ctx.currentTime);
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.05);
    }
    osc.start();
    osc.stop(ctx.currentTime + 0.3);
  } catch(e) {}
}

function toast(msg, type = "success") {
  const t = $("#toast");
  t.textContent = msg;
  t.className = `toast ${type === "error" ? "error" : ""}`;
  t.classList.remove("hidden");
  playSound(type);
  setTimeout(() => t.classList.add("hidden"), 2200);
}

function showModal(html) {
  const m = $("#modal");
  m.innerHTML = `<div class="modal-content">${html}</div>`;
  m.classList.remove("hidden");
}

function hideModal() {
  $("#modal").classList.add("hidden");
}

function confirmAction(msg, onConfirm) {
  showModal(`
    <h2>Confirmation</h2>
    <p style="margin: 20px 0; color: var(--muted);">${msg}</p>
    <div class="big-actions">
      <button class="ghost" onclick="hideModal()">Annuler</button>
      <button class="danger" id="confirmBtn">Confirmer</button>
    </div>
  `);
  $("#confirmBtn").onclick = () => { onConfirm(); hideModal(); };
}

function metrics() {
  const todaySales = db.sales.filter(s => isToday(s.ts));
  const todayExpenses = db.expenses.filter(e => isToday(e.ts));
  const rev = todaySales.reduce((sum, s) => sum + (s.total || 0), 0);
  const exp = todayExpenses.reduce((sum, e) => sum + e.amount, 0);
  return {
    clients: db.clients.length,
    sales: db.sales.length,
    qty: db.sales.reduce((s, x) => s + (x.items?.reduce((sum, it) => sum + it.qty, 0) || 0), 0),
    stamps: db.clients.reduce((s, c) => s + c.stamps, 0),
    alerts: db.products.filter((p) => p.stock <= p.alert).length,
    todaySales: todaySales.length,
    todayRevenue: rev,
    todayProfit: rev - exp
  };
}

let currentPin = "";
function updatePinUI() {
  const dots = $$("#pinDots span");
  dots.forEach((dot, i) => dot.classList.toggle("filled", i < currentPin.length));
  if (currentPin.length === 4) {
    setTimeout(unlock, 100);
  }
}

function unlock() {
  if (currentPin !== db.config.pins[selectedRole]) {
    toast("PIN incorrect", "error");
    currentPin = "";
    updatePinUI();
    return;
  }
  db.role = selectedRole;
  currentPin = "";
  updatePinUI();
  save();
  $("#lock").classList.add("hidden");
  $("#workspace").classList.remove("hidden");
  boot();
}

function lock() {
  db.role = null;
  save();
  $("#workspace").classList.add("hidden");
  $("#lock").classList.remove("hidden");
}

function boot() {
  applyTheme();
  $("#roleLabel").textContent = db.role === "admin" ? "Administrateur" : "Staff";
  renderNav();
  setScreen("home");
}

function renderNav() {
  const items = [
    ["home", "Accueil"],
    ["sell", "Vente"],
    ["client", "Client"],
    ["clients", "Clients"],
    ["stock", "Stock"],
  ];
  if (db.role === "admin") items.push(["admin", "Admin"]);
  const icons = { home: 'home', sell: 'shopping-cart', client: 'user', clients: 'users', stock: 'package', admin: 'settings' };
  $("#bottomNav").innerHTML = items.map(([id, label]) => `
    <button data-screen="${id}">
      <i data-lucide="${icons[id]}" style="width:20px;height:20px"></i>
      <span>${label}</span>
    </button>`).join("");
  $$("[data-screen]").forEach((b) => b.addEventListener("click", () => setScreen(b.dataset.screen)));
}

function setScreen(screen) {
  currentScreen = screen;
  $$(".screen").forEach((el) => el.classList.toggle("active", el.id === screen));
  $$("#bottomNav button").forEach((el) => el.classList.toggle("active", el.dataset.screen === screen));
  $("#screenTitle").textContent = { home: "Tableau de bord", sell: "Vente Pro", client: "Fiche client", clients: "Gestion Clients", stock: "Inventaire", admin: "Paramètres Système" }[screen];
  render();
}

function render() {
  if (currentScreen === "home") renderHome();
  if (currentScreen === "sell") renderSell();
  if (currentScreen === "client") renderClient();
  if (currentScreen === "clients") renderClients();
  if (currentScreen === "stock") renderStock();
  if (currentScreen === "admin") renderAdmin();
  lucide.createIcons();
}

function renderHome() {
  const m = metrics();
  const topProducts = getTopProducts(3);

  const c = currentClient();
  const rank = getRank(c);

  $("#home").innerHTML = `
    <div class="grid cols-2">
      <section class="client-card" style="border-color:${rank.color}44; box-shadow: 0 0 40px ${rank.color}11">
        ${clientCard(c)}
      </section>
      <section class="grid cards">
        <div class="tile" style="background:linear-gradient(135deg, rgba(121,201,60,0.2), transparent)">
          <i data-lucide="trending-up" class="icon"></i>
          <strong>${m.todayRevenue.toFixed(2)}€</strong>
          <span>Revenus Jour</span>
        </div>
        <div class="tile" style="background:linear-gradient(135deg, rgba(224,82,75,0.1), transparent)">
          <i data-lucide="pie-chart" class="icon" style="color:var(--red)"></i>
          <strong>${m.todayProfit.toFixed(2)}€</strong>
          <span>Profit Net Jour</span>
        </div>
        ${tile("shopping-cart", "Vente", "Démarrer un panier", "sell")}
        ${tile("package", "Stocks", `${m.alerts} alertes`, "stock")}
      </section>
    </div>
    <div class="grid cards" style="margin-top:14px">
      <div class="panel card-chart" style="grid-column: span 2">
        <h3 style="margin-bottom:14px">Performance 7 Jours</h3>
        <canvas id="salesChart" height="200"></canvas>
      </div>
        <div class="panel">
          <h2 style="font-size:18px; margin-bottom:10px">Heatmap Horaire</h2>
          <div id="heatmap" style="display:grid; grid-template-columns: repeat(24, 1fr); gap:2px; height:60px; margin-top:20px"></div>
          <div style="display:flex; justify-content:space-between; font-size:10px; opacity:0.5; margin-top:4px">
            <span>00h</span><span>12h</span><span>23h</span>
          </div>
        </div>
      <div class="panel">
        <h2 style="font-size:18px; margin-bottom:10px">Produits Phares</h2>
        <div class="list">
          ${topProducts.map(p => `
            <div class="row-card" style="min-height:60px; padding:10px 16px">
              <b style="font-size:14px">${esc(p.name)}</b>
              <span class="pill">${p.count}</span>
            </div>
          `).join("") || "<p>Aucune vente.</p>"}
        </div>
      </div>
    </div>`;
  bindTiles();
  bindClientActions();
  initHomeCharts();
  renderHeatmap();
}

function renderHeatmap() {
  const container = $("#heatmap");
  if (!container) return;
  const hours = Array(24).fill(0);
  db.sales.forEach(s => {
    const hour = new Date(s.ts).getHours();
    hours[hour]++;
  });
  const max = Math.max(...hours) || 1;
  container.innerHTML = hours.map(h => `
    <div style="background:var(--green); opacity:${0.1 + (h/max)*0.9}; border-radius:2px" title="${h} ventes"></div>
  `).join("");
}

function initHomeCharts() {
  const ctx = $("#salesChart")?.getContext("2d");
  if (!ctx) return;
  const salesData = getSalesLast7Days();
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: salesData.map(d => d.label),
      datasets: [
        {
          label: 'CA (€)',
          data: salesData.map(d => d.revenue),
          backgroundColor: db.config.theme,
          borderRadius: 8
        },
        {
          label: 'Profit (€)',
          data: salesData.map(d => d.profit),
          type: 'line',
          borderColor: '#f5c958',
          borderWidth: 3,
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } },
        x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)' } }
      }
    }
  });
}

function getSalesLast7Days() {
  const results = [];
  const today = new Date();
  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(today.getDate() - i);
    const dateStr = d.toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit" });
    const daySales = db.sales.filter(s => {
      const sd = new Date(s.ts);
      return sd.getDate() === d.getDate() && sd.getMonth() === d.getMonth() && sd.getFullYear() === d.getFullYear();
    });
    const dayExp = db.expenses.filter(e => {
      const ed = new Date(e.ts);
      return ed.getDate() === d.getDate() && ed.getMonth() === d.getMonth() && ed.getFullYear() === d.getFullYear();
    });
    const revenue = daySales.reduce((sum, s) => sum + (s.total || 0), 0);
    const expenses = dayExp.reduce((sum, e) => sum + e.amount, 0);
    results.push({ label: dateStr, revenue, profit: revenue - expenses });
  }
  return results;
}

function getTopProducts(n) {
  const counts = {};
  db.sales.forEach(s => {
    (s.items || []).forEach(it => {
       counts[it.productId] = (counts[it.productId] || 0) + it.qty;
    });
  });
  return Object.entries(counts)
    .sort((a, b) => b[1] - a[1])
    .slice(0, n)
    .map(([id, count]) => ({ name: product(id)?.name || "Inconnu", count }));
}

function tile(icon, title, desc, screen) {
  return `<button class="tile" data-go="${screen}"><i data-lucide="${icon}" class="icon"></i><strong>${title}</strong><span>${desc}</span></button>`;
}

function bindTiles() {
  $$("[data-go]").forEach((b) => b.addEventListener("click", () => setScreen(b.dataset.go)));
}

function clientCard(c) {
  const rank = getRank(c);
  return `
    <div class="client-head">
      <div class="avatar" style="border: 2px solid ${rank.color}">${esc(initials(c.name))}</div>
      <div>
        <h2>${esc(c.name)}</h2>
        <p>${esc(c.phone)} • <span style="color:${rank.color}; font-weight:bold">${rank.name}</span></p>
      </div>
    </div>
    <div style="display:flex; justify-content:space-around; margin:15px 0">
      <div style="text-align:center">
        <div class="stamp-ring"><div><strong>${c.stamps}</strong><span>tampons</span></div></div>
      </div>
      <div style="text-align:center">
        <div class="stamp-ring" style="border-color:${rank.color}"><div><strong style="font-size:14px">${(c.ltv || 0).toFixed(0)}€</strong><span>LTV</span></div></div>
      </div>
    </div>
    <div class="stamp-grid">${Array.from({ length: 10 }, (_, i) => `<button class="stamp ${i < c.stamps ? "done" : ""}" data-stamp="${i + 1}">${i < c.stamps ? "OK" : "+"}</button>`).join("")}</div>
    <div class="big-actions"><button class="primary" data-action="add">+1</button><button class="ghost" data-action="reward">Cadeau</button></div>`;
}

function bindClientActions() {
  $$("[data-action]").forEach((b) => b.addEventListener("click", () => {
    const c = currentClient();
    if (b.dataset.action === "add" && c.stamps < 10) { c.stamps++; addHistory(c.id, "+1", "Tampon ajouté"); }
    if (b.dataset.action === "reward") { c.stamps = 0; addHistory(c.id, "🎁", "Récompense accordée"); }
    save(); toast("Carte mise à jour"); render();
  }));
}

function addHistory(clientId, sign, label) {
  db.history.unshift({ clientId, sign, label, ts: Date.now() });
}

let sellCategory = "Tous";
let sellClientQ = "";

function renderSell() {
  const titles = ["Client", "Produit", "Configuration", "Paiement"];
  const pendingCount = db.pendingSales.length;

  $("#sell").innerHTML = `
    <section class="sale-step">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px">
        <h2 style="margin:0">${titles[sale.step]}</h2>
        <div style="display:flex; gap:8px">
          ${pendingCount > 0 ? `<button class="btn warning" onclick="showPendingSales()" style="padding: 8px 12px; min-height: 44px; background:var(--gold); color:#000; border-radius:10px; font-weight:900">🕒 ${pendingCount}</button>` : ""}
          <button class="btn secondary" onclick="startScanner()" style="padding: 8px 12px; min-height: 44px;"><i data-lucide="scan"></i></button>
          <span class="pill">${sale.items.length} art.</span>
        </div>
      </div>
      ${sale.step === 0 ? renderSellStep0() : ""}
      ${sale.step === 1 ? renderSellStep1() : ""}
      ${sale.step === 2 ? renderSellStep2() : ""}
      ${sale.step === 3 ? renderSellStep3() : ""}
      <div class="big-actions">
        <button class="ghost" id="prev">Retour</button>
        <button class="primary" id="next">${sale.step === 2 ? "Ajouter" : sale.step === 3 ? "Valider" : "Suivant"}</button>
      </div>
    </section>`;

  bindSellEvents();
}

function renderSellStep0() {
  const q = sellClientQ.toLowerCase();
  const filtered = db.clients.filter(c => !q || c.name.toLowerCase().includes(q) || c.phone.includes(q));
  return `
    <input id="sellClientSearch" class="search" placeholder="Nom ou téléphone..." value="${esc(sellClientQ)}">
    <div class="choice-grid">
      <button class="choice ${!sale.clientId ? "active" : ""}" data-client=""><b>Passant</b><span>Vente rapide</span></button>
      ${filtered.map((c) => `<button class="choice ${sale.clientId === c.id ? "active" : ""}" data-client="${c.id}"><b>${esc(c.name)}</b><span>${c.stamps}/10</span></button>`).join("")}
    </div>`;
}

function renderSellStep1() {
  const filtered = sellCategory === "Tous" ? db.products : db.products.filter(p => p.category === sellCategory);
  const recs = getCrossSellRecs(sale.clientId);

  return `
    <div class="cat-filter">
      ${["Tous", "Fleurs", "Résines", "Huiles", "Aliments"].map(c => `<button class="cat-btn ${sellCategory === c ? "active" : ""}" onclick="sellCategory='${c}'; renderSell();">${c}</button>`).join("")}
    </div>
    ${recs.length > 0 ? `
      <div class="panel" style="margin-bottom:14px; background:rgba(121,201,60,0.1); border:1px dashed var(--green)">
        <p style="font-size:12px; margin-bottom:8px; opacity:0.8"><i data-lucide="sparkles"></i> Suggestion Cross-Sell :</p>
        <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:4px">
          ${recs.map(r => `
            <button class="btn ghost" onclick="sale.curProd='${r.id}'; sale.step=2; renderSell();" style="white-space:nowrap; padding:8px 12px">
              ${esc(r.name)} (${r.price}€)
            </button>
          `).join("")}
        </div>
      </div>
    ` : ""}
    <div class="choice-grid">
      ${filtered.map((p) => `
        <button class="choice prod-card ${sale.curProd === p.id ? "active" : ""}" data-product="${p.id}">
          <div class="prod-info"><b>${esc(p.name)}</b><span>${p.price}€ / ${p.unit}</span></div>
        </button>`).join("")}
    </div>`;
}

function getCrossSellRecs(clientId) {
  if (!clientId) return [];
  const clientSales = db.sales.filter(s => s.clientId === clientId);
  if (clientSales.length === 0) return [];

  // Count products bought by this client
  const bought = {};
  clientSales.forEach(s => s.items.forEach(it => bought[it.productId] = (bought[it.productId] || 0) + 1));

  // Find products often bought by OTHERS who bought these products
  const candidates = {};
  db.sales.forEach(s => {
    const hasCommon = s.items.some(it => bought[it.productId]);
    if (hasCommon) {
      s.items.forEach(it => {
        if (!bought[it.productId]) candidates[it.productId] = (candidates[it.productId] || 0) + 1;
      });
    }
  });

  return Object.entries(candidates)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 3)
    .map(([id]) => product(id))
    .filter(p => p && p.stock > 0);
}

function renderSellStep2() {
  const p = product(sale.curProd);
  const variants = p.variants || [];
  return `
    <div style="margin-bottom:20px">
      <h3>${esc(p.name)}</h3>
      ${variants.length > 0 ? `
        <p>Choisir un format :</p>
        <div class="cat-filter" style="margin-top:10px">
          ${variants.map(v => `<button class="cat-btn ${sale.curVariant === v.id ? "active" : ""}" onclick="sale.curVariant='${v.id}'; renderSell();">${v.label} (${v.price}€)</button>`).join("")}
        </div>
      ` : ""}
    </div>
    <div class="qty-display">${sale.curQty}</div>
    <div class="qty-pad">${[1, 2, 5, 10, -1, -5, "reset"].map((q) => `<button data-qty="${q}">${q === "reset" ? "Reset" : q > 0 ? "+" + q : q}</button>`).join("")}</div>`;
}

function renderSellStep3() {
  const subtotal = calculateSubtotal();
  const c = sale.clientId ? client(sale.clientId) : null;
  const rank = getRank(c);
  const rankDisc = (subtotal * (rank.discount / 100));

  const rewardAvailable = c && c.stamps >= 10;
  const disc = (sale.useReward && rewardAvailable) ? 10 : 0;
  const manualDisc = sale.manualDiscountType === "%" ? subtotal * (sale.manualDiscountValue / 100) : sale.manualDiscountValue;
  const total = Math.max(0, subtotal - disc - manualDisc - rankDisc);

  return `
    <div style="display:flex; gap:12px; margin-bottom:14px">
      <button class="btn secondary" style="flex:1; min-height:50px" onclick="putSaleOnHold()">Suspendre</button>
      <button class="btn ghost" style="flex:1; min-height:50px" onclick="openDiscountModal()">Remise</button>
    </div>
    ${rankDisc > 0 ? `
      <div class="panel" style="margin-bottom:14px; background:rgba(255,215,0,0.1); border:1px solid gold; display:flex; justify-content:space-between; align-items:center; min-height:44px">
        <span>Remise Rang <b>${rank.name}</b> (-${rank.discount}%)</span>
        <b style="color:gold">-${rankDisc.toFixed(2)}€</b>
      </div>
    ` : ""}
    ${rewardAvailable ? `
      <button class="panel ${sale.useReward ? 'primary' : 'ghost'}" style="width:100%; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; min-height:60px" onclick="sale.useReward = !sale.useReward; renderSell();">
        <div style="display:flex; align-items:center; gap:12px">
          <i data-lucide="gift"></i>
          <b>Récompense disponible !</b>
        </div>
        <span>${sale.useReward ? '-10.00€' : 'Appliquer -10€'}</span>
      </button>
    ` : ""}
    <div class="list" style="margin-bottom:14px">
      ${sale.items.map((it, idx) => {
        const p = product(it.productId);
        const v = p.variants?.find(x => x.id === it.variantId);
        const pLabel = v ? `${p.name} (${v.label})` : p.name;
        const pPrice = v ? v.price : p.price;
        return `<div class="row-card"><div><b>${esc(pLabel)}</b><span>${it.qty} x ${pPrice}€</span></div><button class="danger" onclick="sale.items.splice(${idx},1); renderSell();" style="min-height:44px; width:44px">×</button></div>`;
      }).join("")}
    </div>
    <div class="panel" style="background:rgba(255,255,255,0.05)">
      <div style="display:flex; justify-content:space-between"><span>A payer</span><b style="font-size:32px; color:var(--green)">${total.toFixed(2)}€</b></div>
    </div>`;
}

function bindSellEvents() {
  const search = $("#sellClientSearch");
  if (search) search.addEventListener("input", (e) => { sellClientQ = e.target.value; renderSell(); });
  $$("[data-client]").forEach(b => b.onclick = () => { sale.clientId = b.dataset.client || null; sale.step = 1; renderSell(); });
  $$("[data-product]").forEach(b => b.onclick = () => {
    sale.curProd = b.dataset.product;
    sale.step = 2;
    sale.curVariant = product(sale.curProd).variants?.[0]?.id || null;
    renderSell();
  });
  $$("[data-qty]").forEach(b => b.onclick = () => {
    const v = b.dataset.qty;
    sale.curQty = v === "reset" ? 1 : Math.max(1, sale.curQty + Number(v));
    renderSell();
  });

  $("#prev").onclick = () => {
    if (sale.step === 1 && sale.items.length > 0) sale.step = 3;
    else sale.step = Math.max(0, sale.step - 1);
    renderSell();
  };

  $("#next").onclick = () => {
    if (sale.step === 2) {
      const p = product(sale.curProd);
      const v = p.variants?.find(x => x.id === sale.curVariant);
      sale.items.push({ productId: p.id, variantId: sale.curVariant, qty: sale.curQty, price: v ? v.price : p.price });
      sale.curQty = 1; // Reset qty for next item
      sale.step = 3;
      renderSell();
    } else if (sale.step === 3) {
      completeSale();
    } else {
      sale.step++;
      renderSell();
    }
  };
}

function calculateSubtotal() { return sale.items.reduce((sum, it) => sum + (it.qty * it.price), 0); }

function putSaleOnHold() {
  db.pendingSales.push(structuredClone(sale));
  sale = { id: crypto.randomUUID(), step: 0, clientId: null, items: [], curProd: db.products[0]?.id || "p1", curVariant: null, curQty: 1, useReward: false, manualDiscountType: "%", manualDiscountValue: 0 };
  save(); setScreen("sell"); toast("Vente suspendue");
}

function showPendingSales() {
  showModal(`
    <h2>Ventes en attente</h2>
    <div class="list" style="margin-top:20px">
      ${db.pendingSales.map((ps, idx) => `
        <div class="row-card">
          <div><b>${ps.clientId ? client(ps.clientId).name : "Passant"}</b><span>${ps.items.length} art.</span></div>
          <div style="display:flex; gap:8px">
            <button class="primary" onclick="resumeSale(${idx})">Reprendre</button>
            <button class="danger" onclick="db.pendingSales.splice(${idx},1); save(); showPendingSales();">×</button>
          </div>
        </div>`).join("")}
    </div>
    <button class="ghost" onclick="hideModal()" style="width:100%; margin-top:14px">Fermer</button>
  `);
}

function resumeSale(idx) {
  sale = db.pendingSales.splice(idx, 1)[0];
  save(); hideModal(); renderSell();
}

function completeSale() {
  if (sale.items.length === 0) return toast("Panier vide", "error");
  const subtotal = calculateSubtotal();

  const c = sale.clientId ? client(sale.clientId) : null;
  const rank = getRank(c);
  const rankDisc = (subtotal * (rank.discount / 100));

  const disc = sale.useReward ? 10 : 0;
  const manualDisc = sale.manualDiscountType === "%" ? subtotal * (sale.manualDiscountValue / 100) : sale.manualDiscountValue;
  const total = Math.max(0, subtotal - disc - manualDisc - rankDisc);

  // TVA breakdown
  const tvaMap = {};
  sale.items.forEach(it => {
    const p = product(it.productId);
    const rate = p.tva || 20;
    const itPrice = it.price * it.qty;
    // Ratio of this item in total (rough estimate for discount distribution)
    const ratio = itPrice / subtotal;
    const itDisc = (disc + manualDisc + rankDisc) * ratio;
    const itNet = itPrice - itDisc;
    const tvaVal = itNet - (itNet / (1 + rate/100));
    tvaMap[rate] = (tvaMap[rate] || 0) + tvaVal;
  });

  const saleRecord = {
    ...structuredClone(sale),
    total,
    ts: Date.now(),
    discount: disc + manualDisc + rankDisc,
    rankDiscount: rankDisc,
    tva: tvaMap
  };

  db.sales.unshift(saleRecord);
  sale.items.forEach(it => {
    const p = product(it.productId);
    const v = p.variants?.find(x => x.id === it.variantId);
    p.stock = Number((p.stock - (v ? v.weight * it.qty : it.qty)).toFixed(2));
  });

  if (sale.clientId) {
    const c = client(sale.clientId);
    if (sale.useReward) c.stamps = Math.max(0, c.stamps - 10);
    else c.stamps = Math.min(10, c.stamps + 1);
    c.ltv = (c.ltv || 0) + total;
  }
  const lastSale = saleRecord;
  sale = { id: crypto.randomUUID(), step: 0, clientId: null, items: [], curProd: db.products[0]?.id || "p1", curVariant: null, curQty: 1, useReward: false, manualDiscountType: "%", manualDiscountValue: 0 };
  save();
  render();
  showReceipt(lastSale);
}

function showReceipt(s) {
  const c = s.clientId ? client(s.clientId) : { name: "Passant" };
  showModal(`
    <div id="receipt" style="text-align:center; font-family:monospace; color:#000; background:#fff; padding:20px; border-radius:4px">
      <h2 style="margin:0">${db.config.shopName}</h2>
      <p style="margin:10px 0">${fmt(s.ts)}</p>
      <div style="border-top:1px dashed #ccc; padding:10px 0; text-align:left">
        ${s.items.map(it => {
          const p = product(it.productId);
          const v = p.variants?.find(x => x.id === it.variantId);
          return `<div style="display:flex; justify-content:space-between"><span>${p.name}${v ? ' ('+v.label+')':''} x${it.qty}</span><span>${(it.qty * it.price).toFixed(2)}€</span></div>`;
        }).join("")}
        ${s.discount > 0 ? `<div style="display:flex; justify-content:space-between; color:#900"><span>REMISE</span><span>-${s.discount.toFixed(2)}€</span></div>` : ""}
      </div>
      <div style="border-top:2px solid #000; padding:10px 0; display:flex; justify-content:space-between; font-weight:bold; font-size:18px">
        <span>TOTAL</span><span>${s.total.toFixed(2)}€</span>
      </div>
      <div class="no-print" style="margin-top:15px"><button class="primary" onclick="hideModal()">Terminer</button></div>
    </div>
  `);
}

function renderClient() {
  const c = currentClient();
  $("#client").innerHTML = `
    <div class="grid cols-2">
      <section class="client-card">${clientCard(c)}</section>
      <section class="panel">
        <h2>Détails Client</h2>
        <div class="list">
          <div class="row-card"><span>Nom</span><b>${esc(c.name)}</b></div>
          <div class="row-card"><span>Anniversaire</span><b>${esc(c.dob || "Non renseigné")}</b></div>
        </div>
        <h3 style="margin-top:20px">Notes</h3>
        <textarea class="search" style="height:100px">${esc(c.notes)}</textarea>
      </section>
    </div>`;
  bindClientActions();
}

function renderClients() {
  const lostThreshold = 30 * 86400000;
  const now = Date.now();
  const lostClients = db.clients.filter(c => {
    const lastSale = db.sales.find(s => s.clientId === c.id);
    return lastSale && (now - lastSale.ts > lostThreshold);
  });

  $("#clients").innerHTML = `
    <section class="panel">
      ${lostClients.length > 0 ? `
        <div class="panel" style="margin-bottom:20px; background:rgba(224,82,75,0.1); border-color:rgba(224,82,75,0.3)">
          <h3 style="margin-top:0; color:var(--red)"><i data-lucide="alert-triangle"></i> CRM : Clients à relancer</h3>
          <div style="display:flex; gap:8px; overflow-x:auto">
            ${lostClients.map(c => `
              <div class="pill crit" style="white-space:nowrap" onclick="db.selectedClientId='${c.id}'; setScreen('client');">
                ${esc(c.name)}
              </div>
            `).join("")}
          </div>
        </div>
      ` : ""}
      <input id="cq" class="search" placeholder="Rechercher un client...">
      <div id="clientRows" class="list"></div>
    </section>`;
  const draw = () => {
    const q = $("#cq").value.toLowerCase();
    const filtered = db.clients.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q));
    $("#clientRows").innerHTML = filtered.map(c => `
      <div class="row-card" onclick="db.selectedClientId='${c.id}'; save(); setScreen('client');">
        <div><b>${esc(c.name)}</b><span>${esc(c.phone)}</span></div>
        <span class="pill">${c.stamps}/10</span>
      </div>`).join("");
  };
  $("#cq").oninput = draw;
  draw();
}

function renderStock() {
  const recommendations = getRestockRecommendations();

  $("#stock").innerHTML = `
    <section class="panel">
      ${recommendations.length > 0 ? `
        <div class="panel" style="margin-bottom:20px; background:rgba(66,133,244,0.1); border-color:rgba(66,133,244,0.3)">
          <h3 style="margin-top:0; color:#4285f4"><i data-lucide="sparkles"></i> IA : Suggestions de réassort</h3>
          <div class="list">
            ${recommendations.map(r => `
              <div style="display:flex; justify-content:space-between; font-size:13px; padding:4px 0">
                <span><b>${esc(r.name)}</b> (épuisé dans ~${r.daysLeft}j)</span>
                <b style="color:var(--green)">+${r.suggestedQty}${r.unit}</b>
              </div>
            `).join("")}
          </div>
        </div>
      ` : ""}
      <input id="sq" class="search" placeholder="Rechercher un produit (Nom, Lot, Catégorie)...">
      <div id="stockRows" class="list"></div>
    </section>`;
  const draw = () => {
    const q = $("#sq").value.toLowerCase();
    const filtered = db.products.filter(p =>
      p.name.toLowerCase().includes(q) ||
      p.category.toLowerCase().includes(q) ||
      (p.batches || []).some(b => b.id.toLowerCase().includes(q))
    );
    $("#stockRows").innerHTML = filtered.map(p => {
      const pct = Math.min(100, (p.stock / (p.alert * 2)) * 100);
      const nearestExpiry = (p.batches || []).reduce((min, b) => b.expiry < min ? b.expiry : min, Infinity);
      const isExpiring = nearestExpiry !== Infinity && (nearestExpiry - Date.now() < 86400000 * 30);

      return `
        <div class="row-card">
          <div style="flex:1">
            <div style="display:flex; justify-content:space-between">
              <b>${esc(p.name)}</b>
              ${isExpiring ? `<span class="pill crit" style="font-size:10px">Péremption Proche</span>` : ""}
            </div>
            <span>${p.stock} ${p.unit} • ${esc(p.category)}</span>
            <div class="gauge-container"><div class="gauge-bar ${p.stock <= p.alert ? 'crit' : ''}" style="width:${pct}%"></div></div>
            ${(p.batches || []).length ? `
              <div style="font-size:11px; color:var(--muted); margin-top:8px">
                Lots: ${p.batches.map(b => `${b.id}(${b.qty}${p.unit})`).join(", ")}
              </div>
            ` : ""}
          </div>
        </div>`;
    }).join("");
  };
  $("#sq").oninput = draw;
  draw();
}

function renderAdmin() {
  const mStats = getMonthlyStats();
  $("#admin").innerHTML = `
    <section class="panel">
      <div class="grid cols-2">
        <div>
          <h2>Préférences</h2>
          <div class="list">
             <div class="row-card"><span>Couleur d'accent</span><input type="color" id="themePicker" value="${db.config.theme}"></div>
             <div class="row-card"><span>Sons UI</span><button class="btn" id="toggleSounds">${db.config.sounds ? 'Activés' : 'Désactivés'}</button></div>
          </div>
          <h2 style="margin-top:20px">Comptabilité (Dépenses)</h2>
          <button class="primary" style="width:100%" onclick="openExpenseModal()">+ Ajouter une dépense</button>
          <div class="list" style="margin-top:10px; max-height: 250px; overflow: auto;">
            ${db.expenses.map(e => `
              <div class="row-card">
                <div><b>${esc(e.label)}</b><span>${fmt(e.ts)}</span></div>
                <div style="display:flex; align-items:center; gap:10px">
                  <b style="color:var(--red)">-${e.amount}€</b>
                  <button class="danger" onclick="deleteExpense(${e.id})" style="min-height:36px; padding:0 8px; font-size:12px">×</button>
                </div>
              </div>`).join("") || "<p>Aucune dépense.</p>"}
          </div>
        </div>
        <div>
          <h2>Performance Mensuelle</h2>
          <div class="list">
            ${mStats.map(s => `
              <div class="row-card">
                <div><b>${esc(s.label)}</b><span>${s.count} transactions</span></div>
                <div style="text-align:right">
                  <b style="color:var(--green)">+${s.revenue.toFixed(2)}€</b>
                  <div style="font-size:12px; font-weight:bold; color:${s.profit >= 0 ? 'var(--green)' : 'var(--red)'}">
                    Marge: ${s.profit.toFixed(2)}€
                  </div>
                </div>
              </div>`).join("")}
          </div>
        </div>
      </div>
      <div class="big-actions" style="margin-top:20px">
        <button class="tile" style="background:var(--green); color:#000" id="zReport">📋 Rapport Z</button>
        <button class="ghost" id="exportCompta">Export Compta</button>
        <button class="ghost" id="exportData">Backup JSON</button>
        <button class="danger" id="resetData">Reset</button>
        <button class="primary" onclick="lock()">Déconnexion</button>
      </div>
    </section>`;
  $("#themePicker").onchange = (e) => { db.config.theme = e.target.value; save(); toast("Couleur appliquée"); };
  $("#toggleSounds").onclick = () => { db.config.sounds = !db.config.sounds; save(); renderAdmin(); };
  $("#zReport").onclick = generateZReport;
  $("#exportCompta").onclick = exportFEC;
  $("#exportData").onclick = exportJson;
  $("#resetData").onclick = () => { if(confirm("Effacer ?")) { localStorage.removeItem(KEY); location.reload(); } };
}

function getMonthlyStats() {
  const stats = {};
  db.sales.forEach(s => {
    const month = new Date(s.ts).toLocaleDateString("fr-FR", { month: "long", year: "numeric" });
    if (!stats[month]) stats[month] = { count: 0, revenue: 0, profit: 0, items: 0 };
    stats[month].count++;
    stats[month].revenue += s.total;
    stats[month].profit += s.total;
    stats[month].items += s.items.reduce((acc, it) => acc + it.qty, 0);
  });
  db.expenses.forEach(e => {
    const month = new Date(e.ts).toLocaleDateString("fr-FR", { month: "long", year: "numeric" });
    if (!stats[month]) stats[month] = { count: 0, revenue: 0, profit: 0, items: 0 };
    stats[month].profit -= e.amount;
  });
  return Object.entries(stats).map(([label, val]) => ({ label, ...val })).sort((a,b) => b.label.localeCompare(a.label));
}

function openExpenseModal() {
  showModal(`
    <h2>Nouvelle Dépense</h2>
    <input id="e_label" class="search" placeholder="Libellé (ex: Loyer, EDF...)">
    <input id="e_amount" type="number" class="search" placeholder="Montant en €">
    <div class="big-actions">
      <button class="ghost" onclick="hideModal()">Annuler</button>
      <button class="primary" id="saveExpense">Enregistrer</button>
    </div>
  `);
  $("#saveExpense").onclick = () => {
    const l = $("#e_label").value;
    const a = Number($("#e_amount").value);
    if (!l || !a) return toast("Veuillez remplir tous les champs", "error");
    db.expenses.unshift({ id: Date.now(), label: l, amount: a, ts: Date.now() });
    save(); hideModal(); renderAdmin(); toast("Dépense enregistrée");
  };
}

function deleteExpense(id) {
  confirmAction("Supprimer cette dépense ?", () => {
    db.expenses = db.expenses.filter(e => e.id !== id);
    save(); renderAdmin(); toast("Dépense supprimée", "error");
  });
}

function openDiscountModal() {
  showModal(`
    <h2>Remise</h2>
    <div style="display:flex; gap:10px; margin:20px 0">
      <button class="btn ${sale.manualDiscountType==='%'?'primary':'ghost'}" onclick="sale.manualDiscountType='%'; openDiscountModal();">%</button>
      <button class="btn ${sale.manualDiscountType==='€'?'primary':'ghost'}" onclick="sale.manualDiscountType='€'; openDiscountModal();">€</button>
    </div>
    <input id="dv" type="number" class="search" value="${sale.manualDiscountValue}" placeholder="Valeur">
    <div class="big-actions"><button class="ghost" onclick="sale.manualDiscountValue=0; hideModal(); renderSell();">Supprimer</button><button class="primary" onclick="sale.manualDiscountValue=Number($('#dv').value); hideModal(); renderSell();">Appliquer</button></div>
  `);
}

function generateZReport() {
  const sales = db.sales.filter(s => isToday(s.ts));
  const total = sales.reduce((sum, s) => sum + (s.total || 0), 0);
  const count = sales.length;

  const tvaTotals = {};
  sales.forEach(s => {
    if (s.tva) {
      Object.entries(s.tva).forEach(([rate, val]) => {
        tvaTotals[rate] = (tvaTotals[rate] || 0) + val;
      });
    }
  });

  showModal(`
    <div id="z-report" style="text-align:center; font-family:monospace; color:#000; background:#fff; padding:20px; border-radius:4px; border:2px solid #000; line-height:1.4">
      <h2 style="margin:0">RAPPORT Z FISCAL</h2>
      <p>${db.config.shopName}</p>
      <p>${new Date().toLocaleString()}</p>

      <div style="border-top:1px dashed #000; margin:10px 0; padding-top:10px; text-align:left">
        <div style="display:flex; justify-content:space-between"><span>Transactions</span><span>${count}</span></div>
        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.2em; margin-top:5px">
          <span>CA BRUT (TTC)</span><span>${total.toFixed(2)}€</span>
        </div>
      </div>

      <div style="border-top:1px solid #000; margin:10px 0; padding-top:10px; text-align:left; font-size:0.9em">
        <b style="display:block; margin-bottom:5px">VENTILATION TVA :</b>
        ${Object.entries(tvaTotals).map(([rate, val]) => `
          <div style="display:flex; justify-content:space-between">
            <span>Taux ${rate}%</span><span>${val.toFixed(2)}€</span>
          </div>
        `).join("") || "<span>Aucune donnée TVA</span>"}
        <div style="display:flex; justify-content:space-between; font-weight:bold; border-top:1px solid #eee; margin-top:5px">
          <span>TOTAL TVA</span><span>${Object.values(tvaTotals).reduce((a,b)=>a+b,0).toFixed(2)}€</span>
        </div>
      </div>

      <div style="border-top:1px solid #000; margin:10px 0; padding-top:10px; text-align:left">
        <div style="display:flex; justify-content:space-between">
          <b>CA NET (HT)</b>
          <b>${(total - Object.values(tvaTotals).reduce((a,b)=>a+b,0)).toFixed(2)}€</b>
        </div>
      </div>

      <p style="font-size:0.7em; margin-top:20px; opacity:0.6">Document de gestion interne non certifié.</p>
      <button class="primary no-print" style="width:100%; margin-top:20px" onclick="window.print()">Imprimer</button>
      <button class="ghost no-print" style="width:100%; margin-top:10px" onclick="hideModal()">Fermer</button>
    </div>
  `);
}

window.showPendingSales = showPendingSales;
window.resumeSale = resumeSale;
window.putSaleOnHold = putSaleOnHold;
window.openExpenseModal = openExpenseModal;
window.deleteExpense = deleteExpense;
window.openDiscountModal = openDiscountModal;
window.startScanner = () => {
  const overlay = $("#reader");
  if (!overlay) return toast("Scanner non prêt", "error");
  overlay.classList.remove("hidden");
  const html5QrCode = new Html5Qrcode("qr-reader");
  const config = { fps: 10, qrbox: { width: 250, height: 250 } };

  html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
    html5QrCode.stop().then(() => {
      overlay.classList.add("hidden");
      handleScan(decodedText);
    });
  }).catch(() => {
    toast("Caméra inaccessible", "error");
    overlay.classList.add("hidden");
  });

  $("#closeScanner").onclick = () => {
    html5QrCode.stop().then(() => overlay.classList.add("hidden"));
  };
};

function handleScan(text) {
  const p = db.products.find(x => x.id === text || x.name === text);
  const c = db.clients.find(x => x.id === text || x.phone === text);

  if (p) {
    if (currentScreen !== 'sell') setScreen('sell');
    sale.curProd = p.id;
    sale.curVariant = p.variants?.[0]?.id || null;
    sale.step = 2;
    renderSell();
    toast(`Produit: ${p.name}`);
  } else if (c) {
    if (currentScreen !== 'sell') setScreen('sell');
    sale.clientId = c.id;
    sale.step = 1;
    renderSell();
    toast(`Client: ${c.name}`);
  } else {
    toast("Code inconnu", "error");
  }
}

function exportJson() {
  const blob = new Blob([JSON.stringify(db, null, 2)], { type: "application/json" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `olab-galaxy.json`;
  a.click();
}

function exportFEC() {
  let csv = "Date;Ref;Libellé;Débit;Crédit;Compte;TVA\n";
  db.sales.forEach(s => {
    const d = new Date(s.ts).toLocaleDateString();
    csv += `${d};SALE-${s.id.slice(0,8)};Vente Client;${s.total.toFixed(2)};0;707;${Object.values(s.tva || {}).reduce((a,b)=>a+b,0).toFixed(2)}\n`;
  });
  db.expenses.forEach(e => {
    const d = new Date(e.ts).toLocaleDateString();
    csv += `${d};EXP-${e.id};${e.label};0;${e.amount.toFixed(2)};606;\n`;
  });

  const blob = new Blob([csv], { type: "text/csv" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `export-compta-${Date.now()}.csv`;
  a.click();
}

$$(".role").forEach((b) => b.addEventListener("click", () => {
  selectedRole = b.dataset.role;
  $$(".role").forEach((x) => x.classList.toggle("active", x === b));
  currentPin = "";
  updatePinUI();
}));
$$(".pin-btn").forEach((b) => b.addEventListener("click", () => {
  if (b.id === "pinClear") currentPin = "";
  else if (b.id === "pinBack") currentPin = currentPin.slice(0, -1);
  else if (currentPin.length < 4) currentPin += b.textContent;
  updatePinUI();
}));
$("#lockBtn").addEventListener("click", lock);
$("#syncBtn").addEventListener("click", () => { save(); toast("Sauvegardé"); });

// OMNISEARCH LOGIC
const omni = {
  show: () => { $("#omnisearch").classList.remove("hidden"); $("#omniInput").focus(); },
  hide: () => { $("#omnisearch").classList.add("hidden"); $("#omniInput").value = ""; $("#omniResults").innerHTML = ""; },
  search: (q) => {
    q = q.toLowerCase();
    if (!q) return $("#omniResults").innerHTML = "";
    const results = [];

    // Actions
    const actions = [
      { label: "Vente", icon: "shopping-cart", act: () => setScreen("sell") },
      { label: "Clients", icon: "users", act: () => setScreen("clients") },
      { label: "Stock", icon: "package", act: () => setScreen("stock") },
      { label: "Admin", icon: "settings", act: () => setScreen("admin") },
      { label: "Sauvegarder", icon: "save", act: () => { save(); toast("Manuel Save"); } },
      { label: "Verrouiller", icon: "lock", act: lock }
    ];
    actions.filter(a => a.label.toLowerCase().includes(q)).forEach(a => results.push({ ...a, type: "Action" }));

    // Products
    db.products.filter(p => p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q)).forEach(p => {
      results.push({ label: p.name, type: "Produit", icon: "package", act: () => { setScreen("stock"); $("#sq").value = p.name; renderStock(); } });
    });

    // Clients
    db.clients.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q)).forEach(c => {
      results.push({ label: c.name, type: "Client", icon: "user", act: () => { db.selectedClientId = c.id; save(); setScreen("client"); } });
    });

    omni.currentIndex = 0;
    $("#omniResults").innerHTML = results.map((r, i) => `
      <div class="row-card omni-item ${i === 0 ? 'selected' : ''}" data-idx="${i}" style="margin-bottom:4px; padding:12px; cursor:pointer" onclick="omni.run(${i})">
        <div style="display:flex; align-items:center; gap:12px">
          <i data-lucide="${r.icon}" style="width:16px; opacity:0.6"></i>
          <div><b>${esc(r.label)}</b> <span style="font-size:10px; opacity:0.5; text-transform:uppercase">${r.type}</span></div>
        </div>
      </div>
    `).join("");
    lucide.createIcons();
    omni.currentResults = results;
  },
  navigate: (dir) => {
    const items = $$(".omni-item");
    if (!items.length) return;
    items[omni.currentIndex].classList.remove("selected");
    omni.currentIndex = (omni.currentIndex + dir + items.length) % items.length;
    items[omni.currentIndex].classList.add("selected");
    items[omni.currentIndex].scrollIntoView({ block: "nearest" });
  },
  run: (i) => { const r = omni.currentResults[i]; if(r) r.act(); omni.hide(); }
};

$("#searchBtn").onclick = omni.show;
$("#omniInput").oninput = (e) => omni.search(e.target.value);
window.addEventListener("keydown", (e) => {
  if (e.ctrlKey && e.key === "k") { e.preventDefault(); omni.show(); }
  if (e.key === "Escape") omni.hide();
  if (!$("#omnisearch").classList.contains("hidden")) {
    if (e.key === "ArrowDown") { e.preventDefault(); omni.navigate(1); }
    if (e.key === "ArrowUp") { e.preventDefault(); omni.navigate(-1); }
    if (e.key === "Enter") { e.preventDefault(); omni.run(omni.currentIndex); }
  }
});
function getRestockRecommendations() {
  const recs = [];
  const lookbackDays = 14;
  const cutoff = Date.now() - (lookbackDays * 86400000);

  db.products.forEach(p => {
    // Calculate total qty sold in lookback period
    const recentSales = db.sales.filter(s => s.ts > cutoff);
    let totalSold = 0;
    recentSales.forEach(s => {
      s.items.forEach(it => {
        if (it.productId === p.id) {
           const v = p.variants?.find(vx => vx.id === it.variantId);
           totalSold += v ? v.weight * it.qty : it.qty;
        }
      });
    });

    const velocity = totalSold / lookbackDays; // unit per day
    if (velocity > 0) {
      const daysLeft = Math.floor(p.stock / velocity);
      if (daysLeft <= 7) { // Alert if less than 7 days of stock
        recs.push({
          name: p.name,
          daysLeft,
          unit: p.unit,
          suggestedQty: Math.ceil(velocity * 30) // Suggest 30 days of stock
        });
      }
    } else if (p.stock <= p.alert) {
       recs.push({ name: p.name, daysLeft: 0, unit: p.unit, suggestedQty: p.alert * 5 });
    }
  });
  return recs.sort((a,b) => a.daysLeft - b.daysLeft);
}

window.omni = omni;

if (db.role) { $("#lock").classList.add("hidden"); $("#workspace").classList.remove("hidden"); boot(); } else { applyTheme(); }
