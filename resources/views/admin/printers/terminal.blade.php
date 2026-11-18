@extends('layouts.admin')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="page-top">
    <div>
        <div class="page-title">Terminal de Impresion</div>
        <div class="page-sub">Conecta las impresoras Bluetooth y deja esta pagina abierta</div>
    </div>
    <a href="{{ route('admin.printers.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if($printers->isEmpty())
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fa-solid fa-print" style="font-size:32px;color:var(--text-4);margin-bottom:12px;display:block"></i>
                No hay impresoras Bluetooth activas. <a href="{{ route('admin.printers.create') }}" style="color:var(--gold)">Crear una</a>
            </div>
        </div>
    </div>
@else
    <div class="terminal-grid" x-data="printTerminal()">
        <template x-for="printer in printers" :key="printer.id">
            <div class="terminal-card">
                <div class="terminal-card-head">
                    <div class="terminal-card-info">
                        <div class="terminal-card-icon" :class="printer.connected ? 'connected' : ''">
                            <i class="fa-solid fa-print"></i>
                        </div>
                        <div>
                            <div class="terminal-card-name" x-text="printer.nombre"></div>
                            <div class="terminal-card-meta">
                                <span class="badge b-gray" x-text="printer.baud_rate + ' baud'"></span>
                                <span class="badge" :class="printer.connected ? 'b-green' : 'b-red'">
                                    <span class="bdot"></span>
                                    <span x-text="printer.connected ? 'Conectada' : 'Desconectada'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="terminal-card-actions">
                        <button x-show="printer.connected && !printer.testing"
                                class="btn btn-ghost btn-sm"
                                @click="testPrint(printer)"
                                style="color:var(--green-text)">
                            <i class="fa-solid fa-flask"></i> Test
                        </button>
                        <button x-show="printer.testing"
                                class="btn btn-ghost btn-sm"
                                disabled>
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </button>
                        <button x-show="!printer.connected && !printer.connecting"
                                class="btn btn-gold btn-sm"
                                @click="connectPrinter(printer)">
                            <i class="fa-solid fa-plug"></i> Conectar
                        </button>
                        <button x-show="printer.connecting"
                                class="btn btn-ghost btn-sm"
                                disabled>
                            <i class="fa-solid fa-spinner fa-spin"></i> Conectando...
                        </button>
                        <button x-show="printer.connected"
                                class="btn btn-ghost btn-sm"
                                @click="disconnectPrinter(printer)">
                            <i class="fa-solid fa-xmark"></i> Desconectar
                        </button>
                    </div>
                </div>
                <div class="terminal-card-body" x-show="printer.connected">
                    <div class="terminal-stats">
                        <div class="terminal-stat">
                            <div class="terminal-stat-val" x-text="printer.jobsPrinted">0</div>
                            <div class="terminal-stat-label">Impresos</div>
                        </div>
                        <div class="terminal-stat">
                            <div class="terminal-stat-val" x-text="printer.errors">0</div>
                            <div class="terminal-stat-label">Errores</div>
                        </div>
                        <div class="terminal-stat">
                            <div class="terminal-stat-val" x-text="printer.lastPrint || '—'"></div>
                            <div class="terminal-stat-label">Ultima impresion</div>
                        </div>
                    </div>
                </div>
                <div class="terminal-card-error" x-show="printer.errorMsg" x-text="printer.errorMsg"></div>
                <div class="terminal-card-log" x-show="printer.log.length > 0">
                    <template x-for="(entry, i) in printer.log.slice(-5).reverse()" :key="i">
                        <div class="terminal-log-entry" :class="'log-' + entry.type" x-text="entry.text"></div>
                    </template>
                </div>
            </div>
        </template>
    </div>
@endif

<style>
.terminal-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.terminal-card {
    background: var(--bg-3);
    border: 1px solid var(--border-dim);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: border-color .2s;
}
.terminal-card:hover {
    border-color: var(--border);
}
.terminal-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    gap: 16px;
}
.terminal-card-info {
    display: flex;
    align-items: center;
    gap: 14px;
}
.terminal-card-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius);
    background: var(--bg-4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--text-3);
    transition: all .3s;
    flex-shrink: 0;
}
.terminal-card-icon.connected {
    background: var(--green-bg);
    color: var(--green-text);
    box-shadow: 0 0 12px rgba(45, 143, 94, 0.2);
}
.terminal-card-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-1);
}
.terminal-card-meta {
    display: flex;
    gap: 8px;
    margin-top: 4px;
}
.terminal-card-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}
.terminal-card-body {
    padding: 0 20px 16px;
}
.terminal-stats {
    display: flex;
    gap: 24px;
    padding: 14px 18px;
    background: var(--bg-4);
    border-radius: var(--radius);
}
.terminal-stat-val {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-1);
}
.terminal-stat-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--text-3);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-top: 2px;
}
.terminal-card-error {
    padding: 10px 20px;
    background: var(--red-bg);
    color: var(--red-text);
    font-size: 12px;
    border-top: 1px solid var(--red);
}
.terminal-card-log {
    padding: 8px 20px 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.terminal-log-entry {
    font-size: 11px;
    font-family: monospace;
    padding: 3px 8px;
    border-radius: 4px;
}
.log-print {
    background: var(--green-bg);
    color: var(--green-text);
}
.log-error {
    background: var(--red-bg);
    color: var(--red-text);
}
.log-info {
    background: var(--blue-bg);
    color: var(--blue-text);
}
.log-poll {
    background: var(--bg-4);
    color: var(--text-3);
}
@keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 0 0 rgba(45, 143, 94, 0.3); }
    50% { box-shadow: 0 0 0 6px rgba(45, 143, 94, 0); }
}
.terminal-card-icon.connected {
    animation: pulse-green 2s infinite;
}
</style>

