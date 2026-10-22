@php($role = $role ?? null)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengeluaran Harian</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #3b82f6;
            --accent: #8b5cf6;
            --bg-dark: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: rgba(255,255,255,0.08);
            --radius: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100dvh;
        }
        h1, h2, h3, h4, h5, h6, p, span, label, a { color: inherit; }

        /* ===== PIN SCREEN ===== */
        .pin-screen {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .pin-logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: #fff;
            box-shadow: 0 8px 24px rgba(59,130,246,0.35);
            margin-bottom: 1rem;
        }
        .pin-title { font-size: 1.3rem; font-weight: 700; }
        .pin-subtitle { font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.3rem; }

        .pin-dots { display: flex; gap: 0.9rem; margin: 1.8rem 0 0.6rem; }
        .pin-dot {
            width: 16px; height: 16px; border-radius: 50%;
            border: 2px solid var(--text-muted);
            transition: all 0.15s ease;
        }
        .pin-dot.filled {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(59,130,246,0.6);
            transform: scale(1.1);
        }
        .pin-dots.error .pin-dot { border-color: #ef4444; background: rgba(239,68,68,0.25); }
        .pin-error { font-size: 0.82rem; color: #f87171; min-height: 1.2rem; }
        .shake { animation: shake 0.4s ease; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }

        .pin-pad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.8rem;
            width: 100%;
            max-width: 300px;
            margin-top: 1.2rem;
        }
        .pin-key {
            height: 64px;
            border: none;
            border-radius: var(--radius);
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text-primary);
            font-size: 1.4rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pin-key:active { background: rgba(59,130,246,0.2); transform: scale(0.96); }
        .pin-key.ghost { background: transparent; border-color: transparent; font-size: 1.1rem; color: var(--text-secondary); }
        .pin-hint { margin-top: 1.8rem; font-size: 0.72rem; color: var(--text-muted); text-align: center; line-height: 1.6; }
        .pin-hint b { color: var(--text-secondary); }

        /* ===== MAIN SCREEN ===== */
        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1rem;
            padding-top: calc(0.85rem + env(safe-area-inset-top));
            display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
        }
        .topbar .brand { display: flex; align-items: center; gap: 0.6rem; min-width: 0; }
        .topbar .logo {
            width: 34px; height: 34px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem; color: #fff;
        }
        .topbar .titles h1 { font-size: 0.95rem; font-weight: 700; white-space: nowrap; }
        .topbar .titles .date-line { font-size: 0.72rem; color: var(--text-muted); }
        .mode-badge {
            font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;
            padding: 0.2rem 0.5rem; border-radius: 50px;
            background: rgba(59,130,246,0.15); color: #60a5fa;
            border: 1px solid rgba(59,130,246,0.3);
        }
        .mode-badge.admin { background: rgba(139,92,246,0.15); color: #a78bfa; border-color: rgba(139,92,246,0.35); }
        .btn-logout {
            width: 36px; height: 36px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 10px; color: var(--text-secondary); font-size: 1rem;
        }

        /* Date navigator (admin) */
        .date-nav {
            position: sticky; top: calc(57px + env(safe-area-inset-top)); z-index: 90;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.6rem 1rem;
            display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
        }
        .date-nav .nav-btn {
            width: 40px; height: 40px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 12px; color: var(--text-primary); font-size: 1.2rem;
            transition: all 0.15s ease;
        }
        .date-nav .nav-btn:active { background: rgba(59,130,246,0.2); }
        .date-nav .nav-btn:disabled { opacity: 0.3; }
        .date-nav .nav-center { text-align: center; }
        .date-nav .nav-date { font-size: 0.88rem; font-weight: 600; }
        .today-chip {
            margin-top: 0.2rem; font-size: 0.65rem; font-weight: 600;
            padding: 0.15rem 0.6rem; border-radius: 50px;
            background: rgba(59,130,246,0.12); color: #60a5fa;
            border: 1px solid rgba(59,130,246,0.25);
        }

        .container { max-width: 520px; margin: 0 auto; padding: 1rem; padding-bottom: calc(290px + env(safe-area-inset-bottom)); }

        /* Total card */
        .total-card {
            background: linear-gradient(135deg, rgba(59,130,246,0.18), rgba(139,92,246,0.18));
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        }
        .total-card .lbl { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #93c5fd; }
        .total-card .val { font-size: 1.55rem; font-weight: 800; margin-top: 0.25rem; word-break: break-all; }
        .total-card .count { font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.2rem; }
        .total-card .icon {
            width: 46px; height: 46px; flex-shrink: 0;
            background: rgba(255,255,255,0.08);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #bfdbfe;
        }

        .section-title { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); margin: 1.4rem 0 0.7rem; }

        /* Expense item */
        .exp-item {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.9rem 1rem;
            margin-bottom: 0.6rem;
            display: flex; align-items: flex-start; gap: 0.75rem;
        }
        .exp-item .exp-icon {
            width: 38px; height: 38px; flex-shrink: 0;
            background: rgba(239,68,68,0.1);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #f87171; font-size: 1rem;
        }
        .exp-item .exp-body { flex: 1; min-width: 0; }
        .exp-item .exp-nama { font-size: 0.88rem; font-weight: 600; word-break: break-word; }
        .exp-item .exp-meta { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem; word-break: break-word; }
        .exp-item .exp-right { text-align: right; flex-shrink: 0; }
        .exp-item .exp-nominal { font-size: 0.9rem; font-weight: 700; color: #fca5a5; white-space: nowrap; }
        .exp-item .exp-actions { margin-top: 0.4rem; display: flex; gap: 0.35rem; justify-content: flex-end; }
        .exp-item .act-btn {
            width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; font-size: 0.8rem;
            background: rgba(255,255,255,0.06); border: 1px solid var(--border);
        }
        .act-btn.edit { color: #60a5fa; }
        .act-btn.delete { color: #f87171; }

        .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); }
        .empty-state i { font-size: 2.4rem; opacity: 0.5; }
        .empty-state p { font-size: 0.82rem; margin-top: 0.6rem; }

        /* ===== FIXED ADD FORM ===== */
        .add-form {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 100;
            background: rgba(15,23,42,0.92);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-top: 1px solid var(--border);
            padding: 0.9rem 1rem;
            padding-bottom: calc(0.9rem + env(safe-area-inset-bottom));
        }
        .add-form .form-inner { max-width: 520px; margin: 0 auto; }
        .add-form .form-title {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--text-muted); margin-bottom: 0.55rem;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.55rem; }
        .form-grid .full { grid-column: 1 / -1; }
        .field label { font-size: 0.68rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 0.3rem; }
        .field input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            padding: 0.65rem 0.8rem;
            font-size: 0.88rem;
            font-family: 'Inter', sans-serif;
        }
        .field input:focus {
            outline: none;
            border-color: rgba(59,130,246,0.5);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
            background: rgba(59,130,246,0.05);
        }
        .field input::placeholder { color: var(--text-muted); }
        .btn-save {
            width: 100%; margin-top: 0.6rem;
            height: 48px;
            border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff; font-size: 0.92rem; font-weight: 700;
            font-family: 'Inter', sans-serif;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
            box-shadow: 0 4px 16px rgba(59,130,246,0.35);
            transition: all 0.15s ease;
        }
        .btn-save:active { transform: scale(0.98); }
        .btn-save:disabled { opacity: 0.6; }

        /* Edit modal (dark) */
        .modal-content { background: #1e293b; border: 1px solid var(--border); color: var(--text-primary); }
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-title { color: var(--text-primary) !important; }
        .btn-close { filter: invert(1); }

        /* Toast */
        .toast-box {
            position: fixed; top: calc(12px + env(safe-area-inset-top)); left: 50%; transform: translateX(-50%);
            z-index: 2000; display: flex; flex-direction: column; gap: 0.4rem; width: min(92%, 420px);
        }
        .toast-item {
            background: rgba(30,41,59,0.95);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.7rem 1rem;
            font-size: 0.82rem;
            display: flex; align-items: center; gap: 0.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            animation: slideDown 0.25s ease;
        }
        .toast-item.success { border-color: rgba(16,185,129,0.4); color: #6ee7b7; }
        .toast-item.error { border-color: rgba(239,68,68,0.4); color: #fca5a5; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }

        @media (min-width: 768px) {
            .topbar { padding: 0.85rem 2rem; }
            .topbar .titles h1 { font-size: 1.05rem; }
            .date-nav { padding: 0.7rem 2rem; }
            .container { padding-bottom: 280px; }
        }
    </style>
</head>
<body>

<!-- ================= PIN SCREEN ================= -->
<div id="pinScreen" class="pin-screen {{ $role ? 'd-none' : '' }}">
    <div class="pin-logo"><i class="bi bi-wallet2"></i></div>
    <div class="pin-title">Pengeluaran Harian</div>
    <div class="pin-subtitle">Masukkan PIN 6 angka untuk melanjutkan</div>

    <div class="pin-dots" id="pinDots">
        <div class="pin-dot"></div><div class="pin-dot"></div><div class="pin-dot"></div>
        <div class="pin-dot"></div><div class="pin-dot"></div><div class="pin-dot"></div>
    </div>
    <div class="pin-error" id="pinError"></div>

    <div class="pin-pad" id="pinPad">
        <button type="button" class="pin-key" data-key="1">1</button>
        <button type="button" class="pin-key" data-key="2">2</button>
        <button type="button" class="pin-key" data-key="3">3</button>
        <button type="button" class="pin-key" data-key="4">4</button>
        <button type="button" class="pin-key" data-key="5">5</button>
        <button type="button" class="pin-key" data-key="6">6</button>
        <button type="button" class="pin-key" data-key="7">7</button>
        <button type="button" class="pin-key" data-key="8">8</button>
        <button type="button" class="pin-key" data-key="9">9</button>
        <button type="button" class="pin-key ghost" data-key="clear">C</button>
        <button type="button" class="pin-key" data-key="0">0</button>
        <button type="button" class="pin-key ghost" data-key="back"><i class="bi bi-backspace"></i></button>
    </div>

    <div class="pin-hint">
        PIN <b>222222</b> — catat pengeluaran (hari ini)<br>
        PIN <b>444444</b> — admin: pilih tanggal, ubah &amp; hapus
    </div>
</div>

<!-- ================= MAIN SCREEN ================= -->
<div id="mainScreen" class="{{ $role ? '' : 'd-none' }}">

    <header class="topbar">
        <div class="brand">
            <div class="logo"><i class="bi bi-wallet2"></i></div>
            <div class="titles">
                <h1>Pengeluaran Harian</h1>
                <div class="date-line" id="topDate"></div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem;">
            <span class="mode-badge {{ $role === 'admin' ? 'admin' : '' }}" id="modeBadge">Mode</span>
            <button type="button" class="btn-logout" id="btnLogout" title="Keluar"><i class="bi bi-box-arrow-right"></i></button>
        </div>
    </header>

    <!-- Navigator tanggal (khusus admin) -->
    <div class="date-nav {{ $role === 'admin' ? '' : 'd-none' }}" id="dateNav">
        <button type="button" class="nav-btn" id="prevDay" aria-label="Sebelumnya"><i class="bi bi-chevron-left"></i></button>
        <div class="nav-center">
            <div class="nav-date" id="navDateLabel"></div>
            <button type="button" class="today-chip" id="todayBtn">Hari Ini</button>
        </div>
        <button type="button" class="nav-btn" id="nextDay" aria-label="Berikutnya"><i class="bi bi-chevron-right"></i></button>
    </div>

    <div class="container">
        <div class="total-card">
            <div>
                <div class="lbl">Total Pengeluaran</div>
                <div class="val" id="totalAmount">Rp 0</div>
                <div class="count" id="totalCount">0 transaksi</div>
            </div>
            <div class="icon"><i class="bi bi-cash-coin"></i></div>
        </div>

        <div class="section-title">Daftar Pengeluaran</div>
        <div id="expList"></div>
    </div>

    <!-- Form tambah (fixed bottom) -->
    <form id="addForm" class="add-form" autocomplete="off">
        <div class="form-inner">
            <div class="form-title"><i class="bi bi-plus-circle"></i> Catat Pengeluaran</div>
            <div class="form-grid">
                <div class="field">
                    <label for="fNama">Keperluan</label>
                    <input type="text" id="fNama" name="nama" maxlength="100" placeholder="cth: Makan siang" required>
                </div>
                <div class="field">
                    <label for="fNominal">Nominal (Rp)</label>
                    <input type="text" id="fNominal" name="nominal" inputmode="decimal" placeholder="cth: 15000" required>
                </div>
                <div class="field full">
                    <label for="fKet">Keterangan <span style="color:var(--text-muted); font-weight:400">(opsional)</span></label>
                    <input type="text" id="fKet" name="keterangan" maxlength="255" placeholder="cth: di kantin pusat">
                </div>
            </div>
            <button type="submit" class="btn-save" id="btnSave"><i class="bi bi-plus-lg"></i> Simpan</button>
        </div>
    </form>
</div>

<!-- ================= EDIT MODAL ================= -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="editForm" autocomplete="off">
                <input type="hidden" id="eId">
                <div class="modal-body">
                    <div class="field mb-3">
                        <label for="eNama">Keperluan</label>
                        <input type="text" id="eNama" maxlength="100" required>
                    </div>
                    <div class="field mb-3">
                        <label for="eNominal">Nominal (Rp)</label>
                        <input type="text" id="eNominal" inputmode="decimal" required>
                    </div>
                    <div class="field">
                        <label for="eKet">Keterangan</label>
                        <input type="text" id="eKet" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-box" id="toastBox"></div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const BASE = '/pengeluaran-harian';
    let role = @json($role);          // null | 'input' | 'admin'
    let tanggal = todayStr();
    let pin = '';
    let padLocked = false;

    /* ---------- helpers ---------- */
    function todayStr(d = new Date()) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }
    function fmtRupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
    function fmtTanggal(str, withWeekday = true) {
        const opt = withWeekday
            ? { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
            : { day: 'numeric', month: 'long', year: 'numeric' };
        return new Date(str + 'T00:00:00').toLocaleDateString('id-ID', opt);
    }
    function fmtJam(iso) {
        return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    function toast(msg, type = 'success') {
        const el = $('<div class="toast-item ' + type + '"><i class="bi ' +
            (type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') + '"></i><span>' + msg + '</span></div>');
        $('#toastBox').append(el);
        setTimeout(() => el.fadeOut(300, () => el.remove()), 2600);
    }
    function sanitizeNominal(el) {
        el.value = el.value.replace(/[^\d]/g, '');
    }

    /* ---------- PIN screen ---------- */
    function renderDots(err = false) {
        const dots = $('#pinDots .pin-dot');
        dots.removeClass('filled');
        $('#pinDots').toggleClass('error', err);
        for (let i = 0; i < pin.length; i++) dots.eq(i).addClass('filled');
    }

    $('#pinPad').on('click', '.pin-key', function () {
        if (padLocked) return;
        const k = $(this).data('key');
        if (k === 'back') { pin = pin.slice(0, -1); renderDots(); return; }
        if (k === 'clear') { pin = ''; renderDots(); $('#pinError').text(''); return; }
        if (pin.length >= 6) return;
        pin += k;
        renderDots();
        if (pin.length === 6) {
            const sentPin = pin;
            setTimeout(() => submitPin(sentPin), 180);
        }
    });

    function submitPin(value) {
        $.post(BASE + '/pin', { pin: value })
            .done(function (res) {
                if (res.status) {
                    role = res.role;
                    enterMain();
                }
            })
            .fail(function (xhr) {
                const res = xhr.responseJSON || {};
                pin = '';
                if (res.locked) {
                    padLocked = true;
                    $('#pinError').text(res.message);
                    $('#pinPad').css('opacity', '0.4');
                    setTimeout(() => { padLocked = false; $('#pinPad').css('opacity', '1'); $('#pinError').text(''); }, 60000);
                } else {
                    renderDots(true);
                    $('#pinError').text(res.message || 'PIN salah');
                    $('#pinDots').addClass('shake');
                    setTimeout(() => { $('#pinDots').removeClass('shake'); renderDots(); }, 450);
                }
            });
    }

    function enterMain() {
        $('#pinScreen').addClass('d-none');
        $('#mainScreen').removeClass('d-none');
        applyRoleUI();
        loadData();
    }

    function applyRoleUI() {
        const isAdmin = role === 'admin';
        $('#dateNav').toggleClass('d-none', !isAdmin);
        const badge = $('#modeBadge');
        badge.text(isAdmin ? 'Mode Admin' : 'Mode Input');
        badge.toggleClass('admin', isAdmin);
        $('#topDate').text(isAdmin ? '' : fmtTanggal(tanggal));
    }

    /* ---------- data ---------- */
    function loadData() {
        const url = role === 'admin' ? BASE + '/data?tanggal=' + tanggal : BASE + '/data';
        $.get(url).done(function (res) {
            renderList(res);
        }).fail(function () {
            toast('Gagal memuat data', 'error');
        });
    }

    function renderList(res) {
        tanggal = res.tanggal;
        $('#totalAmount').text(fmtRupiah(res.total));
        const n = res.data.length;
        $('#totalCount').text(n + ' transaksi');

        if (role === 'admin') {
            $('#navDateLabel').text(fmtTanggal(tanggal));
            $('#topDate').text(fmtTanggal(tanggal, false));
            $('#nextDay').prop('disabled', tanggal >= todayStr());
        }

        const list = $('#expList');
        if (!n) {
            list.html('<div class="empty-state"><i class="bi bi-receipt"></i><p>Belum ada pengeluaran<br>pada tanggal ini</p></div>');
            return;
        }

        list.html(res.data.map(function (it) {
            const actions = role === 'admin'
                ? '<div class="exp-actions">' +
                  '<button type="button" class="act-btn edit" data-edit="' + it.id + '" data-nama="' + escapeHtml(it.nama) + '" data-nominal="' + it.nominal + '" data-ket="' + escapeHtml(it.keterangan || '') + '" title="Ubah"><i class="bi bi-pencil"></i></button>' +
                  '<button type="button" class="act-btn delete" data-del="' + it.id + '" title="Hapus"><i class="bi bi-trash"></i></button>' +
                  '</div>'
                : '';
            return '<div class="exp-item">' +
                '<div class="exp-icon"><i class="bi bi-cart"></i></div>' +
                '<div class="exp-body">' +
                  '<div class="exp-nama">' + escapeHtml(it.nama) + '</div>' +
                  (it.keterangan ? '<div class="exp-meta">' + escapeHtml(it.keterangan) + '</div>' : '') +
                  '<div class="exp-meta">' + fmtJam(it.created_at) + '</div>' +
                '</div>' +
                '<div class="exp-right">' +
                  '<div class="exp-nominal">' + fmtRupiah(it.nominal) + '</div>' +
                  actions +
                '</div>' +
              '</div>';
        }).join(''));
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ---------- date nav (admin) ---------- */
    function shiftDay(delta) {
        const d = new Date(tanggal + 'T00:00:00');
        d.setDate(d.getDate() + delta);
        tanggal = todayStr(d);
        loadData();
    }
    $('#prevDay').on('click', () => shiftDay(-1));
    $('#nextDay').on('click', () => { if (tanggal < todayStr()) shiftDay(1); });
    $('#todayBtn').on('click', () => { tanggal = todayStr(); loadData(); });

    /* ---------- tambah ---------- */
    $('#fNominal').on('input', function () { sanitizeNominal(this); });

    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        const data = {
            nama: $('#fNama').val().trim(),
            nominal: $('#fNominal').val(),
            keterangan: $('#fKet').val().trim(),
        };
        if (role === 'admin') data.tanggal = tanggal;
        if (!data.nama || !data.nominal) return;

        const btn = $('#btnSave');
        btn.prop('disabled', true);
        $.post(BASE, data)
            .done(function (res) {
                toast(res.message || 'Tersimpan');
                $('#addForm')[0].reset();
                loadData();
            })
            .fail(function (xhr) {
                const res = xhr.responseJSON || {};
                const msg = res.message || (res.errors ? Object.values(res.errors).flat().join(', ') : 'Gagal menyimpan');
                toast(msg, 'error');
            })
            .always(() => btn.prop('disabled', false));
    });

    /* ---------- edit & hapus (admin) ---------- */
    $('#expList').on('click', '[data-edit]', function () {
        const btn = $(this);
        $('#eId').val(btn.data('edit'));
        $('#eNama').val(btn.data('nama'));
        $('#eNominal').val(btn.data('nominal'));
        $('#eKet').val(btn.data('ket') || '');
        new bootstrap.Modal('#editModal').show();
    });

    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#eId').val();
        const data = {
            nama: $('#eNama').val().trim(),
            nominal: $('#eNominal').val(),
            keterangan: $('#eKet').val().trim(),
        };
        $.ajax({ url: BASE + '/' + id, method: 'PUT', data: data })
            .done(function (res) {
                bootstrap.Modal.getInstance($('#editModal'))?.hide();
                toast(res.message || 'Tersimpan');
                loadData();
            })
            .fail(function (xhr) {
                const res = xhr.responseJSON || {};
                const msg = res.message || (res.errors ? Object.values(res.errors).flat().join(', ') : 'Gagal mengubah');
                toast(msg, 'error');
            });
    });

    $('#expList').on('click', '[data-del]', function () {
        const id = $(this).data('del');
        const nama = $(this).closest('.exp-item').find('.exp-nama').text();
        Swal.fire({
            title: 'Hapus pengeluaran?',
            text: nama,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            background: '#1e293b',
            color: '#f1f5f9',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({ url: BASE + '/' + id, method: 'DELETE' })
                .done(function (res) { toast(res.message || 'Terhapus'); loadData(); })
                .fail(function () { toast('Gagal menghapus', 'error'); });
        });
    });

    /* ---------- keluar ---------- */
    $('#btnLogout').on('click', function () {
        $.post(BASE + '/keluar').always(function () { location.reload(); });
    });

    /* ---------- init ---------- */
    if (role) {
        applyRoleUI();
        loadData();
    }
</script>
</body>
</html>
