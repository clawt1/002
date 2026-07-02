const KEY = "olab004_tablet_local";
const pins = { admin: "0420", staff: "2024" };
let selectedRole = "admin";
let currentScreen = "home";
let sale = { step: 0, clientId: null, items: [], curProd: "p1", curQty: 1 };

const seed = {
  role: null,
  selectedClientId: "c1",
  clients: [
    { id: "c1", name: "Martin Lemoine", phone: "06 12 34 56 78", email: "martin@email.fr", stamps: 7, segment: "Client", notes: "" },
    { id: "c2", name: "Nadia Résines", phone: "06 01 01 01 01", email: "nadia@olab.local", stamps: 9, segment: "Gros client", notes: "Préférence résines." },
    { id: "c3", name: "Sofia Fleurs", phone: "06 03 03 03 03", email: "sofia@olab.local", stamps: 4, segment: "Client", notes: "Nouveautés fleurs." },
  ],
  products: [
    { id: "p1", name: "Amnesia CBD", category: "Fleurs", unit: "g", stock: 76.5, alert: 15 },
    { id: "p2", name: "3x Filtre Lemon", category: "Résines", unit: "g", stock: 45, alert: 12 },
    { id: "p3", name: "Huile Nateava 20%", category: "Huiles", unit: "pièce", stock: 8, alert: 3 },
    { id: "p4", name: "Vape relax CBD", category: "E-liquides", unit: "pièce", stock: 7, alert: 5 },
  ],
  rewards: [
    { id: "r1", name: "10€ offerts", cost: 10 },
    { id: "r2", name: "Infusion offerte", cost: 6 },
    { id: "r3", name: "Accessoire offert", cost: 8 },
  ],
  sales: [
    { id: "s1", clientId: "c1", productId: "p1", qty: 2, at: "Aujourd'hui 10:12" },
    { id: "s2", clientId: null, productId: "p2", qty: 10, at: "Hier 18:20" },
  ],
  history: [
    { clientId: "c1", label: "Tampon ajouté", sign: "+1", at: "Aujourd'hui 14:32" },
    { clientId: "c1", label: "Tampon ajouté", sign: "+1", at: "10/05 16:45" },
  ],
};

let db = JSON.parse(localStorage.getItem(KEY) || "null") || structuredClone(seed);

const $ = (q) => document.querySelector(q);
const $$ = (q) => [...document.querySelectorAll(q)];
const save = () => localStorage.setItem(KEY, JSON.stringify(db));
const product = (id) => db.products.find((p) => p.id === id);
const client = (id) => db.clients.find((c) => c.id === id);
const currentClient = () => client(db.selectedClientId) || db.clients[0];
const initials = (name) => name.split(/\s+/).map((p) => p[0]).join("").slice(0, 2).toUpperCase();
const now = () => new Date().toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit" });

function toast(msg, type = "success") {
  const t = $("#toast");
  t.textContent = msg;
  t.className = `toast ${type === "error" ? "error" : ""}`;
  t.classList.remove("hidden");
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
  return {
    clients: db.clients.length,
    sales: db.sales.length,
    qty: db.sales.reduce((s, x) => s + x.qty, 0),
    stamps: db.clients.reduce((s, c) => s + c.stamps, 0),
    alerts: db.products.filter((p) => p.stock <= p.alert).length,
  };
}

function unlock() {
  const pin = $("#pin").value;
  if (pin !== pins[selectedRole]) return toast("PIN incorrect");
  db.role = selectedRole;
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
  $("#bottomNav").innerHTML = items.map(([id, label]) => `<button data-screen="${id}">${label}</button>`).join("");
  $$("[data-screen]").forEach((b) => b.addEventListener("click", () => setScreen(b.dataset.screen)));
}

function setScreen(screen) {
  currentScreen = screen;
  $$(".screen").forEach((el) => el.classList.toggle("active", el.id === screen));
  $$("#bottomNav button").forEach((el) => el.classList.toggle("active", el.dataset.screen === screen));
  $("#screenTitle").textContent = { home: "Accueil", sell: "Vente tactile", client: "Fiche client", clients: "Clients", stock: "Stock local", admin: "Administration locale" }[screen];
  render();
}

