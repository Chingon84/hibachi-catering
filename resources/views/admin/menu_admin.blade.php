<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Menu</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#b21e27;--brand-hover:#9a1a22}
    .title{font-size:22px;margin:0}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04);margin-bottom:12px}
    .card-body{padding:16px}
    .muted{color:var(--muted)}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
    .btn:hover{background:var(--brand-hover)}
    .btn.secondary{background:#4b5563}
    .btns{display:flex;gap:10px;align-items:center}
    .input{width:100%;padding:7px 9px;border:1px solid var(--border);border-radius:10px;background:#fff;font-size:14px}
    .menu-name-input{max-width:220px;border:0;border-bottom:1px solid #e5e7eb;border-radius:0;padding:6px 4px}
    .menu-name-input:focus{outline:none;border-bottom-color:var(--brand)}
    .menu-desc-input{max-width:320px;border:0;border-bottom:1px solid #e5e7eb;border-radius:0;padding:6px 4px}
    .menu-desc-input:focus{outline:none;border-bottom-color:var(--brand)}
    table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed}
    th,td{padding:8px 10px;font-size:14px;text-align:left;vertical-align:middle}
    thead th{background:#f3f4f6;color:#374151;border-bottom:1px solid var(--border)}
    tbody tr + tr td{border-top:1px solid var(--border)}
    .price{width:110px;text-align:right;padding:6px 4px;border:0;border-bottom:1px solid #e5e7eb;border-radius:0;background:#fff}
    .price:focus{outline:none;border-bottom-color:var(--brand)}
    .price-wrap{display:flex;align-items:center;gap:8px;justify-content:flex-end}
    .price-wrap .icon-btn{min-width:34px}
    @media (max-width: 1000px){ .price{width:90px} }
    .key{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .alert{border-radius:10px;padding:10px 12px;font-size:14px}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    .toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:8px}
    .link{color:var(--brand);text-decoration:none;font-weight:600;cursor:pointer}
    .link:hover{text-decoration:underline}
    .changes{font-size:13px;color:#6b7280}
    .danger{color:#b21e27}
    /* Grid for category cards */
    .cats{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width: 1000px){.cats{grid-template-columns:1fr}}
    /* Icon button (hover/click effects) */
    .icon-btn{appearance:none;border:1px solid #e5e7eb;background:#fff;color:#b21e27;border-radius:10px;width:34px;height:34px;line-height:1;font-size:18px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease}
    .icon-btn:hover{transform:translateY(-1px);box-shadow:0 4px 10px rgba(178,30,39,.15);border-color:#d1d5db;background:#fff}
    .icon-btn:active{transform:translateY(0) scale(.98);box-shadow:0 2px 6px rgba(178,30,39,.15)}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div></div>
      <div class="btns">
        <a href="{{ route('admin.dashboard') }}" class="btn secondary">Back</a>
        <button class="btn" type="submit" form="menuForm">Save Menu</button>
      </div>
    </div>

    @if (session('ok'))
      <div class="card"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
    @endif
    @if ($errors->any())
      <div class="card"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
    @endif

    <form method="post" action="{{ route('admin.menu.update') }}" id="menuForm">
      @csrf
      <div class="toolbar">
        <div class="changes">Unsaved changes: <span id="chgCount">0</span></div>
        <div style="display:flex;gap:10px;align-items:center">
          <button type="button" class="icon-btn" id="addCat" title="Add category">+</button>
        </div>
      </div>
      <div class="cats">
      @foreach(($cfg ?? []) as $cat => $rows)
        <div class="card">
          <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
              <h2 style="margin:0">{{ $cat }}</h2>
              <div style="display:flex;gap:8px;align-items:center">
                <button type="button" class="icon-btn add-item" data-cat="{{ $cat }}" title="Add item">+</button>
                <button type="button" class="icon-btn remove-cat" data-cat="{{ $cat }}" title="Remove category" aria-label="Remove category">−</button>
              </div>
            </div>
            <table data-cat="{{ $cat }}">
              <thead>
                <tr>
                  <th style="width:140px">Key</th>
                  <th style="width:32%">Name</th>
                  <th style="width:34%">Description</th>
                  <th style="width:120px;text-align:right">Price</th>
                </tr>
              </thead>
              <tbody>
                @foreach((array)$rows as $i => $it)
                  <tr data-row="{{ $cat }}::{{ $it['key'] ?? '' }}">
                    <td class="key">{{ $it['key'] ?? '' }}
                      <input type="hidden" data-init="{{ $it['key'] ?? '' }}" name="items[{{ $cat }}][{{ $i }}][key]" value="{{ $it['key'] ?? '' }}">
                    </td>
                    <td>
                      <input class="input track menu-name-input" data-init="{{ $it['name'] ?? '' }}" type="text" name="items[{{ $cat }}][{{ $i }}][name]" value="{{ old("items.$cat.$i.name", $it['name'] ?? '') }}">
                    </td>
                    <td>
                      <input class="input track menu-desc-input" data-init="{{ $it['desc'] ?? '' }}" type="text" name="items[{{ $cat }}][{{ $i }}][desc]" value="{{ old("items.$cat.$i.desc", $it['desc'] ?? '') }}" placeholder="Description">
                    </td>
                    <td style="text-align:right">
                      <div class="price-wrap">
                        <input class="input price track" data-init="{{ number_format((float)($it['price'] ?? 0),2,'.','') }}" type="number" step="0.01" name="items[{{ $cat }}][{{ $i }}][price]" value="{{ old("items.$cat.$i.price", $it['price'] ?? 0) }}">
                        <button type="button" class="icon-btn remove-item" title="Remove item" aria-label="Remove item">−</button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px">
        <button class="btn" type="submit">Save Menu</button>
      </div>
    </form>
  
  <template id="rowTpl">
    <tr>
      <td>
        <input class="input track key-inp" placeholder="unique-key" name="KEY_PLACEHOLDER" value="">
      </td>
      <td>
        <input class="input track name-inp menu-name-input" placeholder="Item name" name="NAME_PLACEHOLDER" value="">
      </td>
      <td>
        <input class="input track desc-inp menu-desc-input" placeholder="Description" name="DESC_PLACEHOLDER" value="">
      </td>
      <td style="text-align:right">
        <div class="price-wrap">
          <input class="input price track" type="number" step="0.01" placeholder="0.00" name="PRICE_PLACEHOLDER" value="">
          <button type="button" class="icon-btn remove-item" title="Remove item" aria-label="Remove item">−</button>
        </div>
      </td>
    </tr>
  </template>

  <template id="catTpl">
    <div class="card new-cat"><div class="card-body">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <h2 style="margin:0 0 8px"><input class="input" style="max-width:360px" placeholder="Category name" value="New Category" data-cat-name data-prev="New Category"></h2>
        <div style="display:flex;gap:8px;align-items:center">
          <button type="button" class="icon-btn add-item" data-cat="New Category" title="Add item">+</button>
          <button type="button" class="icon-btn remove-cat" title="Remove category" aria-label="Remove category">−</button>
        </div>
      </div>
      <table data-cat="New Category"><thead><tr><th style="width:140px">Key</th><th style="width:32%">Name</th><th style="width:34%">Description</th><th style="width:120px;text-align:right">Price</th></tr></thead><tbody></tbody></table>
    </div></div>
  </template>

  <script>
    (function(){
      const form = document.getElementById('menuForm');
      const chg = document.getElementById('chgCount');
      const rowTpl = document.getElementById('rowTpl');
      const catTpl = document.getElementById('catTpl');

      function recalcChanges(){
        const rows = new Map();
        document.querySelectorAll('tr[data-row]').forEach(tr => {
          const id = tr.getAttribute('data-row');
          const inputs = tr.querySelectorAll('.track');
          let changed = false;
          inputs.forEach(inp => {
            const init = inp.getAttribute('data-init');
            if (init !== null) {
              if (String(inp.value) !== String(init)) changed = true;
            }
          });
          rows.set(id, changed);
        });
        // Count also new inputs without data-init (new rows)
        document.querySelectorAll('tr:not([data-row]) .track').forEach(inp => {
          const tr = inp.closest('tr');
          if (tr) rows.set(tr, true);
        });
        let total = 0; rows.forEach(v => { if (v) total++; });
        chg.textContent = String(total);
      }

      document.addEventListener('input', (e) => {
        if (e.target.classList.contains('track')) recalcChanges();
        if (e.target.matches('[data-cat-name]')) {
          // update data-cat on add-item link and table dataset
          const card = e.target.closest('.new-cat');
          const name = e.target.value.trim();
          const link = card.querySelector('.add-item');
          const table = card.querySelector('table');
          link.setAttribute('data-cat', name);
          table.setAttribute('data-cat', name);
        }
      });

      document.querySelectorAll('.add-item').forEach(a => a.addEventListener('click', addItemHandler));
      const addCatBtn = document.getElementById('addCat');
      if (addCatBtn) addCatBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const node = catTpl.content.cloneNode(true);
        const container = document.querySelector('.cats') || document.querySelector('form#menuForm');
        container.appendChild(node);
        // bind events for the newly added elements
        const last = container.querySelector('.new-cat:last-of-type');
        if (last) {
          const link = last.querySelector('.add-item');
          if (link) link.addEventListener('click', addItemHandler);
          const rc = last.querySelector('.remove-cat');
          if (rc) rc.addEventListener('click', removeCatHandler);
          const name = last.querySelector('[data-cat-name]');
          if (name) name.addEventListener('input', catRenameHandler);
        }
      });

      function addItemHandler(e){
        e.preventDefault();
        const card = this.closest('.card');
        let cat = this.getAttribute('data-cat') || '';
        const table = document.querySelector(`table[data-cat="${CSS.escape(cat)}"]`) || card.querySelector('table');
        const nameInput = card.querySelector('[data-cat-name]');
        const catFromTable = table?.getAttribute('data-cat');
        const catFromInput = nameInput?.value?.trim();
        if (!cat) cat = catFromTable || catFromInput || 'New Category';
        // Ensure link and table are synced
        this.setAttribute('data-cat', cat);
        if (table) table.setAttribute('data-cat', cat);
        const tbody = table.querySelector('tbody');
        const idx = tbody.querySelectorAll('tr').length;
        const row = rowTpl.content.cloneNode(true);
        // Set proper names
        row.querySelector('input[placeholder="unique-key"]').setAttribute('name', `items[${cat}][${idx}][key]`);
        row.querySelector('input[placeholder="Item name"]').setAttribute('name', `items[${cat}][${idx}][name]`);
        row.querySelector('input[placeholder="Description"]').setAttribute('name', `items[${cat}][${idx}][desc]`);
        row.querySelector('input[placeholder="0.00"]').setAttribute('name', `items[${cat}][${idx}][price]`);
        const node = row.cloneNode(true);
        // Bind name -> auto key slugging for convenience
        const nameInp = node.querySelector('.name-inp');
        const keyInp  = node.querySelector('.key-inp');
        if (nameInp && keyInp) {
          nameInp.addEventListener('input', () => {
            if ((keyInp.value || '').trim() !== '') return;
            const slug = (nameInp.value || '')
              .toLowerCase()
              .replace(/[^a-z0-9]+/g,'-')
              .replace(/^-+|-+$/g,'');
            keyInp.value = slug;
          });
        }
        tbody.appendChild(node);
        const rem = node.querySelector('.remove-item');
        if (rem) rem.addEventListener('click', removeItemHandler);
        recalcChanges();
      }

      // Remove item/category handlers
      function removeItemHandler(e){
        e.preventDefault();
        const tr = this.closest('tr');
        if (!tr) return;
        const name = tr.querySelector('input[name$="[name]"]')?.value?.trim() || tr.querySelector('td')?.textContent?.trim() || 'this item';
        if (!confirm(`Are you sure you want to remove \"${name}\"?`)) return;
        tr.remove();
        recalcChanges();
      }
      function removeCatHandler(e){
        e.preventDefault();
        const card = this.closest('.card');
        if (!card) return;
        const nameInput = card.querySelector('[data-cat-name]');
        const catName = nameInput ? (nameInput.value || 'this category') : (card.querySelector('h2')?.textContent?.trim() || 'this category');
        const itemsCount = card.querySelectorAll('tbody tr').length;
        const msg = itemsCount > 0 ? `Are you sure you want to remove "${catName}" and its ${itemsCount} item(s)?` : `Are you sure you want to remove "${catName}"?`;
        if (!confirm(msg)) return;
        card.remove();
        recalcChanges();
      }
      document.querySelectorAll('.remove-item').forEach(a => a.addEventListener('click', removeItemHandler));
      document.querySelectorAll('.remove-cat').forEach(a => a.addEventListener('click', removeCatHandler));

      // Category rename: update input names inside its table
      function catRenameHandler(e){
        const inp = e.target;
        const newName = inp.value.trim();
        const oldName = inp.getAttribute('data-prev') || '';
        if (!newName || newName === oldName) return;
        const card = inp.closest('.card');
        const table = card.querySelector('table');
        table.setAttribute('data-cat', newName);
        const inputs = table.querySelectorAll('input[name]');
        inputs.forEach(x => {
          const n = x.getAttribute('name');
          const search = `items[${oldName}]`;
          const repl = `items[${newName}]`;
          if (n.includes(search)) x.setAttribute('name', n.replace(search, repl));
        });
        const add = card.querySelector('.add-item');
        if (add) add.setAttribute('data-cat', newName);
        inp.setAttribute('data-prev', newName);
      }
      document.querySelectorAll('[data-cat-name]').forEach(inp => inp.addEventListener('input', catRenameHandler));

      // Client-side duplicate key validation
      form.addEventListener('submit', (e) => {
        const cats = {};
        document.querySelectorAll('table').forEach(tbl => {
          const cat = tbl.getAttribute('data-cat') || '';
          if (!cats[cat]) cats[cat] = new Set();
          const keys = tbl.querySelectorAll('input[name$="[key]"]');
          keys.forEach(k => {
            const val = (k.value || '').trim();
            if (!val) return;
            if (cats[cat].has(val)) {
              e.preventDefault();
              alert(`Duplicate key in category "${cat}": ${val}`);
            }
            cats[cat].add(val);
          });
        });
      });

      function init(){
        recalcChanges();
      }
      init();
    })();
  </script>
  </div>
</body>
</html>
