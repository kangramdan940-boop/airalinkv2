@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 65vh;">
    <div class="card" style="max-width: 340px; width: 100%;">
        <div class="card-body" style="padding: 2.2rem 2rem; text-align: center;">
            <div style="width: 56px; height: 56px; margin: 0 auto 1rem; background: linear-gradient(135deg, rgba(244,63,94,0.25), rgba(139,92,246,0.2)); border: 1px solid rgba(244,63,94,0.35); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-shield-lock-fill" style="font-size: 1.5rem; color: #fda4af;"></i>
            </div>
            <h5 style="font-weight: 700; margin-bottom: 0.3rem;">Rekap Pengeluaran</h5>
            <p style="font-size: 0.82rem; color: var(--text-secondary);">Masukkan PIN 6 angka untuk melanjutkan</p>

            <div id="pinDots" style="display: flex; gap: 0.7rem; justify-content: center; margin: 1.4rem 0 0.5rem;">
                <span class="rekap-dot"></span><span class="rekap-dot"></span><span class="rekap-dot"></span>
                <span class="rekap-dot"></span><span class="rekap-dot"></span><span class="rekap-dot"></span>
            </div>
            <div id="pinError" style="font-size: 0.8rem; color: #f87171; min-height: 1.2rem;"></div>

            <div id="pinPad" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem; margin-top: 1rem;">
                <button type="button" class="rekap-key" data-key="1">1</button>
                <button type="button" class="rekap-key" data-key="2">2</button>
                <button type="button" class="rekap-key" data-key="3">3</button>
                <button type="button" class="rekap-key" data-key="4">4</button>
                <button type="button" class="rekap-key" data-key="5">5</button>
                <button type="button" class="rekap-key" data-key="6">6</button>
                <button type="button" class="rekap-key" data-key="7">7</button>
                <button type="button" class="rekap-key" data-key="8">8</button>
                <button type="button" class="rekap-key" data-key="9">9</button>
                <button type="button" class="rekap-key rekap-key-ghost" data-key="clear">C</button>
                <button type="button" class="rekap-key" data-key="0">0</button>
                <button type="button" class="rekap-key rekap-key-ghost" data-key="back"><i class="bi bi-backspace"></i></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rekap-dot {
        width: 14px; height: 14px; border-radius: 50%;
        border: 2px solid var(--text-muted);
        transition: all 0.15s ease;
        display: inline-block;
    }
    .rekap-dot.filled {
        background: #f43f5e;
        border-color: #f43f5e;
        box-shadow: 0 0 10px rgba(244,63,94,0.6);
        transform: scale(1.1);
    }
    #pinDots.error .rekap-dot { border-color: #ef4444; background: rgba(239,68,68,0.25); }
    .shake { animation: rekapShake 0.4s ease; }
    @keyframes rekapShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-8px); }
        40%, 80% { transform: translateX(8px); }
    }
    .rekap-key {
        height: 56px;
        border: none;
        border-radius: 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 1.25rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .rekap-key:active { background: rgba(244,63,94,0.2); transform: scale(0.96); }
    .rekap-key-ghost { background: transparent; border-color: transparent; font-size: 1rem; color: var(--text-secondary); }
</style>
@endpush

@push('scripts')
<script>
    let pin = '';

    function renderDots(err = false) {
        const dots = document.querySelectorAll('#pinDots .rekap-dot');
        dots.forEach(d => d.classList.remove('filled'));
        document.getElementById('pinDots').classList.toggle('error', err);
        for (let i = 0; i < pin.length; i++) dots[i].classList.add('filled');
    }

    document.getElementById('pinPad').addEventListener('click', function (e) {
        const btn = e.target.closest('.rekap-key');
        if (!btn) return;

        const k = btn.dataset.key;
        if (k === 'back') { pin = pin.slice(0, -1); renderDots(); return; }
        if (k === 'clear') { pin = ''; renderDots(); document.getElementById('pinError').textContent = ''; return; }
        if (pin.length >= 6) return;

        pin += k;
        renderDots();

        if (pin.length === 6) {
            const sentPin = pin;
            setTimeout(function () {
                $.post('{{ url('/rekap-pengeluaran/pin') }}', { pin: sentPin })
                    .done(function (res) {
                        if (res.status) location.reload();
                    })
                    .fail(function (xhr) {
                        const res = xhr.responseJSON || {};
                        pin = '';
                        renderDots(true);
                        document.getElementById('pinError').textContent = res.message || 'PIN salah';
                        document.getElementById('pinDots').classList.add('shake');
                        setTimeout(function () {
                            document.getElementById('pinDots').classList.remove('shake');
                            renderDots();
                        }, 450);
                    });
            }, 180);
        }
    });
</script>
@endpush