function render() {
  if (currentScreen === "home") renderHome();
  if (currentScreen === "sell") renderSell();
  if (currentScreen === "client") renderClient();
  if (currentScreen === "clients") renderClients();
  if (currentScreen === "stock") renderStock();
  if (currentScreen === "admin") renderAdmin();
}

function renderHome() {
  const m = metrics();
  const salesByDay = getSalesLast7Days();
  const topProducts = getTopProducts(3);

  $("#home").innerHTML = `
    <div class="grid cols-2">
      <section class="client-card">${clientCard(currentClient())}</section>
      <section class="grid cards">
        ${tile("＋", "Vente", "Workflow tactile", "sell")}
        ${tile("◎", "Clients", `${m.clients} profils`, "clients")}
        ${tile("▦", "Stocks", `${m.alerts} alerte(s)`, "stock")}
        ${tile("🎁", "Récompenses", `${db.rewards.length} offres`, "client")}
      </section>
    </div>
    <div class="grid cards" style="margin-top:14px">
      <div class="panel">
        <h2 style="font-size:18px; margin-bottom:10px">Ventes 7 derniers jours</h2>
        <div class="chart-container">
          ${salesByDay.map(d => `
            <div class="chart-col">
              <div class="chart-bar" style="height:${d.pct}%" title="${d.count} ventes"></div>
              <span class="chart-label">${d.label}</span>
            </div>
          `).join("")}
        </div>
      </div>
      <div class="panel">
        <h2 style="font-size:18px; margin-bottom:10px">Produits phares</h2>
        <div class="list">
          ${topProducts.map(p => `
            <div class="row-card" style="min-height:60px; padding:10px 16px">
              <b>${p.name}</b>
              <span class="pill">${p.count}</span>
            </div>
          `).join("") || "<p>Aucune vente.</p>"}
        </div>
      </div>
    </div>
    <div class="grid cards" style="margin-top:14px">
      <div class="tile"><span class="icon">🧾</span><strong>${m.sales}</strong><span>ventes locales</span></div>
      <div class="tile"><span class="icon">⚖</span><strong>${m.qty}</strong><span>quantité vendue</span></div>
      <div class="tile"><span class="icon">●</span><strong>${m.stamps}</strong><span>tampons actifs</span></div>
    </div>`;
  bindTiles();
  bindClientActions();
}

function getSalesLast7Days() {
  const days = ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"];
  const results = [];
  const today = new Date();

  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(today.getDate() - i);
    const label = days[d.getDay()];
    const dateStr = d.toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit" });

    // Simuler le comptage pour la démo car "at" est une string locale complexe
    // Dans un vrai cas, on parserait la date correctement.
    const count = db.sales.filter(s => s.at.includes(dateStr)).length;
    results.push({ label, count });
  }

  const max = Math.max(...results.map(r => r.count), 1);
  return results.map(r => ({ ...r, pct: Math.max(5, (r.count / max) * 100) }));
}

function getTopProducts(n) {
  const counts = {};
  db.sales.forEach(s => {
    counts[s.productId] = (counts[s.productId] || 0) + s.qty;
  });
  return Object.entries(counts)
    .sort((a, b) => b[1] - a[1])
    .slice(0, n)
    .map(([id, count]) => ({ name: product(id)?.name || "Inconnu", count }));
}

function tile(icon, title, desc, screen) {
  return `<button class="tile" data-go="${screen}"><span class="icon">${icon}</span><strong>${title}</strong><span>${desc}</span></button>`;
}

function bindTiles() {
  $$("[data-go]").forEach((b) => b.addEventListener("click", () => setScreen(b.dataset.go)));
}

function clientCard(c) {
  return `
    <div class="client-head"><div class="avatar">${initials(c.name)}</div><div><h2>${c.name}</h2><p>${c.phone} • ${c.segment}</p><p>${c.email}</p></div></div>
    <div class="stamp-ring"><div><strong>${c.stamps}</strong><span>/10 tampons</span></div></div>
    <div class="stamp-grid">${Array.from({ length: 10 }, (_, i) => `<button class="stamp ${i < c.stamps ? "done" : ""}" data-stamp="${i + 1}">${i < c.stamps ? "OK" : "+"}</button>`).join("")}</div>
    <div class="big-actions"><button class="primary" data-action="add">+1</button><button class="danger" data-action="remove">-1</button><button class="ghost" data-action="reward">Cadeau</button></div>`;
}

