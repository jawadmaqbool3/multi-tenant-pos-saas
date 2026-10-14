<script>
    $(document).ready(function() {
        var productID = '';

        $(document).bind("keydown", (event) => {
            if ($('#add_stock_modal').hasClass('show') ||
                $('#place_order_modal').hasClass('show')) {
                if (event.code == "Enter") {
                    selectLastProduct(productID);
                    productID = '';
                } else {
                    productID += event.key;
                }
            } else {
                productID = '';
            }
        });


        function selectLastProduct(productID) {
            var shouldContinue = false;
            for (var i = 0, len = products.length; i < len; i++) {
                if (products[i]["code"] == productID) {
                    shouldContinue = true;
                    break;
                }
            }
            if (!shouldContinue) {
                toastr.warning(
                    "Product not found.",
                    "warning", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    }
                );
                return;
            }
            let modal = null;
            let alreadyRegistered = [];
            @can('add stock')
                if ($('#add_stock_modal').hasClass('show')) {

                    modal = '#add_stock_modal';
                    alreadyRegistered = $('#add_stock_modal #product_wrapper .product').find(
                        `[data-product-no=${productID}]:selected`);
                }
            @endcan
            @can('place order')
                if ($('#place_order_modal').hasClass('show')) {
                    modal = '#place_order_modal';
                    alreadyRegistered = $('#place_order_modal #product_wrapper .product').find(
                        `[data-product-no=${productID}]:selected`);
                }
            @endcan
            alreadyRegistered.each(function() {
                let previousQuantity = $(this).parents('tr').find('.quantity');
                previousQuantity.val(Number(previousQuantity.val()) + 1);
                previousQuantity.change();
            });

            if (alreadyRegistered.length == 0) {
                @can('place order')
                    if (modal == '#place_order_modal') {
                        addOrderRow();
                    }
                @endcan
                @can('add stock')
                    if (modal == '#add_stock_modal') {
                        addStockRow();
                    }
                @endcan
                let lastChild = $(modal).find('#product_wrapper tr:last-child');
                lastChild.find(`.product option[data-product-no=${productID}]`).attr('selected', true);
                lastChild.find('.quantity').val(1);
                lastChild.find(`.product`).change();
            }
            productID = '';
            calculateTotal();
        }


        $('#add_stock_modal ._select2').select2({
            dropdownParent: $('#add_stock_modal #dropdown_parent')
        });
        $('#place_order_modal ._select2').select2({
            dropdownParent: $('#place_order_modal #dropdown_parent')
        });
        var products = {};
        getCharts();


        const directionPostive = (inverse = false) => {
            return `<span class="badge ${inverse? 'badge-light-danger': 'badge-light-success' }  fs-base">
                <span class="svg-icon svg-icon-5 ${inverse? 'svg-icon-danger': 'svg-icon-success' } ms-n1">
                    <x-icon name="arrow-up" :size="16" />
                </span>
            </span>`;
        }
        const directionNegetive = (inverse = false) => {
            return `<span class="badge ${inverse? 'badge-light-success': 'badge-light-danger' } fs-base">
                        <span class="svg-icon svg-icon-5 ${inverse? 'svg-icon-success': 'svg-icon-danger' } ms-n1">
                            <x-icon name="arrow-down" :size="16" />
                        </span>
                    </span>`;
        }
        @can('dashboard daily average sales')
            function averageSalesChart(series) {
                const C = UiTheme.palette();
                $('#average_sale_wrapper').text(convertToAbbreviation(series.averageSalePerDay));
                if (series.direction == 'positive') {
                    $('#average_sale_direction').html(directionPostive)
                } else {
                    $('#average_sale_direction').html(directionNegetive)
                }
                var options = {
                    series: [{
                        name: 'Per day sales',
                        data: series.sales
                    }],
                    chart: {
                        height: 150,
                        type: 'bar',
                        toolbar: {
                            show: false
                        },
                        background: 'none',

                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 5,
                        }
                    },
                    dataLabels: {
                        enabled: false,
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: [C.label]
                        }
                    },

                    xaxis: {
                        categories: series.days,
                        position: 'bottom',
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        crosshairs: {
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    colorFrom: C.primary,
                                    colorTo: C.primary,
                                    stops: [0, 100],
                                    opacityFrom: 0.18,
                                    opacityTo: 0.04,
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false,
                        },
                        labels: {
                            show: false,
                            formatter: function(val) {
                                return convertToAbbreviation(val);
                            }
                        }

                    },
                };

                var chart = new ApexCharts(document.querySelector("#average_sales_chart"), options);
                chart.render();
            }
        @endcan
        @can('dashboard profit loss report')
            function profitChart(profit) {
                const C = UiTheme.palette();
                let revenueWrapper = profit.revenue;
                let expenseWrapper = profit.expense;
                let direction = profit.direction;
                $('#revenue_wrapper').text(convertToAbbreviation(revenueWrapper));
                $('#expense_wrapper').text(convertToAbbreviation(expenseWrapper));
                $('#profit_wrapper').text(convertToAbbreviation(revenueWrapper - expenseWrapper));
                if (direction == 'positive') {
                    $('#profit_direction').html(directionPostive)
                } else {
                    $('#profit_direction').html(directionNegetive)
                }
                new ApexCharts(document.querySelector("#profit_chart"), {
                    series: [revenueWrapper, expenseWrapper],
                    chart: {
                        width: 150,
                        height: 150,
                        type: 'donut',
                        toolbar: {
                            show: false
                        },
                    },
                    labels: ['Revenue', 'Expense'],
                    legend: {
                        show: false
                    },
                    colors: [C.primary, C.danger],
                    dataLabels: {
                        enabled: false
                    },
                }).render();
            }
        @endcan
        @can('dashboard monthly sales')
            function monthlySalesCard(sales) {
                $("#sales_this_month .amount").html(convertToAbbreviation(sales.amount))
                $("#sales_this_month .direction").html(sales.direction == "positive" ? directionPostive :
                    directionNegetive);
                $("#sales_this_month .count").html(convertToAbbreviation(sales.count))
            }
        @endcan
        @can('dashboard monthly orders')
            function monthlyOrdersCard(orders) {
                $("#orders_this_month .amount").html(convertToAbbreviation(orders.amount))
                $("#orders_this_month .direction").html(orders.direction == "positive" ? directionPostive :
                    directionNegetive);
                $("#orders_this_month .count").html(convertToAbbreviation(orders.count))
            }
        @endcan
        @can('dashboard monthly cancelled orders')
            function cancelledOrdersCard(orders) {
                $("#cancelled_this_month .amount").html(convertToAbbreviation(orders.amount))
                $("#cancelled_this_month .direction").html(orders.direction == "positive" ? directionPostive(
                        true) :
                    directionNegetive(true));
                $("#cancelled_this_month .count").html(convertToAbbreviation(orders.count))
            }
        @endcan
        @can('dashboard monthly refunded orders')
            function refundedOrderMonth(orders) {
                $("#refunded_this_month .amount").html(convertToAbbreviation(orders.amount))
                $("#refunded_this_month .direction").html(orders.direction == "positive" ? directionPostive(
                        true) :
                    directionNegetive(true));
                $("#refunded_this_month .count").html(convertToAbbreviation(orders.count))
            }
        @endcan
        @can('dashboard monthly customers')
            function customerCard(customers) {
                $("#customer_card .count").html(convertToAbbreviation(customers.thisMonthCustomers))
                $("#customer_card .direction").html(customers.direction == "positive" ? directionPostive(true) :
                    directionNegetive(true));
                if (customers.todayHeroes.length != 0) {
                    for (let index = 0; index < customers.todayHeroes.length && index < 7; index++) {
                        const hero = customers.todayHeroes[index];
                        let avatar = null;
                        const palette = ['primary', 'success', 'warning', 'info'];
                        let color = palette[(hero.name || '?').charCodeAt(0) % palette.length];
                        if (hero.image != false) {
                            avatar = `<div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                                        title="${hero.name}">
                                        <img alt="Pic" src="{{ url('/assets/media/users/profile') }}/${hero.image}" />
                                    </div>`
                        } else {
                            avatar = `<div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                                        title="${hero.name}">
                                        <span class="symbol-label bg-${color} text-inverse-${color} fw-bold">${hero.name.charAt(0)}</span>
                                    </div>`;
                        }
                        $("#customer_card .hero-list").append(avatar);
                    }
                    if (customers.todayHeroes.length > 7) {
                        $("#customer_card .hero-list").append(
                            `<a href="#" class="symbol symbol-35px symbol-circle" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_view_users">
                                        <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+${customers.todayHeroes.length - 7}</span>
                                    </a>`
                        );
                    }
                } else {
                    $('#customer_card .hero-list').parents('.card-body').html('');
                }

            }
        @endcan
        @can('dashboard recent orders')
            function recentOrderCard(orders) {
                if (orders.length != 0) {
                    for (let index = 0; index < orders.length && index < 7; index++) {
                        const order = orders[index];
                        let color = 'success';
                        if (order.status == 'refunded') {
                            color = 'danger';
                        } else if (order.status == 'cancelled') {
                            color = 'warning';
                        } else if (order.status == 'generated') {
                            color = 'success';
                        } else if (order.status == 'paid') {
                            color = 'primary';
                        }
                        let user = order.order_user && order.order_user.length ? order.order_user[0].user : null;
                        if (user) {
                            user =
                                `To: <a href="#" class="text-gray-800 text-hover-primary fw-bold">${user.name}</a>`;
                        } else {
                            user = 'Instore Order';
                        }

                        $('#recent_order_card').append(`<div class="border border-dashed border-gray-300 rounded px-7 py-3 mb-6">
                                        <h4>${order.order_no}</h4>
                                        <div class="d-flex flex-stack">
                                            <span class="text-gray-400 fw-bold">${user}</span>
                                            <span class="badge badge-light-${color}">${order.status}</span>
                                        </div>
                                    </div>`);
                    }
                } else {
                    $('#recent_order_card').append(`<div class="border border-dashed border-gray-300 rounded px-7 py-3 mb-6">
                                        <h4>Nothing found</h4>
                                    </div>`)
                }
            }
        @endcan
        $(document).on('click', '.btn-add-stock, .btn-place-order', function() {
            $('#add_stock_modal #product_wrapper, #place_order_modal #product_wrapper').empty();
            $('#add_stock_modal #total, #place_order_modal #total').val(0);
            getInitialParams();
        });

        function getInitialParams() {
            $.ajax({
                url: window.location.href + "?ajax=true",
                method: "GET",
                success: function(response) {
                    if (response.success) {
                        products = response.products;
                    }
                },
                error: function(response) {
                    toastr.error(
                        response.message,
                        "Error", {
                            timeOut: 5000,
                            extendedTimeOut: 0,
                            closeButton: true,
                            closeDuration: 0
                        }
                    );

                },
                complete: function() {}
            });
        }

        function getCharts() {
            $.ajax({
                url: window.location.href + "?charts=true&ajax=true",
                method: "GET",
                success: function(response) {
                    if (response.success) {

                        @can('dashboard recent orders')
                            recentOrderCard(response.recentOrders);
                        @endcan
                        @can('dashboard profit loss report')
                            profitChart(response.profitChart);
                        @endcan
                        @can('dashboard daily average sales')
                            averageSalesChart(response.averageSalesChart);
                        @endcan
                        @can('dashboard monthly sales')
                            monthlySalesCard(response.salesThisMonth);
                        @endcan
                        @can('dashboard monthly orders')
                            monthlyOrdersCard(response.ordersThisMonth);
                        @endcan
                        @can('dashboard monthly cancelled orders')
                            cancelledOrdersCard(response.cancelledThisMonth);
                        @endcan
                        @can('dashboard monthly refunded orders')
                            refundedOrderMonth(response.refundedThisMonth);
                        @endcan
                        @can('dashboard monthly customers')
                            customerCard(response.cutomers);
                        @endcan

                    }
                },
                complete: function() {}
            });
        }

        @can('add stock')
            @include('dashboard.add_stock_js')
        @endcan

        @can('place order')
            @include('dashboard.place_order_js')




            $(document).on('change', '.payment-check', function () {  
                if($(this).is(":checked"))
                {
                    $(this).parents('.modal').find('#payment_method').removeClass('d-none');
                }
                else
                {
                    $(this).parents('.modal').find('#payment_method').addClass('d-none');
                }
            });


        @endcan




        document.addEventListener("table_reload", function() {
            $('#add_stock_modal_cancel').click();
            $('#place_order_modal_cancel').click();
        });

        @can('dashboard employee mark attendance')
            @include('employee::reports.employee.attendance_js')

            getAttendance("{{ @$user->uid ?? auth()->user()->uid }}");
            $(document).on('change', '#attendance_month', function() {
                getAttendance("{{ auth()->user()->uid }}", $('#attendance_month').val());
            });
            document.addEventListener("clockin", function() {
                $('#attendance_form').html(`
                                        <input type="submit" name="clockout" class="btn-sm btn btn-danger"
                                            value="Clock out" />`);
                getAttendance("{{ auth()->user()->uid }}");

            });
            document.addEventListener("clockout", function() {
                $('#attendance_form').html(`
                                        <input type="submit" name="clockin" class="btn-sm btn btn-success"
                                            value="Clock in" />`);
                getAttendance("{{ auth()->user()->uid }}");

            });
        @endcan


    });
</script>
