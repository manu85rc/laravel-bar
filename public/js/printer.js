/**
 * BluetoothPrinter — Web Serial + ESC/POS for thermal printers
 *
 * Usage:
 *   const printer = new BluetoothPrinter();
 *   await printer.connect(9600);
 *   await printer.printTicket(ticketData);
 *   await printer.pollAndPrint(printerId, csrfToken);
 */

class BluetoothPrinter {
    port = null;
    writer = null;
    connected = false;
    pollInterval = null;

    /**
     * Request a serial port from the browser (shows native BT picker)
     */
    async requestPort() {
        if (!('serial' in navigator)) {
            throw new Error('Web Serial API no soportada. Use Chrome 117+.');
        }
        this.port = await navigator.serial.requestPort();
        return this.port;
    }

    /**
     * Open the serial connection
     */
    async connect(baudRate = 9600) {
        if (!this.port) {
            await this.requestPort();
        }
        await this.port.open({ baudRate });
        this.writer = this.port.writable.getWriter();
        this.connected = true;

        this.port.addEventListener('disconnect', () => {
            this.connected = false;
            this.writer = null;
            console.log('[Printer] Desconectada');
        });

        console.log('[Printer] Conectada a', baudRate, 'baud');
    }

    /**
     * Reconnect to a previously authorized port
     */
    async reconnect(baudRate = 9600) {
        const ports = await navigator.serial.getPorts();
        if (ports.length === 0) {
            throw new Error('No hay puertos autorizados. Use "Detectar" primero.');
        }
        this.port = ports[0];
        await this.connect(baudRate);
    }

    /**
     * Send raw bytes to the printer
     */
    async write(bytes) {
        if (!this.connected || !this.writer) {
            throw new Error('No conectada');
        }
        await this.writer.write(bytes);
    }

    /**
     * Build ESC/POS bytes from ticket data
     * Matches the PHP PrinterService output format
     */
    buildESCPOS(ticket) {
        const encoder = new TextEncoder();
        const chunks = [];

        const pushText = (text) => {
            chunks.push(encoder.encode(text));
        };

        const pushByte = (...bytes) => {
            chunks.push(new Uint8Array(bytes));
        };

        const width = ticket.papel === '58mm' ? 32 : 48;

        // ESC @ — Initialize
        pushByte(0x1B, 0x40);

        // Center align
        pushByte(0x1B, 0x61, 0x01);

        // Double height + double width
        pushByte(0x1B, 0x21, 0x30);
        pushText(ticket.encabezado + '\n');

        // Normal mode
        pushByte(0x1B, 0x21, 0x00);
        pushByte(0x0A); // feed 1 line

        // Bold — PEDIDO #id (or referencia)
        pushByte(0x1B, 0x45, 0x01);
        pushText((ticket.referencia != null ? ticket.referencia : 'PEDIDO #' + ticket.pedido_id) + '\n');
        pushByte(0x1B, 0x45, 0x00); // bold off

        // Left align
        pushByte(0x1B, 0x61, 0x00);

        pushText('Mesa: ' + ticket.mesa + '\n');
        pushText('Staff: ' + ticket.staff + '\n');
        pushText('Fecha: ' + ticket.fecha + '\n');
        pushByte(0x0A); // feed 1 line

        if (ticket.type === 'factura') {
            this._buildFactura(chunks, ticket, width, encoder);
        } else {
            this._buildCocina(chunks, ticket, width, encoder);
        }

        // Feed 3 lines + cut
        pushByte(0x1B, 0x64, 0x03); // ESC d 3
        pushByte(0x1D, 0x56, 0x00); // GS V 0 — full cut

        // Concatenate all chunks
        const totalLength = chunks.reduce((sum, c) => sum + c.length, 0);
        const result = new Uint8Array(totalLength);
        let offset = 0;
        for (const chunk of chunks) {
            result.set(chunk, offset);
            offset += chunk.length;
        }

        return result;
    }

    _buildCocina(chunks, ticket, width, encoder) {
        const pushText = (text) => chunks.push(encoder.encode(text));
        const pushByte = (...bytes) => chunks.push(new Uint8Array(bytes));

        // Separator
        pushText('-'.repeat(width) + '\n');

        // Header
        pushByte(0x1B, 0x45, 0x01); // bold on
        pushText('CANT  PRODUCTO\n');
        pushByte(0x1B, 0x45, 0x00); // bold off
        pushText('-'.repeat(width) + '\n');

        // Items
        for (const item of ticket.items) {
            let line = item.cantidad + 'x  ' + item.nombre;
            if (line.length > width) {
                line = line.substring(0, width - 1);
            }
            pushText(line + '\n');
        }

        pushText('-'.repeat(width) + '\n');
        pushByte(0x0A); // feed

        // Center — gracias
        pushByte(0x1B, 0x61, 0x01);
        pushText('¡Gracias!\n');
        pushByte(0x1B, 0x61, 0x00); // back to left
    }

