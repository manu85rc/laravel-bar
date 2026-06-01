<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Nuevo Pedido</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    (function() {
      var theme = '{{ auth()->check() ? auth()->user()->theme : 'light' }}';
      var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      if (isDark) document.documentElement.classList.add('dark');
      window.__userTheme = theme;
    })();
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:     #f8f6f1;
      --bg-2:   #f0ece4;
      --bg-3:   #ffffff;
      --bg-4:   #e8e2d8;
      --bg-5:   #ddd6ca;
      --gold:        #c8922a;
      --gold-light:  #dba34e;
      --gold-dim:    #b07a1e;
      --gold-glow:   rgba(200,146,42,0.12);
      --gold-subtle: rgba(200,146,42,0.06);
      --text-1: #1a1816;
      --text-2: #5a4e3c;
      --text-3: #8a7d68;
      --text-4: #b0a594;
      --border:     rgba(200,146,42,0.2);
      --border-dim: rgba(0,0,0,0.08);
      --green:      #2d8f5e;
      --green-bg:   rgba(45,143,94,0.08);
      --green-text: #2d8f5e;
      --red:        #c0392b;
      --red-bg:     rgba(192,57,43,0.08);
      --red-text:   #c0392b;
      --radius:   8px;
      --radius-lg: 14px;
    }
    .dark {
      --bg:     #0e0a05;
      --bg-2:   #16100a;
      --bg-3:   #1e160d;
      --bg-4:   #271d11;
      --bg-5:   #2f2415;
      --gold-glow:   rgba(200,146,42,0.15);
      --gold-subtle: rgba(200,146,42,0.08);
      --text-1: #f0e6d3;
      --text-2: #b89e7a;
      --text-3: #7a6a52;
      --text-4: #4a3d2a;
      --border:     rgba(200,146,42,0.12);
      --border-dim: rgba(240,230,211,0.06);
      --green:      #4caf7d;
      --green-bg:   rgba(76,175,125,0.12);
      --green-text: #6fcf97;
      --red:        #e05252;
      --red-bg:     rgba(224,82,82,0.12);
      --red-text:   #ff8080;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      color: var(--text-2);
    }

    .back-link {
      position: fixed; top: 16px; left: 16px;
      font-size: 13px; color: var(--text-3);
      text-decoration: none; display: flex; align-items: center; gap: 6px;
      background: var(--bg-3); padding: 7px 14px;
      border: 1px solid var(--border-dim); border-radius: var(--radius);
      transition: all 0.15s;
    }
    .back-link:hover { border-color: var(--border); color: var(--text-2); }

    h1.app-title {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 600;
      color: var(--gold);
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    h1.app-title i { font-size: 18px; }

    .pos {
      display: grid;
      grid-template-columns: 1fr 290px;
      width: 100%;
      max-width: 1050px;
      border: 1px solid var(--border-dim);
      border-radius: var(--radius-lg);
      overflow: hidden;
      background: var(--bg-2);
      min-height: 640px;
    }

    .left { padding: 16px; background: var(--bg-2); }

    .top-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
    }

    .table-select {
      flex: 1;
      padding: 9px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13px;
      background: var(--bg-4);
      color: var(--text-1);
      cursor: pointer;
      font-family: inherit;
      outline: none;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23b89e7a'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 28px;
    }
    .table-select:focus { border-color: var(--gold); }

    .waiter-tag {
      font-size: 12px;
      color: var(--text-2);
      background: var(--bg-3);
      border: 1px solid var(--border-dim);
      border-radius: var(--radius);
      padding: 7px 10px;
      white-space: nowrap;
    }

    .search-box {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13px;
      background: var(--bg-4);
      color: var(--text-1);
      margin-bottom: 10px;
      outline: none;
      transition: border-color 0.15s;
      font-family: inherit;
    }
    .search-box:focus { border-color: var(--gold); }
    .search-box::placeholder { color: var(--text-4); }

    .cats {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .cat-btn {
      padding: 6px 14px;
      border: 1px solid var(--border-dim);
      border-radius: 20px;
      font-size: 12px;
      cursor: pointer;
      background: var(--bg-3);
      color: var(--text-3);
      font-family: inherit;
      transition: all 0.15s;
    }
    .cat-btn.active { background: var(--gold); border-color: var(--gold); color: #1a1a18; font-weight: 500; }
    .cat-btn:hover:not(.active) { border-color: var(--border); color: var(--text-2); }

    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 8px;
    }

    .item-btn {
      border: 1px solid var(--border-dim);
      border-radius: var(--radius);
      padding: 12px 10px;
      cursor: pointer;
      background: var(--bg-3);
      text-align: left;
      font-family: inherit;
      transition: all 0.12s;
    }
    .item-btn:hover { border-color: var(--gold); background: var(--gold-subtle); transform: translateY(-1px); }
    .item-btn:active { transform: scale(0.97); }

    .item-name { font-size: 13px; font-weight: 500; color: var(--text-1); line-height: 1.3; margin-bottom: 4px; }
    .item-price { font-size: 12px; color: var(--gold); font-weight: 500; }

    .right {
      background: var(--bg-3);
      border-left: 1px solid var(--border-dim);
      display: flex;
      flex-direction: column;
    }

    .order-header {
      padding: 16px;
      border-bottom: 1px solid var(--border-dim);
    }
    .order-title { font-size: 14px; font-weight: 500; color: var(--text-1); }
    .order-subtitle { font-size: 12px; color: var(--text-3); margin-top: 2px; }

    .order-items { flex: 1; overflow-y: auto; padding: 8px 12px; }

    .order-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      min-height: 180px;
      color: var(--text-4);
      font-size: 13px;
      gap: 8px;
    }
    .order-empty i { font-size: 28px; }

    .order-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 0;
      border-bottom: 1px solid var(--border-dim);
    }
    .order-row:last-child { border-bottom: none; }

    .order-row-name { flex: 1; font-size: 13px; color: var(--text-1); line-height: 1.3; }

    .qty-ctrl { display: flex; align-items: center; gap: 4px; }
    .qty-btn {
      width: 24px; height: 24px;
      border: 1px solid var(--border);
      border-radius: 6px;
      cursor: pointer;
      background: var(--bg-4);
      color: var(--text-2);
      font-size: 13px;
      display: flex; align-items: center; justify-content: center;
      font-family: inherit;
      transition: background 0.1s;
    }
    .qty-btn:hover { background: var(--bg-5); color: var(--text-1); }
    .qty-num { font-size: 13px; font-weight: 500; color: var(--text-1); min-width: 18px; text-align: center; }
    .row-price { font-size: 12px; color: var(--gold); min-width: 52px; text-align: right; font-weight: 500; }

    .order-footer { padding: 14px; border-top: 1px solid var(--border-dim); }

    .totals { margin-bottom: 12px; }
    .total-row {
      display: flex; justify-content: space-between;
      font-size: 12px; color: var(--text-3); margin-bottom: 4px;
    }
    .total-final {
      display: flex; justify-content: space-between;
      font-size: 15px; font-weight: 600; color: var(--gold-light);
      padding-top: 8px;
      border-top: 1px solid var(--border);
      margin-top: 4px;
    }

    .btn-confirm {
      width: 100%; padding: 10px;
      background: var(--gold);
      border: none; border-radius: var(--radius);
      color: #1a1a18; font-size: 13px; font-weight: 600;
      cursor: pointer; transition: background 0.15s;
      font-family: inherit;
      margin-bottom: 6px;
    }
    .btn-confirm:hover { background: var(--gold-light); }
    .btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }

    .btn-cancel {
      width: 100%; padding: 8px;
      background: var(--bg-3);
      border: 1px solid var(--border-dim);
      border-radius: var(--radius);
      color: var(--text-3); font-size: 12px;
      cursor: pointer; transition: all 0.15s;
      font-family: inherit;
    }
    .btn-cancel:hover { border-color: var(--border); color: var(--text-2); }

    .toast {
      position: fixed; bottom: 24px; left: 50%;
      transform: translateX(-50%);
      background: var(--bg-3); color: var(--gold-light);
      font-size: 13px; padding: 10px 22px;
      border-radius: 99px; opacity: 0;
      transition: opacity 0.3s; pointer-events: none;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
      z-index: 1000;
      border: 1px solid var(--border);
    }
    .toast.show { opacity: 1; }
    .toast.error { background: var(--red-bg); color: var(--red-text); border-color: rgba(224,82,82,0.2); }

    .piso-selector{display:flex;gap:4px;margin-bottom:8px;flex-wrap:wrap;}
    .piso-btn{padding:5px 12px;border-radius:6px;border:1px solid var(--border-dim);font-size:12px;cursor:pointer;background:var(--bg-3);color:var(--text-3);font-family:inherit;transition:all .12s;}
    .piso-btn.active{background:var(--gold);border-color:var(--gold);color:#1a1a18;font-weight:500;}
    .piso-btn:hover:not(.active){border-color:var(--border);color:var(--text-2);}
    .plano-btn{width:34px;height:34px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-3);color:var(--gold);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .12s;}
    .plano-btn:hover{border-color:var(--gold);background:var(--gold-subtle);}
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);}
    .modal-content{background:var(--bg-3);border-radius:var(--radius-lg);width:90%;max-width:780px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border-dim);font-size:15px;font-weight:600;color:var(--text-1);}
    .modal-close{background:none;border:none;font-size:22px;color:var(--text-3);cursor:pointer;padding:0 4px;line-height:1;}
    .modal-close:hover{color:var(--text-1);}
    #modal-pisos{padding:10px 18px 0;}
    #modal-canvas{position:relative;width:100%;min-height:380px;background:var(--bg-4);background-image:linear-gradient(rgba(0,0,0,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,0.04) 1px,transparent 1px);background-size:20px 20px;overflow:hidden;border-radius:0 0 var(--radius-lg) var(--radius-lg);margin-top:10px;}
    .dark #modal-canvas{background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);}
    #modal-canvas .mesa-shape{position:absolute;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid var(--border-dim);user-select:none;transition:box-shadow .15s;}
    #modal-canvas .mesa-shape:hover{box-shadow:0 0 0 3px var(--gold),0 0 12px rgba(200,146,42,0.3);z-index:10;}
    #modal-canvas .mesa-shape.circle{border-radius:50%;}
    #modal-canvas .mesa-shape.square{border-radius:6px;}
    #modal-canvas .mesa-shape.occupied{background:var(--gold-glow);border-color:var(--gold-dim);color:var(--gold-light);}
    #modal-canvas .mesa-shape.free{background:var(--bg-4);color:var(--text-3);border-color:var(--border-dim);}
    #modal-canvas .mesa-shape.reserved{background:var(--blue-bg);border-color:rgba(99,164,216,0.2);color:var(--blue-text);}
    #modal-canvas .mesa-label{font-family:'Playfair Display',serif;font-size:13px;font-weight:700;pointer-events:none;line-height:1;}
    #modal-canvas .mesa-pax{position:absolute;top:4px;right:6px;font-size:8px;opacity:.7;pointer-events:none;}
    .no-results { font-size: 13px; color: var(--text-4); padding: 12px 0; }

    @media (max-width: 700px) {
      .pos { grid-template-columns: 1fr; }
      .right { border-left: none; border-top: 1px solid var(--border-dim); }
      body { padding: 10px; }
      h1.app-title { font-size: 17px; }
    }
  </style>
