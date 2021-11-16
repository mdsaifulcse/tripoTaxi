<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <style>
        #earningTab .nav-pills>li>a {
            border-radius: 4px;
        }

        .nav {
            display: inline-flex;
        }

        .date {
            padding-top: 10px;
        }

        .date .start-date,
        .date .end-date {
            font-weight: bolder;
            color: purple;
        }

        .week-title {
            font-size: 2rem;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .weekdata {
            margin-top: 40px;
        }

        .dataTables_length {
            display: none;
        }
    </style>
</head>

<body>
    <div id="earningTab" class="container mx-auto text-center">
        <ul class="nav nav-pills">
            <li class="active"><a href="#weekly" data-toggle="tab">Week</a></li>
            <li><a href="#total" data-toggle="tab">Total</a></li>
        </ul>
        <div class="tab-content clearfix text-left">
            <div class="tab-pane active" id="weekly">
                <div class="text-center date">
                    <span class="start-date">{{ \Carbon\Carbon::today()->subDays(6)->format('d/m/Y') }}</span> - <span class="end-date">{{ \Carbon\Carbon::today()->format('d/m/Y') }}</span>
                </div>
                <canvas id="weeklyChart" width="400" height="400"></canvas>
                <div class="weekdata">
                    <h3 class="text-center"> <strong> WEEK SUMMARY </strong></h3>
                    <div class="row">
                        <div class="col-xs-6 text-center">
                            <p class="week-title"><strong>{{ currency($weekRides->sum('payment.provider_pay')/$weekRides->count()) }}</strong></p>
                            <p>Earn Per Trips(avg)</p>
                        </div>
                        <div class="col-xs-6 text-center">
                            <p class="week-title"><strong>{{ $weekRides->count() }}</strong></p>
                            <p>Total Trips</p>
                        </div>
                        <br><br><br><br><br>
                        <div class="col-xs-12">
                            <span>Total Earnings</span>
                            <span class="money-right pull-right">{{ currency($weekRides->sum('payment.provider_pay')) }}</span>
                        </div>
                        <div class="col-xs-12">
                            <span>Commissions</span>
                            <span class="money-right pull-right"> - {{ currency($weekRides->sum('payment.commision')) }}</span>
                        </div>
                        <div class="col-xs-12">
                            <h4> <strong> Net Earning <span class="pull-right">{{ currency($weekRides->sum('payment.provider_pay') - $weekRides->sum('payment.commision')) }}</span></strong> </h4>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-xs-12">
                            <table class="table table-hover table-striped">
                                <thead>
                                  <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($weekRides as $ride)
                                    <tr>
                                        <td scope="row">{{ $ride->booking_id }}</td>
                                        <th>{{ $ride->created_at->diffForHumans() }}</th>
                                        <td>{{ currency($ride->payment->provider_pay) }}</td>
                                    </tr>
                                    @empty
                                    <td colspan="3" class="text-center">No Rides Found</td>
                                    @endforelse
                                </tbody>
                              </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="total">
                <canvas id="totalChart" width="400" height="400"></canvas>
                <div class="weekdata">
                    <h3 class="text-center"> <strong> Total SUMMARY </strong></h3>
                    <div class="row">
                        <div class="col-xs-4 text-center">
                            <p class="week-title"><strong>{{ currency($total->sum('payment.provider_pay')) }}</strong></p>
                            <p>Earn Per Trips(avg)</p>
                        </div>
                        <div class="col-xs-4 text-center">
                            <p class="week-title"><strong>{{ $total->count() }}</strong></p>
                            <p>Completed</p>
                        </div>
                        <div class="col-xs-4 text-center">
                            <p class="week-title"><strong>{{ $totalCancelled }}</strong></p>
                            <p>Cancelled</p>
                        </div>
                        <br><br><br><br><br>
                        <div class="col-xs-12">
                            <span>Total Earnings</span>
                            <span class="money-right pull-right">{{ currency($total->sum('payment.provider_pay')) }}</span>
                        </div>
                        <div class="col-xs-12">
                            <span>Commissions</span>
                            <span class="money-right pull-right"> - {{ currency($total->sum('payment.commision')) }}</span>
                        </div>
                        <div class="col-xs-12">
                            <h4> <strong> Net Earning <span class="pull-right">{{ currency($total->sum('payment.provider_pay') - $total->sum('payment.commision')) }}</span></strong> </h4>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-xs-12">
                            <table class="table table-hover table-striped">
                                <thead>
                                  <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($weekRides as $ride)
                                    <tr>
                                        <td scope="row">{{ $ride->booking_id }}</td>
                                        <th>{{ $ride->created_at->diffForHumans() }}</th>
                                        <td>{{ currency($ride->payment->provider_pay) }}</td>
                                    </tr>
                                    @empty
                                    <td colspan="3" class="text-center">No Rides Found</td>
                                    @endforelse
                                </tbody>
                              </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>

    <script>
        $('table').DataTable({searching: false, info: false, ordering: false});
        // Weekly Earnings Chart
        const weekData = ['{{$seven}}', '{{$six}}', '{{$five}}', '{{$four}}', '{{$three}}', '{{$two}}', '{{$one}}' ];
        var ctx = document.getElementById('weeklyChart').getContext('2d');
        var weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                
                labels: [
                    '{{ substr(\Carbon\Carbon::today()->subDays(6)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->subDays(5)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->subDays(4)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->subDays(3)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->subDays(2)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->subDays(1)->format("l"), 0, 3) }}',
                    '{{ substr(\Carbon\Carbon::today()->format("l"), 0, 3) }}'
                    
                    ],
                datasets: [{
                    label: 'Total Earning',
                    data: weekData,
                    borderRadius: 5,
                    hoverBorderRadius: 0,
                    backgroundColor: [
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 3)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(25, 139, 34, 1)'
                    ]
                }]
            },
            options: {
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                            lineWidth: 0
                        }
                    },
                    y: {
                        grid: {
                            display: false,
                            drawBorder: false,
                            lineWidth: 0
                        }
                    }
                }
            }
        });

        // Monthly Earning Charts
        const totalData = ['{{ $total->where("status", "=", "COMPLETED")->count() }}', '{{ $totalCancelled }}'];
        var ctx = document.getElementById('totalChart').getContext('2d');
        var weeklyChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed Rides', 'Cancelled Ride'],
                datasets: [{
                    label: 'Total Earning',
                    data: totalData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 3)',
                        'rgba(54, 162, 235, 1)'
                    ]
                }]
            },
        });
    </script>
</body>

</html>