function bindClientActions() {
  $$("[data-action]").forEach((b) => b.addEventListener("click", () => {
    const c = currentClient();
    if (b.dataset.action === "add" && c.stamps < 10) { c.stamps++; addHistory(c.id, "+1", "Tampon ajouté"); }
    if (b.dataset.action === "remove" && c.stamps > 0) { c.stamps--; addHistory(c.id, "-1", "Tampon retiré"); }
    if (b.dataset.action === "reward") { c.stamps = 0; addHistory(c.id, "🎁", "Récompense accordée"); }
    save(); toast("Carte mise à jour"); render();
  }));
  $$("[data-stamp]").forEach((b) => b.addEventListener("click", () => {
    currentClient().stamps = Number(b.dataset.stamp);
    addHistory(currentClient().id, "↻", `Ajusté à ${b.dataset.stamp}`);
    save(); render();
  }));
}

function addHistory(clientId, sign, label) {
  db.history.unshift({ clientId, sign, label, at: now() });
}

function renderSell() {
  const titles = ["Choisir le client", "Choisir le produit", "Choisir la quantité", "Récapitulatif"];
  $("#sell").innerHTML = `
    <section class="sale-step">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px">
        <h2 style="margin:0">${titles[sale.step]}</h2>
        <span class="pill">${sale.items.length} article(s)</span>
      </div>
      ${sale.step === 0 ? clientChoices() : ""}
      ${sale.step === 1 ? productChoices() : ""}
      ${sale.step === 2 ? qtyChoices() : ""}
      ${sale.step === 3 ? cartChoices() : ""}
      <div class="big-actions">
        <button class="ghost" id="prev">Retour</button>
        <button class="primary" id="next">${sale.step === 2 ? "Ajouter" : sale.step === 3 ? "Encaisser" : "Continuer"}</button>
      </div>
    </section>`;

  $$("[data-client]").forEach((b) => b.onclick = () => { sale.clientId = b.dataset.client || null; renderSell(); });
  $$("[data-product]").forEach((b) => b.onclick = () => { sale.curProd = b.dataset.product; renderSell(); });
  $$("[data-qty]").forEach((b) => b.onclick = () => { const v = b.dataset.qty; sale.curQty = v === "reset" ? 1 : Math.max(.5, Number((sale.curQty + Number(v)).toFixed(2))); renderSell(); });

  $("#prev").onclick = () => {
    if (sale.step === 1 && sale.items.length > 0) sale.step = 3;
    else sale.step = Math.max(0, sale.step - 1);
    renderSell();
  };

  $("#next").onclick = () => {
    if (sale.step === 2) {
      const p = product(sale.curProd);
      if (p.stock < sale.curQty) return toast("Stock insuffisant", "error");
      sale.items.push({ productId: p.id, qty: sale.curQty });
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

function clientChoices() {
  return `<div class="choice-grid"><button class="choice ${!sale.clientId ? "active" : ""}" data-client="">Non membre<span>Vente comptoir</span></button>${db.clients.map((c) => `<button class="choice ${sale.clientId === c.id ? "active" : ""}" data-client="${c.id}"><b>${c.name}</b><span>${c.stamps}/10 tampons</span></button>`).join("")}</div>`;
}
function productChoices() {
  return `<div class="choice-grid">${db.products.map((p) => `<button class="choice ${sale.curProd === p.id ? "active" : ""}" data-product="${p.id}"><b>${p.name}</b><span>${p.stock} ${p.unit} • ${p.category}</span></button>`).join("")}</div>`;
}
function qtyChoices() {
  return `<div class="qty-display">${sale.curQty}</div><div class="qty-pad">${[.5, 1, 2, 5, 10, -.5, -1, "reset"].map((q) => `<button data-qty="${q}">${q === "reset" ? "Reset" : q > 0 ? "+" + q : q}</button>`).join("")}</div>`;
}
function cartChoices() {
  return `
    <div class="list" style="margin-bottom:14px">
      ${sale.items.map((it, idx) => `
        <div class="row-card">
          <div><b>${product(it.productId).name}</b><span>${it.qty} ${product(it.productId).unit}</span></div>
          <button class="danger" style="min-height:40px; padding:0 12px; border-radius:10px" onclick="sale.items.splice(${idx},1); renderSell();">×</button>
        </div>
      `).join("")}
      <button class="ghost" style="width:100%; border:1px dashed var(--line); min-height:60px" onclick="sale.step=1; renderSell();">＋ Ajouter un autre produit</button>
    </div>`;
}

function completeSale() {
  if (sale.items.length === 0) return toast("Panier vide", "error");

  sale.items.forEach(it => {
    const p = product(it.productId);
    p.stock = Number((p.stock - it.qty).toFixed(2));
    db.sales.unshift({ id: crypto.randomUUID(), clientId: sale.clientId, productId: p.id, qty: it.qty, at: now() });
  });

  if (sale.clientId) {
    const c = client(sale.clientId);
    c.stamps = Math.min(10, c.stamps + 1);
    db.selectedClientId = c.id;
    addHistory(c.id, "+1", `Vente multi-produits (${sale.items.length})`);
  }

  sale = { step: 0, clientId: null, items: [], curProd: "p1", curQty: 1 };
  save(); toast("Vente enregistrée avec succès");
  setScreen("home");
}

function renderClient() {
  const c = currentClient();
  const h = db.history.filter((x) => x.clientId === c.id).slice(0, 8);
  $("#client").innerHTML = `<div class="grid cols-2"><section class="client-card">${clientCard(c)}</section><section class="panel"><h2>Historique</h2><div class="list">${h.map((x) => `<div class="row-card"><div><b>${x.label}</b><span>${x.at}</span></div><span class="pill">${x.sign}</span></div>`).join("") || "<p>Aucun historique.</p>"}</div><h2>Note</h2><textarea id="note" class="search">${c.notes || ""}</textarea><button id="saveNote" class="primary" style="width:100%">Sauvegarder</button></section></div>`;
  bindClientActions();
  $("#saveNote").addEventListener("click", () => { c.notes = $("#note").value; save(); toast("Note sauvée"); });
}

function renderClients() {
  $("#clients").innerHTML = `
    <section class="panel">
      <div style="display:flex; gap:12px; margin-bottom:14px">
        <input id="q" class="search" placeholder="Nom, téléphone, email" style="margin-bottom:0">
        <button class="primary" id="addClientBtn" style="min-height:68px; width:120px; font-size:32px">＋</button>
      </div>
      <div id="rows" class="list"></div>
    </section>`;
  const draw = () => {
    const q = $("#q").value.toLowerCase();
    const rows = db.clients.filter((c) => !q || c.name.toLowerCase().includes(q) || c.phone.includes(q) || c.email.toLowerCase().includes(q));
    $("#rows").innerHTML = rows.map((c) => `
      <div class="row-card">
        <div style="flex:1; cursor:pointer" data-open="${c.id}">
          <b>${c.name}</b><span>${c.phone} • ${c.email}</span>
        </div>
        <span class="pill" style="margin-right:10px">${c.stamps}/10</span>
        <button class="ghost" style="min-height:50px; padding:0 15px; border-radius:12px" data-edit-client="${c.id}">✎</button>
      </div>`).join("");
    $$("[data-open]").forEach((b) => b.addEventListener("click", () => { db.selectedClientId = b.dataset.open; save(); setScreen("client"); }));
    $$("[data-edit-client]").forEach((b) => b.addEventListener("click", () => openClientModal(b.dataset.editClient)));
  };
  $("#q").addEventListener("input", draw);
  $("#addClientBtn").onclick = () => openClientModal();
  draw();
}

function openClientModal(id = null) {
  const c = id ? client(id) : { name: "", phone: "", email: "", segment: "Client", stamps: 0, notes: "" };
  showModal(`
    <h2>${id ? "Modifier client" : "Nouveau client"}</h2>
    <div style="margin-top:20px; display:grid; gap:12px">
      <input id="c_name" class="search" placeholder="Nom complet" value="${c.name}">
      <input id="c_phone" class="search" placeholder="Téléphone" value="${c.phone}">
      <input id="c_email" class="search" placeholder="Email" value="${c.email}">
      <select id="c_segment" class="search">
        <option ${c.segment === "Client" ? "selected" : ""}>Client</option>
        <option ${c.segment === "Gros client" ? "selected" : ""}>Gros client</option>
        <option ${c.segment === "VIP" ? "selected" : ""}>VIP</option>
      </select>
    </div>
    <div class="big-actions">
      <button class="ghost" onclick="hideModal()">Annuler</button>
      <button class="primary" id="saveClientBtn">Enregistrer</button>
    </div>
    ${id ? `<button class="danger" id="delClientBtn" style="width:100%; margin-top:12px; min-height:50px; font-size:16px">Supprimer le client</button>` : ""}
  `);
  $("#saveClientBtn").onclick = () => saveClient(id);
  if ($("#delClientBtn")) $("#delClientBtn").onclick = () => confirmAction(`Supprimer ${c.name} ?`, () => deleteClient(id));
}

function saveClient(id) {
  const name = $("#c_name").value;
  if (!name) return toast("Le nom est requis", "error");
  const data = {
    name,
    phone: $("#c_phone").value,
    email: $("#c_email").value,
    segment: $("#c_segment").value,
  };
  if (id) {
    Object.assign(client(id), data);
  } else {
    const newId = "c" + Date.now();
    db.clients.push({ id: newId, ...data, stamps: 0, notes: "" });
    db.selectedClientId = newId;
  }
  save(); hideModal(); toast("Client enregistré"); render();
}

function deleteClient(id) {
  db.clients = db.clients.filter(c => c.id !== id);
  if (db.selectedClientId === id) db.selectedClientId = db.clients[0]?.id || null;
  save(); render(); toast("Client supprimé", "error");
}

function renderStock() {
  $("#stock").innerHTML = `
    <section class="panel">
      ${db.role === "admin" ? `<button class="primary" id="addProductBtn" style="width:100%; margin-bottom:14px">＋ Ajouter un produit</button>` : ""}
      <div class="list">${db.products.map((p) => {
        const pct = Math.min(100, (p.stock / (p.alert * 4)) * 100);
        const status = p.stock <= p.alert ? "crit" : p.stock <= p.alert * 2 ? "warn" : "";
        return `
          <div class="row-card">
            <div style="flex:1">
              <b>${p.name}</b>
              <span>${p.category} • ${p.unit}</span>
              <div class="gauge-container"><div class="gauge-bar ${status}" style="width:${pct}%"></div></div>
            </div>
            <div style="text-align:right; margin-left:14px">
              <span class="pill ${status}">${p.stock} ${p.unit}</span>
              ${db.role === "admin" ? `<button class="ghost" style="margin-top:8px; min-height:40px; padding:0 10px; border-radius:10px; font-size:14px" data-edit-product="${p.id}">Modifier</button>` : ""}
            </div>
          </div>`;
      }).join("")}</div>
      ${db.role === "admin" ? `<button id="reassort" class="ghost" style="width:100%; margin-top:14px; border:1px dashed var(--line)">Réassortir les alertes</button>` : ""}
    </section>`;

  if ($("#addProductBtn")) $("#addProductBtn").onclick = () => openProductModal();
  if ($("#reassort")) $("#reassort").onclick = () => {
    db.products.forEach((p) => { if (p.stock <= p.alert) p.stock += p.alert * 2; });
    save(); renderStock(); toast("Réassort automatique effectué");
  };
  $$("[data-edit-product]").forEach(b => b.onclick = () => openProductModal(b.dataset.editProduct));
}

function openProductModal(id = null) {
  const p = id ? product(id) : { name: "", category: "Fleurs", unit: "g", stock: 0, alert: 5 };
  showModal(`
    <h2>${id ? "Modifier produit" : "Nouveau produit"}</h2>
    <div style="margin-top:20px; display:grid; gap:12px">
      <input id="p_name" class="search" placeholder="Nom du produit" value="${p.name}">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
        <input id="p_category" class="search" placeholder="Catégorie" value="${p.category}">
        <input id="p_unit" class="search" placeholder="Unité (g, pce...)" value="${p.unit}">
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
        <div><label style="font-size:12px; color:var(--muted)">Stock actuel</label><input id="p_stock" type="number" class="search" value="${p.stock}"></div>
        <div><label style="font-size:12px; color:var(--muted)">Seuil alerte</label><input id="p_alert" type="number" class="search" value="${p.alert}"></div>
      </div>
    </div>
    <div class="big-actions">
      <button class="ghost" onclick="hideModal()">Annuler</button>
      <button class="primary" id="saveProductBtn">Enregistrer</button>
    </div>
    ${id ? `<button class="danger" id="delProductBtn" style="width:100%; margin-top:12px; min-height:50px; font-size:16px">Supprimer le produit</button>` : ""}
  `);
  $("#saveProductBtn").onclick = () => saveProduct(id);
  if ($("#delProductBtn")) $("#delProductBtn").onclick = () => confirmAction(`Supprimer ${p.name} ?`, () => deleteProduct(id));
}

function saveProduct(id) {
  const name = $("#p_name").value;
  if (!name) return toast("Le nom est requis", "error");
  const data = {
    name,
    category: $("#p_category").value,
    unit: $("#p_unit").value,
    stock: Number($("#p_stock").value),
    alert: Number($("#p_alert").value),
  };
  if (id) {
    Object.assign(product(id), data);
  } else {
    db.products.push({ id: "p" + Date.now(), ...data });
  }
  save(); hideModal(); toast("Produit enregistré"); render();
}

function deleteProduct(id) {
  db.products = db.products.filter(p => p.id !== id);
  save(); render(); toast("Produit supprimé", "error");
}

function renderAdmin() {
  if (db.role !== "admin") return setScreen("home");
  const data = JSON.stringify(db, null, 2);
  $("#admin").innerHTML = `<section class="panel"><div class="grid cards"><button id="export" class="tile"><span class="icon">⬇</span><strong>Export</strong><span>Sauvegarde JSON</span></button><button id="import" class="tile"><span class="icon">⬆</span><strong>Import</strong><span>Restaurer JSON</span></button><button id="reset" class="tile"><span class="icon">↻</span><strong>Reset</strong><span>Données démo</span></button></div><textarea class="search" style="height:220px;margin-top:14px">${data}</textarea></section>`;
  $("#export").addEventListener("click", exportJson);
  $("#import").addEventListener("click", () => $("#importFile").click());
  $("#reset").addEventListener("click", () => { if (!confirm("Réinitialiser ?")) return; localStorage.removeItem(KEY); db = structuredClone(seed); db.role = "admin"; save(); boot(); });
}

function exportJson() {
  const blob = new Blob([JSON.stringify(db, null, 2)], { type: "application/json" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `olab004-${Date.now()}.json`;
  a.click();
  URL.revokeObjectURL(a.href);
}

$("#importFile").addEventListener("change", async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  db = JSON.parse(await file.text());
  save(); boot(); toast("Import terminé");
});

$$(".role").forEach((b) => b.addEventListener("click", () => {
  selectedRole = b.dataset.role;
  $$(".role").forEach((x) => x.classList.toggle("active", x === b));
  $("#pin").value = pins[selectedRole];
}));
$("#unlock").addEventListener("click", unlock);
$("#pin").addEventListener("keydown", (e) => { if (e.key === "Enter") unlock(); });
$("#lockBtn").addEventListener("click", lock);
$("#syncBtn").addEventListener("click", () => { save(); toast("Sauvegardé sur cette tablette"); });

if ("serviceWorker" in navigator && location.protocol !== "file:") {
  navigator.serviceWorker.register("sw.js").catch(() => {});
}

if (db.role) {
  $("#lock").classList.add("hidden");
  $("#workspace").classList.remove("hidden");
  boot();
}