<script>
function printTerminal() {
    return {
        printers: {!! $printersJson !!},

        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',

        async connectPrinter(printer) {
            if (!('serial' in navigator)) {
                printer.errorMsg = 'Web Serial no soportada. Use Chrome 117+.';
                return;
            }

            printer.connecting = true;
            printer.errorMsg = null;

            try {
                const bt = new BluetoothPrinter();
                await bt.requestPort();
                await bt.connect(printer.baud_rate);

                printer._instance = bt;
                printer.connected = true;
                printer.connecting = false;
                printer.log.push({ type: 'info', text: this.now() + ' Conectada a ' + printer.nombre });

                bt.port.addEventListener('disconnect', () => {
                    printer.connected = false;
                    printer._instance = null;
                    this.stopPoll(printer);
                    printer.log.push({ type: 'error', text: this.now() + ' Desconectada inesperadamente' });
                    printer.errorMsg = 'Conexion perdida. Reconecte la impresora.';
                });

                this.startPoll(printer);
            } catch (e) {
                printer.connecting = false;
                if (e.name === 'NotFoundError') {
                    printer.errorMsg = null;
                    printer.log.push({ type: 'info', text: this.now() + ' Seleccion cancelada' });
                } else {
                    printer.errorMsg = 'Error: ' + e.message;
                    printer.log.push({ type: 'error', text: this.now() + ' ' + e.message });
                }
            }
        },

        startPoll(printer) {
            this.stopPoll(printer);
            printer._pollTimer = setInterval(() => this.pollOnce(printer), 3000);
        },

        stopPoll(printer) {
            if (printer._pollTimer) {
                clearInterval(printer._pollTimer);
                printer._pollTimer = null;
            }
        },

        async pollOnce(printer) {
            if (!printer.connected || !printer._instance) return;
            try {
                const resp = await fetch('/print-queue?printer_id=' + printer.id, {
                    headers: { 'Accept': 'application/json' }
                });
                const jobs = await resp.json();
                if (jobs.length === 0) return;

                let printed = 0;
                for (const job of jobs) {
                    if (!job.pending) continue;
                    try {
                        await printer._instance.printTicket(job.ticket_data);
                        await fetch('/print-queue/complete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ id: job.id })
                        });
                        printed++;
                        printer.log.push({ type: 'print', text: this.now() + ' Job #' + job.id + ' impreso' });
                    } catch (e) {
                        printer.errors++;
                        printer.log.push({ type: 'error', text: this.now() + ' Error job #' + job.id + ': ' + e.message });
                    }
                }
                if (printed > 0) {
                    printer.jobsPrinted += printed;
                    printer.lastPrint = this.now();
                }
            } catch (e) {
                printer.errors++;
                printer.log.push({ type: 'error', text: this.now() + ' Error polling: ' + e.message });
            }
        },

        async testPrint(printer) {
            printer.testing = true;
            try {
                const resp = await fetch('{{ route("admin.printers.test-print") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ printer_id: printer.id })
                });
                const data = await resp.json();
                if (data.success) {
                    printer.log.push({ type: 'info', text: this.now() + ' Ticket de prueba enviado. Deberia imprimirse en breve.' });
                } else {
                    printer.log.push({ type: 'error', text: this.now() + ' Error: ' + (data.message || 'desconocido') });
                }
            } catch (e) {
                printer.log.push({ type: 'error', text: this.now() + ' Error test: ' + e.message });
            }
            printer.testing = false;
        },

        async disconnectPrinter(printer) {
            this.stopPoll(printer);
            if (printer._instance) {
                try { await printer._instance.disconnect(); } catch(e) {}
                printer._instance = null;
            }
            printer.connected = false;
            printer.log.push({ type: 'info', text: this.now() + ' Desconectada manualmente' });
        },

        now() {
            return new Date().toLocaleTimeString('es');
        }
    };
}
</script>
@endsection