    _buildFactura(chunks, ticket, width, encoder) {
        const pushText = (text) => chunks.push(encoder.encode(text));
        const pushByte = (...bytes) => chunks.push(new Uint8Array(bytes));

        // Left align
        pushByte(0x1B, 0x61, 0x00);

        pushByte(0x1B, 0x45, 0x01); // bold
        pushText('FACTURA\n');
        pushByte(0x1B, 0x45, 0x00);
        pushByte(0x0A);

        pushText('='.repeat(width) + '\n');

        pushByte(0x1B, 0x45, 0x01);
        pushText('CANT  PRODUCTO\n');
        pushByte(0x1B, 0x45, 0x00);
        pushText('-'.repeat(width) + '\n');

        for (const item of ticket.items) {
            let line = item.cantidad + 'x  ' + item.nombre;
            if (line.length > width) {
                line = line.substring(0, width - 1);
            }
            pushText(line + '\n');

            const precioStr = '$' + item.precio_unitario.toLocaleString('es');
            const subtotalStr = '$' + item.subtotal.toLocaleString('es');
            pushText('    a ' + precioStr + '\n');

            // Right align for subtotal
            pushByte(0x1B, 0x61, 0x02);
            pushText('Subtotal: ' + subtotalStr + '\n');
            pushByte(0x1B, 0x61, 0x00); // left
        }

        pushText('='.repeat(width) + '\n');

        // Total
        const totalStr = '$' + ticket.total.toLocaleString('es');
        pushByte(0x1B, 0x45, 0x01); // bold
        pushByte(0x1B, 0x61, 0x02); // right
        pushText('TOTAL: ' + totalStr + '\n');
        pushByte(0x1B, 0x45, 0x00); // bold off
        pushByte(0x1B, 0x61, 0x00); // left

        pushByte(0x1B, 0x61, 0x01); // center
        pushText('='.repeat(width) + '\n');
        pushByte(0x0A);
        pushText('¡Gracias por su visita!\n');
        pushByte(0x1B, 0x61, 0x00); // left
    }

    /**
     * Print a ticket (ESC/POS bytes)
     */
    async printTicket(ticket) {
        const escpos = this.buildESCPOS(ticket);

        // BLE/serial chunking — send in 512-byte chunks with throttle
        const CHUNK = 512;
        for (let i = 0; i < escpos.length; i += CHUNK) {
            const chunk = escpos.slice(i, i + CHUNK);
            await this.write(chunk);
            if (i + CHUNK < escpos.length) {
                await new Promise(r => setTimeout(r, 50));
            }
        }
    }

    /**
     * Poll server for pending print jobs and print them
     */
    async pollAndPrint(printerId, csrfToken) {
        try {
            const resp = await fetch('/print-queue?printer_id=' + printerId, {
                headers: { 'Accept': 'application/json' }
            });
            const jobs = await resp.json();

            for (const job of jobs) {
                if (!job.pending) continue;
                try {
                    await this.printTicket(job.ticket_data);
                    await fetch('/print-queue/complete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ id: job.id })
                    });
                    console.log('[Printer] Job #' + job.id + ' impreso');
                } catch (e) {
                    console.error('[Printer] Error imprimiendo job #' + job.id, e);
                }
            }
        } catch (e) {
            console.error('[Printer] Error en polling', e);
        }
    }

    /**
     * Start automatic polling (every 3 seconds)
     */
    startPolling(printerId, csrfToken) {
        this.stopPolling();
        this.pollInterval = setInterval(() => {
            if (this.connected) {
                this.pollAndPrint(printerId, csrfToken);
            }
        }, 3000);
        console.log('[Printer] Polling iniciado cada 3s para printer #' + printerId);
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    /**
     * Disconnect
     */
    async disconnect() {
        this.stopPolling();
        if (this.writer) {
            this.writer.releaseLock();
        }
        if (this.port && this.port.readable) {
            await this.port.close();
        }
        this.connected = false;
        this.writer = null;
        this.port = null;
        console.log('[Printer] Desconectada');
    }
}

// Export for use
if (typeof module !== 'undefined') {
    module.exports = BluetoothPrinter;
}
