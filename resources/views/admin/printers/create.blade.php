@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Nueva Impresora</div>
        <div class="page-sub">Configura una impresora POS para imprimir pedidos</div>
    </div>
    <a href="{{ route('admin.printers.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.printers.store') }}" method="POST" x-data="printerForm()">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-input" value="{{ old('nombre') }}" placeholder="Ej: Cocina, Barra, Comandas..." required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Conexión</label>
                        <div class="btn-toggle">
                            <label class="toggle-option">
                                <input type="radio" name="conexion" value="usb" x-model="conexion" {{ old('conexion', 'usb') === 'bluetooth' ? '' : 'checked' }}>
                                <span class="toggle-label"><i class="fa-solid fa-usb"></i> USB</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="conexion" value="bluetooth" x-model="conexion" {{ old('conexion') === 'bluetooth' ? 'checked' : '' }}>
                                <span class="toggle-label"><i class="fa-brands fa-bluetooth"></i> Bluetooth</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group full" x-show="conexion === 'bluetooth'" x-transition>
                        <label class="form-label">Impresora Bluetooth</label>
                        <div class="bluetooth-scan">
                            <div class="bt-detect-row">
                                <button type="button" class="btn btn-gold" @click="detectFromBrowser()">
                                    <i class="fa-brands fa-bluetooth"></i>
                                    Detectar desde navegador
                                </button>
                                <span class="form-hint" x-show="!detectedName">Requiere Chrome 117+. La impresora debe estar pareada en el SO primero.</span>
                            </div>
                            <div x-show="detectError" class="form-hint" style="color:var(--red)" x-text="detectError"></div>
                            <div x-show="detectedName" class="bt-detected">
                                <i class="fa-solid fa-circle-check" style="color:var(--green)"></i>
                                <span>Detectada: <strong x-text="detectedName"></strong></span>
                                <span class="bt-mac" x-text="detectedId"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo de impresión</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="cocina" {{ old('tipo') == 'cocina' ? 'selected' : '' }}>Cocina (solo comida)</option>
                            <option value="bebidas" {{ old('tipo') == 'bebidas' ? 'selected' : '' }}>Bebidas (solo bebidas)</option>
                            <option value="ambas" {{ old('tipo') == 'ambas' ? 'selected' : '' }}>Ambas (todo)</option>
                            <option value="factura" {{ old('tipo') == 'factura' ? 'selected' : '' }}>Factura (ticket completo)</option>
                        </select>
                        <span class="form-hint">Imprime solo comida, solo bebidas, todos, o ticket de factura</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="papel">Tamaño del papel</label>
                        <select name="papel" id="papel" class="form-select" required>
                            <option value="80mm" {{ old('papel', '80mm') == '80mm' ? 'selected' : '' }}>80 mm</option>
                            <option value="58mm" {{ old('papel') == '58mm' ? 'selected' : '' }}>58 mm</option>
                        </select>
                    </div>

                    <div class="form-group" x-show="conexion === 'usb'">
                        <label class="form-label" for="ruta_dispositivo">Ruta del dispositivo</label>
                        <input type="text" name="ruta_dispositivo" id="ruta_dispositivo" class="form-input"
                            x-model="devicePath"
                            placeholder="/dev/usb/lp0"
                            required>
                        <span class="form-hint">Ruta USB del dispositivo, ej: /dev/usb/lp0</span>
                        <div class="usb-detect-row" style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                            <button type="button" class="btn btn-ghost btn-sm" @click="detectUsb()" :disabled="usbDetecting">
                                <i class="fa-solid fa-usb"></i>
                                <span x-text="usbDetecting ? 'Detectando…' : 'Detectar'"></span>
                            </button>
                            <select class="form-select" style="width:auto;padding:6px 28px 6px 10px;font-size:12px;flex:0 1 auto;" x-show="usbDevices.length > 1" x-model="devicePath">
                                <template x-for="p in usbDevices" :key="p">
                                    <option :value="p" x-text="p"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="usbDetecting" class="form-hint"><i class="fa-solid fa-spinner fa-spin"></i> Detectando impresoras USB…</div>
                        <div x-show="!usbDetecting && usbDetectError" class="form-hint" style="color:var(--red)" x-text="usbDetectError"></div>
                        <div x-show="!usbDetecting && !usbDetectError && usbDevices.length === 0" class="form-hint">No se detectaron impresoras USB.</div>
                        <div x-show="!usbDetecting && !usbDetectError && usbMismatch" class="form-hint" style="color:var(--amber-text)">
                            Guardado «<strong x-text="usbStoredPath"></strong>» · detectado ahora «<strong x-text="usbDevices.join(', ')"></strong>».
                        </div>
                        <div x-show="!usbDetecting && !usbDetectError && usbDetected" class="bt-detected">
                            <i class="fa-solid fa-circle-check" style="color:var(--green)"></i>
                            <span>Detectado: <strong x-text="usbDevices.join(', ')"></strong></span>
                        </div>
                    </div>

                    <div class="form-group" x-show="conexion === 'bluetooth'">
                        <label class="form-label" for="ruta_dispositivo">Nombre del dispositivo</label>
                        <input type="text" name="ruta_dispositivo" id="ruta_dispositivo" class="form-input"
                            :value="detectedName"
                            x-model="detectedName"
                            placeholder="Nombre de la impresora"
                            required>
                        <span class="form-hint">Nombre Bluetooth de la impresora (para identificación)</span>
                    </div>

                    <div class="form-group" x-show="conexion === 'bluetooth'">
                        <label class="form-label" for="baud_rate">Baud Rate</label>
                        <select name="baud_rate" id="baud_rate" class="form-select" required>
                            <option value="9600" {{ old('baud_rate', '9600') == '9600' ? 'selected' : '' }}>9600</option>
                            <option value="19200" {{ old('baud_rate') == '19200' ? 'selected' : '' }}>19200</option>
                            <option value="38400" {{ old('baud_rate') == '38400' ? 'selected' : '' }}>38400</option>
                            <option value="57600" {{ old('baud_rate') == '57600' ? 'selected' : '' }}>57600</option>
                            <option value="115200" {{ old('baud_rate') == '115200' ? 'selected' : '' }}>115200</option>
                        </select>
                        <span class="form-hint">La mayoría de impresoras ESC/POS usan 9600 o 115200</span>
                    </div>

                    <div class="form-group full">
                        <div class="form-checkbox">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" id="activo" {{ old('activo', true) ? 'checked' : '' }}>
                            <label for="activo">Activa</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.printers.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Crear Impresora</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.btn-toggle {
    display: flex;
    gap: 0;
    border: 1px solid var(--border-dim);
    border-radius: var(--radius);
    overflow: hidden;
    width: fit-content;
}
.toggle-option {
    cursor: pointer;
}
.toggle-option input[type="radio"] {
    display: none;
}
.toggle-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-3);
    background: var(--bg-3);
    transition: all .15s;
}
.toggle-option input[type="radio"]:checked + .toggle-label {
    background: var(--gold);
    color: var(--bg-2);
}
.bluetooth-scan {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.bt-detect-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.bt-detected {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: rgba(76, 175, 125, 0.1);
    border: 1px solid rgba(76, 175, 125, 0.3);
    border-radius: var(--radius);
    font-size: 13px;
}
.bt-mac {
    font-size: 11px;
    color: var(--text-3);
    font-family: monospace;
}
</style>

<script>
function printerForm() {
    return {
        conexion: '{{ old('conexion', 'usb') }}',
        detectedName: '{{ old('ruta_dispositivo') }}',
        detectedId: '',
        detectError: '',
        devicePath: '{{ old('ruta_dispositivo', '/dev/usb/lp0') }}',
        usbStoredPath: '{{ old('ruta_dispositivo', '') }}',
        usbDevices: [],
        usbDetecting: false,
        usbDetectError: '',
        usbDetected: false,
        usbMismatch: false,
        init() {
            if (this.conexion === 'usb') {
                this.detectUsb();
            }
            this.$watch('conexion', (val) => {
                if (val === 'usb') {
                    this.detectUsb();
                }
            });
        },
        async detectUsb() {
            this.usbDetecting = true;
            this.usbDetectError = '';
            this.usbDevices = [];
            this.usbDetected = false;
            this.usbMismatch = false;
            const wasEmpty = !this.usbStoredPath || this.usbStoredPath.trim() === '';
            try {
                const res = await fetch('{{ route('admin.printers.usb-detect') }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                });
                const data = await res.json();
                if (!data.success) {
                    this.usbDetectError = data.error || 'No se pudo detectar impresoras USB.';
                } else {
                    this.usbDevices = data.devices || [];
                    this.usbDetected = this.usbDevices.length > 0;
                    if (this.usbDetected && wasEmpty) {
                        this.devicePath = this.usbDevices[0];
                    }
                    if (this.usbDetected && !wasEmpty && !this.usbDevices.includes(this.usbStoredPath)) {
                        this.usbMismatch = true;
                    }
                }
            } catch (e) {
                this.usbDetectError = 'Error de conexión al servidor de impresión.';
            } finally {
                this.usbDetecting = false;
            }
        },
        async detectFromBrowser() {
            this.detectError = '';
            this.detectedName = '';
            this.detectedId = '';
            if (!('serial' in navigator)) {
                this.detectError = 'Web Serial API no soportada. Use Chrome 117+ (desktop) o Chrome 138+ (Android).';
                return;
            }
            try {
                const port = await navigator.serial.requestPort();
                this.detectedName = 'Bluetooth Printer';
                this.detectedId = port.getInfo().usbVendorId || 'N/A';
                this.devicePath = this.detectedName;
            } catch (e) {
                if (e.name !== 'NotFoundError') {
                    this.detectError = 'Error: ' + e.message;
                }
            }
        }
    };
}
</script>
@endsection
