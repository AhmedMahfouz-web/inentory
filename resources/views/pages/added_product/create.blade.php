@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />

    <link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/pickr/pickr-themes.css') }}" />

    <style>
        .product-item-container {
            transition: all 0.25s ease-in-out;
        }

        /* Red highlight when stock is 0 or less */
        .product-item-container.has-stock-error {
            border: 2px solid #ea5455 !important;
            background-color: #fff5f5 !important;
            box-shadow: 0 3px 10px rgba(234, 84, 85, 0.25) !important;
        }

        .product-item-container.has-stock-error .select2-container--default .select2-selection {
            border-color: #ea5455 !important;
            background-color: #fff0f1 !important;
        }

        .product-item-container.has-stock-error .repeater-title {
            color: #ea5455 !important;
            font-weight: bold;
        }

        .stock-error-feedback {
            color: #ea5455;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 6px;
            animation: stockErrorFadeIn 0.3s ease-in-out;
        }

        .btn-delete-row {
            cursor: pointer;
            color: #a1acb8;
            font-size: 1.25rem;
            transition: all 0.2s ease;
        }

        .btn-delete-row:hover {
            color: #ea5455 !important;
            transform: scale(1.15);
        }

        @keyframes stockErrorFadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row invoice-add position-relative">
            <!-- Invoice Add-->
            <div class="col-lg-9 col-12 mb-lg-0 mb-4">
                <div class="card invoice-preview-card">

                    <div class="card-body">
                        <!-- Server session error alert if any -->
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-alert-circle ti-md me-2"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Dynamic Page Stock Error Alert Banner -->
                        <div id="stock-error-alert" class="alert alert-danger alert-dismissible fade show d-none mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-alert-circle ti-md me-2"></i>
                                <div>
                                    <h5 class="alert-heading mb-1 text-danger">خطأ: لا يمكن إتمام الصرف</h5>
                                    <div id="stock-error-message">
                                        يوجد صنف أو أكثر كميته في المخزن الرئيسي (0). برجاء تعديل أو إزالة الأصناف المحددة باللون الأحمر.
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <form id="create" action="{{ route('exchange product') }}" method="post">
                            <div class="row p-sm-4 p-0">
                                <div class="col-md-6 col-sm-5 col-12 mb-sm-0 mb-4">
                                    <h6 class="mb-4">الفرع :</h6>

                                    <div class="col-sm-12 col-md-6 mb-3">
                                        <label for="flatpickr-date" class="form-label">التاريخ</label>
                                        <input type="text" name="created_at" form="create"
                                            class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
                                    </div>

                                    <select form="create" class="select2Basic select2 form-select form-select-lg"
                                        data-allow-clear="true" name="branch_id">
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr class="my-3 mx-n4" />

                            <div class="source-item pt-2">
                                @csrf
                                <div class="mb-3" data-repeater-list="product">
                                    <div class="repeater-wrapper pt-0 pt-md-4" data-repeater-item>
                                        <div class="d-flex border rounded position-relative pe-0 product-item-container">
                                            <div class="row w-100 p-3">
                                                <div class="col-md-6 col-12 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">الصنف</p>
                                                    <select class="select2 select2Basic form-select form-select-lg product-select"
                                                        data-allow-clear="true" name="product_id">
                                                        <option value="">اختر الصنف...</option>
                                                        @foreach ($products as $index => $product)
                                                            <option value="{{ $product->id }}"
                                                                data-qty="{{ $qty[$index]['qty'] }}"
                                                                data-name="{{ $product->name }}"
                                                                data-code="{{ $product->code }}">
                                                                {{ $product->code . ' - ' . $product->name }} ({{ $qty[$index]['qty'] }})
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <!-- Individual stock error feedback -->
                                                    <div class="stock-error-feedback text-danger mt-1 small fw-bold d-none">
                                                        <i class="ti ti-alert-triangle me-1"></i>
                                                        <span class="stock-error-text"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 col-12 mb-md-0 mb-3">
                                                    <p class="mb-2 repeater-title">الكمية</p>
                                                    <input type="decimal" class="form-control invoice-item-qty"
                                                        placeholder="1" step=".01" min="0" max=""
                                                        name="qty" />
                                                </div>
                                            </div>
                                            <!-- Delete Row Button -->
                                            <div class="d-flex flex-column align-items-center justify-content-center border-start px-3">
                                                <i class="ti ti-x cursor-pointer text-muted btn-delete-row" data-repeater-delete title="حذف الصنف"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pb-4">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" id='data_repeater'
                                            data-repeater-create>اضافة صنف</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Invoice Add-->

            <!-- Invoice Actions -->
            <div class="col-lg-3 col-12 invoice-actions">
                <div class="card mb-4">
                    <div class="card-body">
                        <button form="create" type="submit" id="btn-submit-exchange" class="btn btn-primary d-grid w-100 mb-2">
                            <span class="d-flex align-items-center justify-content-center text-nowrap">حفظ</span>
                        </button>
                        <a href="{{ route('exchanged product') }}"
                            class="btn btn-label-secondary d-grid w-100 mb-2">الغاء</a>
                    </div>
                </div>
            </div>
            <!-- /Invoice Actions -->
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
    <script src="{{ asset('vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ asset('vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('js/forms-pickers.js') }}"></script>
    <script src="{{ asset('js/app-invoice-add.js') }}"></script>

    <script>
        $(function () {
            const checkStockUrl = "{{ route('product_exchange.check_stock') }}";
            const csrfToken = "{{ csrf_token() }}";

            // Set red error state on row
            function setRowStockError($rowContainer, message) {
                $rowContainer.addClass('has-stock-error');
                $rowContainer.attr('data-stock-invalid', 'true');
                $rowContainer.find('.stock-error-text').text(message);
                $rowContainer.find('.stock-error-feedback').removeClass('d-none');
            }

            // Clear error state on row
            function clearRowStockError($rowContainer) {
                $rowContainer.removeClass('has-stock-error');
                $rowContainer.removeAttr('data-stock-invalid');
                $rowContainer.find('.stock-error-text').text('');
                $rowContainer.find('.stock-error-feedback').addClass('d-none');
            }

            // Update top alert banner based on currently invalid rows
            function updateStockAlertBanner(errorItems) {
                const $alert = $('#stock-error-alert');
                const invalidRows = $('[data-stock-invalid="true"]');

                if (invalidRows.length > 0 || (errorItems && errorItems.length > 0)) {
                    let msg = 'يوجد صنف أو أكثر كميته في المخزن الرئيسي (0): ';
                    if (errorItems && errorItems.length > 0) {
                        msg += '<strong>' + errorItems.join('، ') + '</strong>. ';
                    }
                    msg += 'برجاء تعديل أو إزالة الأصناف المحددة باللون الأحمر ليتم الحفظ.';
                    $('#stock-error-message').html(msg);
                    $alert.removeClass('d-none');
                } else {
                    $alert.addClass('d-none');
                }
            }

            // Scroll to the first invalid row
            function scrollToFirstError() {
                const $firstError = $('.has-stock-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 120
                    }, 400);
                } else {
                    const $alert = $('#stock-error-alert');
                    if (!$alert.hasClass('d-none')) {
                        $('html, body').animate({
                            scrollTop: $alert.offset().top - 120
                        }, 400);
                    }
                }
            }

            // Validate a single product row via AJAX and instant check
            function validateProductRow($select) {
                const $rowContainer = $select.closest('.product-item-container');
                const productId = $select.val();

                if (!productId) {
                    clearRowStockError($rowContainer);
                    updateStockAlertBanner();
                    return;
                }

                const $option = $select.find('option:selected');
                const initialQty = parseFloat($option.data('qty'));
                const productName = $option.data('name') || $option.text();

                // Instant UI feedback from data attribute if available
                if (!isNaN(initialQty) && initialQty <= 0) {
                    setRowStockError($rowContainer, `لا يمكن صرف هذا الصنف: الكمية في المخزن الرئيسي (${initialQty}) غير متاحة`);
                    updateStockAlertBanner([productName + ' (' + initialQty + ')']);
                }

                // Call AJAX endpoint for verified real-time stock check
                $.ajax({
                    url: checkStockUrl,
                    type: 'POST',
                    data: {
                        product_id: productId,
                        _token: csrfToken
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            if (!res.is_available || res.current_stock <= 0) {
                                setRowStockError($rowContainer, `لا يمكن صرف هذا الصنف: الكمية في المخزن الرئيسي (${res.current_stock}) غير متاحة`);
                                updateStockAlertBanner([res.product_name + ' (' + res.current_stock + ')']);
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(`الصنف "${res.product_name}" رصيده (${res.current_stock}) في المخزن الرئيسي - غير متاح!`, 'تنبيه مخزون');
                                }
                            } else {
                                clearRowStockError($rowContainer);
                                updateStockAlertBanner();
                            }
                        }
                    },
                    error: function (xhr) {
                        console.error('Stock verification request failed:', xhr);
                    }
                });
            }

            // Listen for changes on any product dropdown (delegated to support repeater items)
            $(document).on('change', 'select[name*="product_id"]', function () {
                validateProductRow($(this));
            });

            // When a repeater row is deleted, re-evaluate global error status
            $(document).on('click', '[data-repeater-delete]', function () {
                setTimeout(function () {
                    updateStockAlertBanner();
                }, 300);
            });

            // When adding a new repeater row, ensure clean state on new item
            $('#data_repeater').on('click', function () {
                setTimeout(function () {
                    const $newRow = $('.repeater-wrapper').last().find('.product-item-container');
                    clearRowStockError($newRow);
                }, 100);
            });

            // Intercept form submission to validate all items via AJAX before submitting
            $('#create').on('submit', function (e) {
                const form = this;
                let hasClientError = false;
                const errorNames = [];
                const productsToCheck = [];

                $('.repeater-wrapper').each(function () {
                    const $rowContainer = $(this).find('.product-item-container');
                    const $select = $rowContainer.find('select[name*="product_id"]');
                    const productId = $select.val();

                    if (productId) {
                        productsToCheck.push({ product_id: productId });
                        const $option = $select.find('option:selected');
                        const initialQty = parseFloat($option.data('qty'));
                        const name = $option.data('name') || $option.text();

                        if ($rowContainer.attr('data-stock-invalid') === 'true' || (!isNaN(initialQty) && initialQty <= 0)) {
                            hasClientError = true;
                            setRowStockError($rowContainer, `لا يمكن صرف هذا الصنف: الكمية في المخزن الرئيسي (${isNaN(initialQty) ? 0 : initialQty}) غير متاحة`);
                            errorNames.push(name + ' (' + (isNaN(initialQty) ? 0 : initialQty) + ')');
                        }
                    }
                });

                if (productsToCheck.length === 0) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('يرجى اختيار صنف واحد على الأقل للصرف', 'تنبيه');
                    }
                    return false;
                }

                if (hasClientError) {
                    e.preventDefault();
                    updateStockAlertBanner(errorNames);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('يوجد أصناف محددة باللون الأحمر رصيدها (0) ولا يمكن صرفها!', 'خطأ في الصرف');
                    }
                    scrollToFirstError();
                    return false;
                }

                // Prevent immediate submission and perform batch AJAX validation check
                e.preventDefault();
                const $submitBtn = $('#btn-submit-exchange, button[form="create"]');
                $submitBtn.prop('disabled', true).addClass('disabled');

                $.ajax({
                    url: checkStockUrl,
                    type: 'POST',
                    data: {
                        products: productsToCheck,
                        _token: csrfToken
                    },
                    dataType: 'json',
                    success: function (res) {
                        $submitBtn.prop('disabled', false).removeClass('disabled');

                        if (res.success && res.has_errors) {
                            res.items.forEach(function (item) {
                                if (!item.is_available || item.current_stock <= 0) {
                                    $('.repeater-wrapper').each(function () {
                                        const $rowContainer = $(this).find('.product-item-container');
                                        if ($rowContainer.find('select[name*="product_id"]').val() == item.product_id) {
                                            setRowStockError($rowContainer, `لا يمكن صرف هذا الصنف: الكمية في المخزن الرئيسي (${item.current_stock}) غير متاحة`);
                                        }
                                    });
                                }
                            });

                            updateStockAlertBanner(res.error_products);
                            if (typeof toastr !== 'undefined') {
                                toastr.error('لا يمكن إتمام الصرف لأن بعض الأصناف رصيدها في المخزن الرئيسي (0)!', 'خطأ في الصرف');
                            }
                            scrollToFirstError();
                        } else {
                            // All products valid, submit the form safely
                            form.submit();
                        }
                    },
                    error: function (xhr) {
                        $submitBtn.prop('disabled', false).removeClass('disabled');
                        console.error('Batch stock check error, proceeding to backend validation:', xhr);
                        // In case of network error, proceed with backend validation
                        form.submit();
                    }
                });
            });

            // Initial check on load if any product is already pre-selected
            $('select[name*="product_id"]').each(function () {
                if ($(this).val()) {
                    validateProductRow($(this));
                }
            });
        });
    </script>
@endsection
