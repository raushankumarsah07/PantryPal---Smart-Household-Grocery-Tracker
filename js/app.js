// ---------- helpers ----------
async function api(url, data) {
  const res = await fetch(url, {
    method: data ? 'POST' : 'GET',
    headers: { 'Content-Type': 'application/json' },
    body: data ? JSON.stringify(data) : undefined,
  });
  if (res.status === 401) { location.href = 'login.php'; return; }
  return res.json();
}

function esc(s) {                       // stops HTML injection
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

const $ = id => document.getElementById(id);
let groceries = [];
let shopping = [];

// ---------- inventory ----------
async function loadGroceries() {
  groceries = await api('api/groceries.php?action=list');
  renderGroceries();
  updateStats();
}

function renderGroceries() {
  const q = $('search').value.toLowerCase();
  const list = groceries.filter(g => g.name.toLowerCase().includes(q));
  $('groceryBody').innerHTML = list.map(g => `
    <tr>
      <td>${esc(g.name)}</td>
      <td>
        <button class="mini" onclick="adjust('${g.id}', -1)">−</button>
        ${g.quantity} ${esc(g.unit)}
        <button class="mini" onclick="adjust('${g.id}', 1)">+</button>
      </td>
      <td>${g.expiryDate || '-'}</td>
      <td>
        <span class="tag ${g.expiry}">${g.expiryText}</span>
        ${g.lowStock ? '<span class="tag low">Low stock</span>' : ''}
      </td>
      <td class="actions">
        ${g.lowStock ? `<button class="link" onclick="addToList('${g.id}')">Add to list</button>` : ''}
        <button class="link" onclick="editItem('${g.id}')">Edit</button>
        <button class="link danger" onclick="deleteItem('${g.id}')">Delete</button>
      </td>
    </tr>`).join('');
  $('groceryEmpty').hidden = groceries.length > 0;
}

$('groceryForm').addEventListener('submit', async e => {
  e.preventDefault();
  const data = {
    id: $('gId').value,
    name: $('gName').value,
    quantity: $('gQty').value,
    unit: $('gUnit').value,
    minQuantity: $('gMin').value,
    expiryDate: $('gExpiry').value,
  };
  await api('api/groceries.php?action=' + (data.id ? 'update' : 'add'), data);
  resetForm();
  loadGroceries();
});

function editItem(id) {
  const g = groceries.find(x => x.id === id);
  $('gId').value = g.id;
  $('gName').value = g.name;
  $('gQty').value = g.quantity;
  $('gUnit').value = g.unit;
  $('gMin').value = g.minQuantity;
  $('gExpiry').value = g.expiryDate;
  $('gSave').textContent = 'Save changes';
  $('gCancel').hidden = false;
  $('gName').focus();
}

function resetForm() {
  $('groceryForm').reset();
  $('gId').value = '';
  $('gSave').textContent = 'Add item';
  $('gCancel').hidden = true;
}
$('gCancel').addEventListener('click', resetForm);

async function adjust(id, delta) {
  await api('api/groceries.php?action=adjust', { id, delta });
  loadGroceries();
}

async function deleteItem(id) {
  if (!confirm('Delete this item?')) return;
  await api('api/groceries.php?action=delete', { id });
  loadGroceries();
}

async function addToList(id) {            // smart restock suggestion
  const g = groceries.find(x => x.id === id);
  await api('api/shopping.php?action=add', { name: g.name, quantity: '' });
  loadShopping();
}

$('search').addEventListener('input', renderGroceries);

// ---------- shopping list ----------
async function loadShopping() {
  shopping = await api('api/shopping.php?action=list');
  $('shopList').innerHTML = shopping.map(s => `
    <li class="${s.purchased ? 'done' : ''}">
      <label>
        <input type="checkbox" ${s.purchased ? 'checked' : ''} onchange="toggleShop('${s.id}')">
        ${esc(s.name)} ${s.quantity ? '<small>(' + esc(s.quantity) + ')</small>' : ''}
      </label>
      <button class="link danger" onclick="deleteShop('${s.id}')">Remove</button>
    </li>`).join('');
  $('shopEmpty').hidden = shopping.length > 0;
  updateStats();
}

$('shopForm').addEventListener('submit', async e => {
  e.preventDefault();
  await api('api/shopping.php?action=add', { name: $('shName').value, quantity: $('shQty').value });
  $('shopForm').reset();
  loadShopping();
});

async function toggleShop(id) { await api('api/shopping.php?action=toggle', { id }); loadShopping(); }
async function deleteShop(id) { await api('api/shopping.php?action=delete', { id }); loadShopping(); }
$('clearBought').addEventListener('click', async () => {
  await api('api/shopping.php?action=clear', {});
  loadShopping();
});

// ---------- dashboard numbers ----------
function updateStats() {
  $('sTotal').textContent   = groceries.length;
  $('sLow').textContent     = groceries.filter(g => g.lowStock).length;
  $('sSoon').textContent    = groceries.filter(g => g.expiry === 'soon' || g.expiry === 'today').length;
  $('sExpired').textContent = groceries.filter(g => g.expiry === 'expired').length;
  $('sShop').textContent    = shopping.filter(s => !s.purchased).length;
}

loadGroceries();
loadShopping();
