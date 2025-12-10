<!-- satır 20den sonra-->

<!-- satır 83 row aç-->

<div class="row">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">Bar Chart</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="chart">
                <canvas id="kayitlarChart" width="200" height="100"></canvas>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
</div>

@section('js')
    <!-- ChartJS -->
    <script src="{{url('/admin/plugins/chart.js/Chart.min.js')}}"></script>

    <script>
        const chart = document.getElementById.$(#kayitlarChart).getContext('2d');

        const kayitlarChart = new Chart(barChartCanvas, {
            type: 'bar',
            data: .....,
            options: {
                scales: {
                    y: {
                       beginAtZero: true,
                       precision: 0,
                    }
                }
            }
        })
    </script>
@endsection