</head>
<body>

  <a href="{{ route('pedidos.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Volver
  </a>

  <h1 class="app-title"><i class="fa-solid fa-utensils"></i> Nuevo Pedido</h1>

  <div class="pos" id="app"></div>
  <div class="toast" id="toast"></div>

  <div class="modal-overlay" id="plano-modal" style="display:none">
    <div class="modal-content">
      <div class="modal-header">
        <span>Plano de Mesas</span>
        <button class="modal-close" onclick="cerrarPlanoModal()">&times;</button>
      </div>
      <div class="piso-selector" id="modal-pisos"></div>
      <div class="plano-canvas" id="modal-canvas"></div>
    </div>
  </div>

  <script>
    const MENU = @json($menu);
    const TABLES = @json($tables);
    const PISOS = @json($pisos);
    const PLANO_MESAS = @json($planoMesas);

    let state = {
      cat: Object.keys(MENU)[0] || '',
      order: {},
      table: (TABLES[0] && TABLES[0].id) || null,
      search: '',
      piso: PISOS[0] || ''
    };

    function fmt(n) { return '$' + n.toLocaleString('es-AR'); }

    function addItem(item) {
      const key = item.name;
      if (!state.order[key]) state.order[key] = { ...item, qty: 0 };
      state.order[key].qty++;
      render();
    }

    function changeQty(name, delta) {
      if (!state.order[name]) return;
      state.order[name].qty += delta;
      if (state.order[name].qty <= 0) delete state.order[name];
      render();
    }

    function clearOrder() {
      state.order = {};
      render();
    }

    function showToast(msg, isError) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.className = 'toast' + (isError ? ' error' : '');
      setTimeout(function () { t.classList.add('show'); }, 10);
      setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    async function confirmOrder() {
      const items = Object.values(state.order);
      if (!items.length) return;

      const btn = document.getElementById('confirm-btn');
      if (btn) btn.disabled = true;

      const selectedMesaId = parseInt(document.getElementById('table-sel').value);

      try {
        const res = await fetch('{{ route('pedidos.store') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            mesa_id: selectedMesaId,
            productos: items.map(function (i) { return { id: i.id, cantidad: i.qty }; })
          })
        });

        if (res.ok) {
          const data = await res.json();
          showToast('Pedido enviado a cocina');
          state.order = {};
          setTimeout(function () {
            window.location.href = '/pedidos/' + data.pedido.id;
          }, 900);
        } else {
          var msg = 'Error al crear el pedido (c\u00f3digo ' + res.status + ')';
          try {
            const err = await res.json();
            msg = err.message || msg;
            if (err.errors) {
              msg = Object.values(err.errors).flat().join(', ');
            }
          } catch (_) {
            try {
              const text = await res.text();
              if (text) msg = text.substring(0, 200);
            } catch (_) {}
          }
          showToast(msg, true);
          if (btn) btn.disabled = false;
        }
      } catch (e) {
        console.error('Error al enviar pedido:', e);
        var errorMsg = 'Error de conexi\u00f3n';
        if (e instanceof TypeError && e.message.includes('JSON')) {
          errorMsg = 'Error del servidor: respuesta inv\u00e1lida';
        } else if (e instanceof TypeError && e.message.includes('fetch')) {
          errorMsg = 'Error de red: verifique la conexi\u00f3n';
        }
        showToast(errorMsg, true);
        if (btn) btn.disabled = false;
      }
    }

    function getFiltered() {
      if (!state.search) return MENU[state.cat] || [];
      const allItems = Object.values(MENU).flat();
      return allItems.filter(function (i) { return i.name.toLowerCase().includes(state.search.toLowerCase()); });
    }

    function calcTotals() {
      const items = Object.values(state.order);
      const sub = items.reduce(function (s, i) { return s + i.price * i.qty; }, 0);
      const iva = Math.round(sub * 0.21);
      return { sub: sub, iva: iva, total: sub + iva };
    }

    function getTableLabel(id) {
      var t = TABLES.find(function (t) { return t.id === id; });
      return t ? t.label : '';
    }

    function render() {
      const app = document.getElementById('app');
      if (!app) return;
      const { sub, iva, total } = calcTotals();
      const orderItems = Object.values(state.order);
      const filtered = getFiltered();

      var filteredTables = TABLES.filter(function (t) { return !state.piso || t.piso === state.piso; });

      app.innerHTML =
        '<div class="left">' +
          (PISOS.length > 1
            ? '<div class="piso-selector">' +
                PISOS.map(function (p) {
                  return '<button class="piso-btn' + (p === state.piso ? ' active' : '') + '" data-piso="' + p + '">' + p + '</button>';
                }).join('') +
              '</div>'
            : ''
          ) +
          '<div class="top-bar">' +
            '<select class="table-select" id="table-sel">' +
              filteredTables.map(function (t) {
                return '<option value="' + t.id + '"' + (t.id === state.table ? ' selected' : '') + '>' + t.label + '</option>';
              }).join('') +
            '</select>' +
            '<button class="plano-btn" onclick="abrirPlanoModal()" title="Ver plano"><i class="fa-solid fa-table-cells"></i></button>' +
            '<span class="waiter-tag"><i class="fa-solid fa-user" style="font-size:12px;margin-right:4px"></i>{{ auth()->user()->name }}</span>' +
          '</div>' +

          '<input class="search-box" type="text" placeholder="Buscar \u00edtem\u2026" id="search-inp" value="' + state.search + '">' +

          '<div class="cats">' +
            Object.keys(MENU).map(function (c) {
              return '<button class="cat-btn' + (c === state.cat ? ' active' : '') + '" data-cat="' + c + '">' + c + '</button>';
            }).join('') +
          '</div>' +

          '<div class="items-grid">' +
            filtered.map(function (item) {
              return '<button class="item-btn" data-item=\'' + JSON.stringify(item).replace(/'/g, '&#39;') + '\'>' +
                '<div class="item-name">' + item.name + '</div>' +
                '<div class="item-price">' + fmt(item.price) + '</div>' +
              '</button>';
            }).join('') +
            (filtered.length === 0 ? '<p class="no-results">Sin resultados para "' + state.search + '"</p>' : '') +
          '</div>' +
        '</div>' +

        '<div class="right">' +
          '<div class="order-header">' +
            '<div class="order-title">' +
              '<i class="fa-solid fa-receipt" style="font-size:13px;margin-right:5px"></i>' + getTableLabel(state.table) +
            '</div>' +
            '<div class="order-subtitle">' +
              (orderItems.length ? orderItems.reduce(function (s, i) { return s + i.qty; }, 0) + ' \u00edtem(s)' : 'Sin \u00edtems a\u00fan') +
            '</div>' +
          '</div>' +

          '<div class="order-items">' +
            (orderItems.length === 0
              ? '<div class="order-empty"><i class="fa-solid fa-basket-shopping"></i><span>El pedido est\u00e1 vac\u00edo</span></div>'
              : orderItems.map(function (item) {
                  return '<div class="order-row">' +
                    '<div class="order-row-name">' + item.name + '</div>' +
                    '<div class="qty-ctrl">' +
                      '<button class="qty-btn" data-name="' + item.name + '" data-d="-1"><i class="fa-solid fa-minus fa-xs"></i></button>' +
                      '<span class="qty-num">' + item.qty + '</span>' +
                      '<button class="qty-btn" data-name="' + item.name + '" data-d="1"><i class="fa-solid fa-plus fa-xs"></i></button>' +
                    '</div>' +
                    '<div class="row-price">' + fmt(item.price * item.qty) + '</div>' +
                  '</div>';
                }).join('')
            ) +
          '</div>' +

          (orderItems.length > 0
            ? '<div class="order-footer">' +
                '<div class="totals">' +
                  '<div class="total-row"><span>Subtotal</span><span>' + fmt(sub) + '</span></div>' +
                  '<div class="total-row"><span>IVA 21%</span><span>' + fmt(iva) + '</span></div>' +
                  '<div class="total-final"><span>Total</span><span>' + fmt(total) + '</span></div>' +
                '</div>' +
                '<button class="btn-confirm" id="confirm-btn">' +
                  '<i class="fa-solid fa-paper-plane" style="font-size:12px;margin-right:5px"></i>Enviar a cocina' +
                '</button>' +
                '<button class="btn-cancel" id="cancel-btn">Cancelar pedido</button>' +
              '</div>'
            : ''
          ) +
        '</div>';

      document.getElementById('table-sel').onchange = function (e) {
        state.table = parseInt(e.target.value);
        render();
      };

      var searchInp = document.getElementById('search-inp');
      if (searchInp) {
        searchInp.oninput = function (e) { state.search = e.target.value; render(); };

      }

      app.querySelectorAll('.piso-btn').forEach(function (b) {
        b.onclick = function () { state.piso = b.dataset.piso; if (!filteredTables.find(function(t) { return t.id === state.table; })) { state.table = (filteredTables[0] && filteredTables[0].id) || null; } render(); };
      });
      app.querySelectorAll('.cat-btn').forEach(function (b) {
        b.onclick = function () { state.cat = b.dataset.cat; state.search = ''; render(); };
      });
      app.querySelectorAll('.item-btn').forEach(function (b) {
        b.onclick = function () { addItem(JSON.parse(b.dataset.item)); };
      });
      app.querySelectorAll('.qty-btn').forEach(function (b) {
        b.onclick = function () { changeQty(b.dataset.name, parseInt(b.dataset.d)); };
      });

      var confirmBtn = document.getElementById('confirm-btn');
      var cancelBtn = document.getElementById('cancel-btn');
      if (confirmBtn) confirmBtn.onclick = confirmOrder;
      if (cancelBtn) cancelBtn.onclick = clearOrder;
    }

    // ===== PLANO MODAL =====
    var modalPiso = state.piso;

    function renderPlanoModal() {
      var filtered = PLANO_MESAS.filter(function(m) { return !modalPiso || m.piso === modalPiso; });
      var pisosEl = document.getElementById('modal-pisos');
      var canvasEl = document.getElementById('modal-canvas');

      pisosEl.innerHTML = PISOS.map(function(p) {
        return '<button class="piso-btn' + (p === modalPiso ? ' active' : '') + '" data-mpiso="' + p + '">' + p + '</button>';
      }).join('');

      pisosEl.querySelectorAll('.piso-btn').forEach(function(b) {
        b.onclick = function() {
          modalPiso = b.dataset.mpiso;
          renderPlanoModal();
        };
      });

      canvasEl.innerHTML = filtered.map(function(m) {
        var cls = 'mesa-shape ' + m.estado + ' ' + m.forma;
        return '<div class="' + cls + '" data-mid="' + m.id + '" style="left:' + m.pos_x + 'px;top:' + m.pos_y + 'px;width:' + m.ancho + 'px;height:' + m.alto + 'px;cursor:pointer">' +
          '<div class="mesa-pax"><i class="fa-solid fa-user"></i> ' + m.capacidad + '</div>' +
          '<div class="mesa-label">' + m.numero + '</div>' +
        '</div>';
      }).join('');

      canvasEl.querySelectorAll('.mesa-shape').forEach(function(el) {
        el.onclick = function() {
          var id = parseInt(el.dataset.mid);
          var mesa = PLANO_MESAS.find(function(m) { return m.id === id; });
          if (mesa) {
            state.table = mesa.id;
            state.piso = mesa.piso;
          }
          cerrarPlanoModal();
          render();
        };
      });
    }

    function abrirPlanoModal() {
      modalPiso = state.piso;
      document.getElementById('plano-modal').style.display = 'flex';
      renderPlanoModal();
    }

    function cerrarPlanoModal() {
      document.getElementById('plano-modal').style.display = 'none';
    }

    render();
  </script>
</body>
</html>