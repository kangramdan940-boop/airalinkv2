@extends('layouts.app')
@section('content')
<div class="container-fluid pb-5">
    <div class="row g-4">

        <!-- CALENDAR -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div id="calendar" style="color:#f1f5f9"></div>
                </div>
            </div>
        </div>

        <!-- DATE + BANK SALDO (full width) -->
        <div class="col-12">
            <div class="card">
                <!-- Date picker header -->
                <div class="d-flex align-items-center justify-content-between p-3" style="border-bottom:1px solid var(--border)">
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="closing-date" class="form-control form-control-sm" style="width:auto;color:#f1f5f9;background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1)" value="{{ date('Y-m-d') }}">
                        <button class="btn btn-primary btn-sm" id="btn-load"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                    <span class="badge" id="status-badge" style="display:none">--</span>
                </div>
                <div class="card-body p-4">
                    <h6 class="mb-3" style="color:#f1f5f9"><i class="bi bi-bank2 text-primary"></i> Saldo Bank / E-Wallet</h6>
                    <div id="bank-rows" class="row g-2 mb-0">
                        <div style="color:#94a3b8;font-size:0.8rem">Memuat rekening...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CASH + EXPENSES + NOTES -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Cash denominations -->
                        <div class="col-md-7">
                            <h6 class="mb-3" style="color:#f1f5f9"><i class="bi bi-cash-coin text-success"></i> Uang Tunai per Pecahan</h6>
                            <div id="cash-rows" class="row g-2"></div>
                        </div>
                        <!-- Expenses + notes -->
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0" style="color:#f1f5f9"><i class="bi bi-receipt text-warning"></i> Pengeluaran</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-expense"><i class="bi bi-plus-lg"></i></button>
                            </div>
                            <div id="expense-rows" class="d-flex flex-column gap-2 mb-3"></div>
                            <textarea id="closing-notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- FLOATING SUMMARY BAR (bottom) -->
<div id="floating-bar" style="position:fixed;bottom:0;left:var(--sidebar-w);right:0;z-index:900;background:rgba(15,23,42,0.85);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);border-top:1px solid rgba(255,255,255,0.08);padding:12px 24px">
    <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">
        <!-- Summary -->
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex flex-column">
                <span style="color:#94a3b8;font-size:0.7rem">Total Bank</span>
                <span style="color:#f1f5f9;font-weight:700;font-size:0.9rem" id="total-bank">Rp 0</span>
            </div>
            <div class="d-flex flex-column">
                <span style="color:#94a3b8;font-size:0.7rem">Total Tunai</span>
                <span style="color:#f1f5f9;font-weight:700;font-size:0.9rem" id="total-cash">Rp 0</span>
            </div>
            <div class="d-flex flex-column">
                <span style="color:#94a3b8;font-size:0.7rem">Grand Total</span>
                <span style="color:#60a5fa;font-weight:700;font-size:0.9rem" id="grand-total">Rp 0</span>
            </div>
            <div class="d-flex flex-column">
                <span style="color:#94a3b8;font-size:0.7rem">Pengeluaran</span>
                <span style="color:#cbd5e1;font-weight:600;font-size:0.9rem" id="total-expense">Rp 0</span>
            </div>
            <div class="d-flex flex-column">
                <span style="color:#94a3b8;font-size:0.7rem">Profit</span>
                <span style="font-weight:700;font-size:0.9rem" id="profit">Rp 0</span>
            </div>
            <span style="color:#94a3b8;font-size:0.7rem;display:none" id="prev-total-label">Prev: <span id="prev-total">Rp 0</span></span>
        </div>
        <!-- Submit -->
        <button type="button" class="btn btn-primary" id="btn-submit" style="white-space:nowrap">
            <i class="bi bi-check-lg"></i> Simpan Closing
        </button>
    </div>
</div>

<!-- spacer for floating bar -->
<div style="height:80px"></div>

<!-- DETAIL MODAL -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Closing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body"></div>
        </div>
    </div>
</div>

