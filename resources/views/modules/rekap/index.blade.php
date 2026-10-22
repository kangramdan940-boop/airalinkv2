@extends('layouts.app')

@section('content')

{{-- Filter Tanggal --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.2rem 1.5rem;">
        <form method="GET" action="{{ url('/rekap-pengeluaran') }}" style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            <div>
                <label class="form-label">Mulai Tanggal</label>
                <input type="date" name="mulai" class="form-control" value="{{ $mulai->format('Y-m-d') }}">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="{{ $sampai->format('Y-m-d') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Submit
                </button>
            </div>
            {{-- Total Pengeluaran --}}
            <div style="margin-left: auto;">
                <div style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(139, 92, 246, 0.15)); border: 1.5px solid rgba(244, 63, 94, 0.4); border-radius: 12px; padding: 0.6rem 1.5rem; text-align: center;">
                    <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(253, 164, 175, 0.8); margin-bottom: 0.2rem;">Total Pengeluaran</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #fff;">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Chart Pengeluaran --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h5 style="font-weight: 700; margin: 0; color: #fff;">Grafik Pengeluaran</h5>
            <div class="chart-toggle">
                <button type="button" class="chart-btn active" data-period="daily">Harian</button>
                <button type="button" class="chart-btn" data-period="weekly">Mingguan</button>
                <button type="button" class="chart-btn" data-period="monthly">Bulanan</button>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid-4" style="margin-bottom: 2rem; grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pengeluaran</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-graph-up"></i></div>
        <div class="stat-value">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
        <div class="stat-label">Rata-rata / Hari ({{ $jumlahHari }} hari)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-calendar-date"></i></div>
        <div class="stat-value" style="font-size: 1.2rem; padding-top: 0.3rem;">
            {{ $hariTerbesar ? Carbon\Carbon::parse($hariTerbesar->tanggal)->translatedFormat('d M Y') : '-' }}
        </div>
        <div class="stat-label">
            Hari Terbesar
            @if($hariTerbesar)
                <span style="color: #fda4af; font-weight: 600;">Rp {{ number_format($hariTerbesar->total, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .chart-toggle {
        display: flex;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 3px;
        gap: 2px;
    }
    .chart-btn {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .chart-btn.active {
        background: rgba(244, 63, 94, 0.3);
        color: #fda4af;
        border: 1px solid rgba(244, 63, 94, 0.4);
    }
    .chart-btn:hover:not(.active) {
        color: rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.05);
    }
    @media (max-width: 768px) {
        .grid-4[style*="repeat(3"] { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('expenseChart').getContext('2d');
    let expenseChart = null;
    let currentPeriod = 'daily';

    function createGradient(ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(244, 63, 94, 0.4)');
        gradient.addColorStop(1, 'rgba(244, 63, 94, 0.0)');
        return gradient;
    }

    function loadChart(period) {
        currentPeriod = period;
        const params = new URLSearchParams();
        params.set('period', period);

        // Pass date filter if present in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('mulai')) params.set('mulai', urlParams.get('mulai'));
        if (urlParams.has('sampai')) params.set('sampai', urlParams.get('sampai'));

        fetch(`{{ url('/rekap-pengeluaran/chart-data') }}?${params.toString()}`)
            .then(res => {
                if (res.status === 403) { location.reload(); return null; }
                return res.json();
            })
            .then(result => {
                if (!result) return;

                if (expenseChart) {
                    expenseChart.destroy();
                }

                expenseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: result.labels,
                        datasets: [{
                            label: 'Pengeluaran',
                            data: result.data,
                            borderColor: 'rgba(244, 63, 94, 1)',
                            backgroundColor: createGradient(ctx),
                            borderWidth: 2.5,
                            pointBackgroundColor: 'rgba(244, 63, 94, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleColor: '#f1f5f9',
                                bodyColor: '#fda4af',
                                borderColor: 'rgba(244, 63, 94, 0.3)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                titleFont: { weight: '600' },
                                bodyFont: { size: 14, weight: '700' },
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.5)',
                                    font: { size: 11 },
                                    maxRotation: 45,
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.5)',
                                    font: { size: 11 },
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'jt';
                                        if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
                                        return value;
                                    }
                                },
                                beginAtZero: true,
                            }
                        }
                    }
                });
            });
    }

    // Period toggle buttons
    document.querySelectorAll('.chart-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-btn[data-period]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadChart(this.dataset.period);
        });
    });

    // Load default (daily)
    loadChart('daily');
</script>
@endpush