<style>
/* FullCalendar dark theme overrides */
.fc { --fc-border-color: rgba(255,255,255,0.08); --fc-page-bg-color: transparent; }
.fc .fc-toolbar-title { color: #f1f5f9; font-size: 1rem; }
.fc .fc-col-header-cell-cushion { color: #94a3b8; font-weight: 600; }
.fc .fc-daygrid-day-number { color: #cbd5e1; text-decoration: none; }
.fc .fc-daygrid-day:hover { background: rgba(59,130,246,0.04); }
.fc .fc-day-today { background: rgba(59,130,246,0.08) !important; }
.fc .fc-day-today .fc-daygrid-day-number { color: var(--primary); font-weight: 700; }
.fc .fc-button {
    background: rgba(59,130,246,0.08) !important;
    border: 1px solid rgba(59,130,246,0.2) !important;
    color: #60a5fa !important;
    text-transform: capitalize;
    font-size: 0.78rem;
    border-radius: 8px !important;
}
.fc .fc-button:hover { background: rgba(59,130,246,0.15) !important; }
.fc .fc-button-active { background: rgba(59,130,246,0.2) !important; }
.fc-event { border: none !important; border-radius: 6px !important; padding: 2px 6px !important; font-size: 0.72rem !important; cursor: pointer; }
.fc-event-safe { background: rgba(16,185,129,0.7) !important; }
.fc-event-loss { background: rgba(239,68,68,0.7) !important; }
.fc .fc-daygrid-event { margin-top: 2px; }
.fc .fc-daygrid-day-events { margin-top: 4px; min-height: 20px; }
</style>

<script>
window.addEventListener('load', function() {
    const API = '/closing/api';
    const denominations = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
    const fmt = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    let accounts = [];
    let closingId = null;
    let calendar = null;

    // ===== INIT =====
    function init() {
        console.log('[Closing] init() started');
        renderCashRows();
        console.log('[Closing] cash rows rendered');
        initCalendar();
        console.log('[Closing] calendar init called');
        loadAccounts();
        console.log('[Closing] loadAccounts called');
        loadClosing();
        console.log('[Closing] loadClosing called');
    }

    // ===== CALENDAR =====
    function initCalendar() {
        const calendarEl = document.getElementById('calendar');
        console.log('[Closing] initCalendar() element:', calendarEl);
        if (!calendarEl) { console.error('[Closing] calendar element not found!'); return; }
        if (typeof FullCalendar === 'undefined') { console.error('[Closing] FullCalendar not loaded!'); return; }
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
            height: 'auto',
            events: function(info, successCb, failCb) {
                const mid = new Date((info.start.getTime() + info.end.getTime()) / 2);
                const m = mid.getMonth() + 1;
                const y = mid.getFullYear();
                $.get(`${API}/closings?month=${m}&year=${y}`)
                    .done(function(res) {
                        console.log('[Closing] calendar events response:', res);
                        const data = res.data || [];
                        const events = data.map(c => {
                            const profit = parseFloat(c.profit) || 0;
                            const total = parseFloat(c.grand_total) || 0;
                            return {
                                id: c.id,
                                title: (profit >= 0 ? '✅ ' : '⚠️ ') + fmt(total),
                                start: c.closing_date.split('T')[0],
                                className: c.status === 'safe' ? 'fc-event-safe' : 'fc-event-loss',
                                extendedProps: { closing: c }
                            };
                        });
                        successCb(events);
                    })
                    .fail(failCb);
            },
            eventClick: function(info) {
                showDetail(info.event.id);
            },
            dateClick: function(info) {
                $('#closing-date').val(info.dateStr);
                loadClosing();
                $('html, body').animate({ scrollTop: $('#closing-date').offset().top - 80 }, 300);
            }
        });
        calendar.render();
    }

    // ===== CASH ROWS =====
    function renderCashRows() {
        const colors = { 100000:'#e74c3c', 50000:'#3498db', 20000:'#e67e22', 10000:'#9b59b6', 5000:'#27ae60', 2000:'#e84393', 1000:'#f39c12' };
        let html = '';
        denominations.forEach(d => {
            const label = d >= 1000 ? (d/1000) + 'K' : d;
            html += `
            <div class="col-md-3 col-6">
                <div class="p-2 rounded" style="background:rgba(255,255,255,0.03);border:1px solid var(--border)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="width:12px;height:12px;border-radius:3px;background:${colors[d]};display:inline-block"></span>
                        <span class="small text-muted">${label}</span>
                    </div>
                    <input type="number" class="form-control form-control-sm cash-qty" data-denom="${d}" min="0" value="0" style="text-align:right">
                    <div class="small text-muted text-end mt-1 cash-subtotal" data-denom="${d}">Rp 0</div>
                </div>
            </div>`;
        });
        $('#cash-rows').html(html);
    }

    // ===== ACCOUNTS =====
    function loadAccounts() {
        console.log('[Closing] loadAccounts() fetching', `${API}/accounts`);
        $.get(`${API}/accounts`, function(res) {
            console.log('[Closing] accounts response:', res);
            accounts = res.data || [];
            console.log('[Closing] accounts loaded:', accounts.length, 'items');
            let html = '';
                if (!accounts.length) { html = '<div style="color:#94a3b8;font-size:0.8rem">Tidak ada rekening terdaftar</div>'; }
            accounts.forEach(a => {
            html += `
            <div class="col-md-4">
                <div class="bank-row" data-id="${a.id}" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi ${a.type==='bank'?'bi-bank':'bi-phone'}" style="color:var(--primary)"></i>
                        <div style="overflow:hidden">
                            <div style="font-weight:600;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${a.label}</div>
                            <div style="font-size:0.7rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${a.bank_name} · ${a.account_number || '-'}</div>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text" style="font-size:0.75rem">Rp</span>
                        <input type="text" class="form-control form-control-sm bank-balance" data-id="${a.id}" placeholder="0" style="text-align:right" oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.')">
                    </div>
                </div>
            </div>`;
            });
            $('#bank-rows').html(html);
            $('.bank-balance').on('input', calcTotals);
            // After accounts load, reload closing to fill balances
            loadClosing();
        }).fail(function(xhr, status, error) {
            console.error('[Closing] loadAccounts FAILED:', status, error, xhr.responseText);
            $('#bank-rows').html('<div style="color:#ef4444;font-size:0.8rem">Gagal memuat rekening. Cek console untuk detail.</div>');
        });
    }

    // ===== LOAD CLOSING BY DATE =====
    function loadClosing() {
        const date = $('#closing-date').val();
        if (!date) return;

        // Load current date
        $.get(`${API}/by-date/${date}`, function(res) {
            const c = res.data;
            if (!c) {
                closingId = null;
                $('#status-badge').hide();
                $('.bank-balance').val('');
                $('.cash-qty').val(0);
                $('#expense-rows').empty();
                $('#closing-notes').val('');
                calcTotals();
                return;
            }
            closingId = c.id;
            $('#status-badge').show().text(c.status === 'safe' ? 'AMAN' : 'RUGI').removeClass('bg-info bg-success bg-danger').addClass(c.status === 'safe' ? 'bg-success' : 'bg-danger');

            // Set previous total & profit directly from API response
            $('#prev-total').text(fmt(parseFloat(c.previous_total)));
            $('#total-expense').text(fmt(parseFloat(c.total_expense)));
            $('#prev-total-label').show();

            // Fill bank balances
            (c.banks || []).forEach(b => {
                const val = parseFloat(b.balance) || 0;
                const formatted = val > 0 ? val.toLocaleString('id-ID') : '';
                $(`.bank-balance[data-id="${b.brilink_account_id}"]`).val(formatted);
            });

            // Fill cash
            $('.cash-qty').val(0);
            (c.cash || []).forEach(cash => {
                $(`.cash-qty[data-denom="${cash.denomination}"]`).val(cash.quantity || 0);
            });

            // Fill expenses
            $('#expense-rows').empty();
            (c.expenses || []).forEach(e => addExpenseRow(e.description, e.amount));

            // Notes
            $('#closing-notes').val(c.notes || '');

            calcTotals();

            // Override profit from API (backend calculates it)
            const apiProfit = parseFloat(c.profit) || 0;
            $('#profit').text(fmt(apiProfit));
            $('#profit').removeClass('text-success text-danger').addClass(apiProfit >= -10000 ? 'text-success' : 'text-danger');
        }).fail(function() {
            closingId = null;
            calcTotals();
        });

        // Load previous day only for new entries (existing closings already have previous_total)
        if (!closingId) {
            const prev = new Date(date);
            prev.setDate(prev.getDate() - 1);
            const prevDate = prev.toISOString().split('T')[0];
            $.get(`${API}/by-date/${prevDate}`, function(res) {
                const pc = res.data;
                $('#prev-total').text(pc ? fmt(parseFloat(pc.grand_total)) : 'Rp 0');
                calcTotals();
            }).fail(function() {
                $('#prev-total').text('Rp 0');
            });
        }
    }

    // ===== EXPENSES =====
    function addExpenseRow(desc = '', amount = '') {
        const formattedAmt = amount ? (parseInt(amount) || 0).toLocaleString('id-ID') : '';
        $('#expense-rows').append(`
        <div class="row g-2 align-items-center expense-row">
            <div class="col-5">
                <input type="text" class="form-control form-control-sm expense-desc" placeholder="Keterangan" value="${desc}">
            </div>
            <div class="col-5">
                <div class="input-group">
                    <span class="input-group-text" style="font-size:0.75rem">Rp</span>
                    <input type="text" class="form-control form-control-sm expense-amount" placeholder="0" value="${formattedAmt}" style="text-align:right" oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.')">
                </div>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="$(this).closest('.expense-row').remove();calcTotals()"><i class="bi bi-trash"></i></button>
            </div>
        </div>`);
        $('.expense-amount').off('input', calcTotals).on('input', calcTotals);
    }

    // ===== CALCULATE TOTALS =====
    function calcTotals() {
        let totalBank = 0;
        $('.bank-balance').each(function() {
            const v = parseFloat(String($(this).val()).replace(/[^0-9-]/g, '')) || 0;
            totalBank += v;
        });

        let totalCash = 0;
        $('.cash-qty').each(function() {
            const denom = parseInt($(this).data('denom'));
            const qty = parseInt($(this).val()) || 0;
            const sub = denom * qty;
            totalCash += sub;
            $(`.cash-subtotal[data-denom="${denom}"]`).text(fmt(sub));
        });

        let totalExpense = 0;
        $('.expense-amount').each(function() {
            const v = parseFloat(String($(this).val()).replace(/[^0-9-]/g, '')) || 0;
            totalExpense += v;
        });

        const grandTotal = totalBank + totalCash;
        const prevText = $('#prev-total').text().replace(/[^0-9-]/g, '');
        const prevTotal = parseFloat(prevText) || 0;
        const profit = (grandTotal - prevTotal) + totalExpense;

        $('#total-bank').text(fmt(totalBank));
        $('#total-cash').text(fmt(totalCash));
        $('#grand-total').text(fmt(grandTotal));
        $('#total-expense').text(fmt(totalExpense));
        const profitEl = $('#profit');
        profitEl.text(fmt(profit));
        profitEl.removeClass('text-success text-danger');
        profitEl.addClass(profit >= -10000 ? 'text-success' : 'text-danger');
    }

    // ===== SUBMIT =====
    function submitClosing() {
        const date = $('#closing-date').val();
        if (!date) { Swal.fire('Error', 'Pilih tanggal dulu', 'error'); return; }

        const banks = [];
        $('.bank-row').each(function() {
            const id = $(this).data('id');
            const balance = parseFloat(String($(this).find('.bank-balance').val()).replace(/[^0-9-]/g, '')) || 0;
            if (balance > 0) banks.push({ brilink_account_id: id, balance });
        });

        const cash = [];
        $('.cash-qty').each(function() {
            const denom = parseInt($(this).data('denom'));
            const qty = parseInt($(this).val()) || 0;
            if (qty > 0) cash.push({ denomination: denom, quantity: qty });
        });

        const expenses = [];
        $('.expense-row').each(function() {
            const desc = $(this).find('.expense-desc').val();
            const amt = parseFloat(String($(this).find('.expense-amount').val()).replace(/[^0-9-]/g, '')) || 0;
            if (desc || amt) expenses.push({ description: desc || '-', amount: amt });
        });

        const notes = $('#closing-notes').val();
        const payload = { closing_date: date, banks, cash, expenses, notes };

        $('#btn-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: `${API}/closings`,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                closingId = res.data?.id || closingId;
                Swal.fire('Berhasil!', 'Closing tersimpan', 'success');
                if (calendar) calendar.refetchEvents();
            },
            error: function() {
                Swal.fire('Error', 'Gagal menyimpan closing', 'error');
            },
            complete: function() {
                $('#btn-submit').prop('disabled', false).html('<i class="bi bi-check-lg"></i> Simpan Closing');
            }
        });
    }

    // ===== DETAIL =====
    window.showDetail = function(id) {
        $.get(`${API}/closings/${id}`, function(res) {
            const c = res.data;
            if (!c) return;
            let banks = (c.banks || []).map(b => `<tr><td>${b.account?.label || b.account?.bank_name || '-'}</td><td class="text-end">${fmt(parseFloat(b.balance))}</td></tr>`).join('');
            let cash = (c.cash || []).map(c => `<tr><td>Rp ${parseInt(c.denomination).toLocaleString('id-ID')}</td><td class="text-end">${c.quantity}</td><td class="text-end">${fmt(parseInt(c.denomination) * parseInt(c.quantity))}</td></tr>`).join('');
            let exp = (c.expenses || []).map(e => `<tr><td>${e.description}</td><td class="text-end">${fmt(parseFloat(e.amount))}</td></tr>`).join('');
            const date = new Date(c.closing_date).toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
            $('#detail-body').html(`
                <h6 class="mb-3">Closing ${date}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted small">Bank / E-Wallet</h6>
                        <table class="table table-sm">${banks || '<tr><td class="text-muted">Tidak ada</td></tr>'}</table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small">Uang Tunai</h6>
                        <table class="table table-sm">${cash || '<tr><td class="text-muted">Tidak ada</td></tr>'}</table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small">Pengeluaran</h6>
                        <table class="table table-sm">${exp || '<tr><td class="text-muted">Tidak ada</td></tr>'}</table>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:rgba(255,255,255,0.03)">
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Bank</span><b>${fmt(parseFloat(c.total_bank))}</b></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Tunai</span><b>${fmt(parseFloat(c.total_cash))}</b></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Grand Total</span><b class="text-primary">${fmt(parseFloat(c.grand_total))}</b></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hari Sebelumnya</span><b>${fmt(parseFloat(c.previous_total))}</b></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pengeluaran</span><b>${fmt(parseFloat(c.total_expense))}</b></div>
                            <hr style="border-color:var(--border)">
                            <div class="d-flex justify-content-between"><span class="fw-bold">Profit</span><b class="${parseFloat(c.profit)>=0?'text-success':'text-danger'} fs-5">${fmt(parseFloat(c.profit))}</b></div>
                        </div>
                    </div>
                </div>`);
            new bootstrap.Modal('#detailModal').show();
        }).fail(function() {
            Swal.fire('Error', 'Gagal memuat detail', 'error');
        });
    };

    // ===== EVENTS =====
    $('#btn-load').on('click', loadClosing);
    $('#closing-date').on('change', loadClosing);
    $('#btn-add-expense').on('click', () => addExpenseRow());
    $('#btn-submit').on('click', submitClosing);
    $(document).on('input', '.cash-qty', calcTotals);

    init();
});
</script>
@endsection
