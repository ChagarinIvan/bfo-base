<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="http://orient.by/favicon.ico" type="image/x-icon">
    <link rel="icon" href="http://orient.by/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous"
    >
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css"
          integrity="sha512-mR/b5Y7FRsKqrYZou7uysnOdCIJib/7r5QeJMFvLNHNhtye3xJp1TdJVPLtetkukFn227nKpXD9OjUc09lx97Q=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link href="https://unpkg.com/jquery-resizable-columns@0.2.3/dist/jquery.resizableColumns.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/sticky-header/bootstrap-table-sticky-header.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css">
    <style>
        #table {
            caret-color: transparent;
            cursor: default;
        }
    </style>
<title>{{ \Illuminate\Support\Str::limit($__env->yieldContent('title'), 20) }}</title>
</head>
<body style="padding-bottom: 55px;">
    @include('layouts.navbar')
    <main>
        <div class="container-fluid">
            <h2 id="up">@yield('title')</h2>
            @yield('content')
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"
    ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"
    ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"
            integrity="sha512-FHZVRMUW9FsXobt+ONiix6Z0tIkxvQfxtCSirkKc5Sb4TKHmqq1dZa8DphF0XqKb3ldLu/wgMa8mT6uXiLlRlw=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    ></script>
    <script src="https://unpkg.com/jquery-resizable-columns@0.2.3/dist/jquery.resizableColumns.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/resizable/bootstrap-table-resizable.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/cookie/bootstrap-table-cookie.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>
    @yield('script')
    <script>
        $.fn.bootstrapTable.locales['ru-RU'] = $.fn.bootstrapTable.locales['ru'] = {
            formatRecordsPerPage (pageNumber) {
                return `${pageNumber} {{ __('app.table.rows_count') }}`
            },
            formatShowingRows (pageFrom, pageTo, totalRows, totalNotFiltered) {
                return `{{ __('app.table.items') }} ${pageFrom} {{ __('app.table.po') }} ${pageTo} {{ __('app.table.iz') }} ${totalRows}`
            },
            formatSearch () {
                return '{{ __('app.common.search') }}'
            },
            formatNoMatches () {
                return '{{ __('app.table.nothing_found') }}'
            },
        }

        $.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales['ru-RU'])
    </script>
    <script>
        function customSort(sortName, sortOrder, data) {
            let order = sortOrder === 'desc' ? -1 : 1;

            data.sort(function (a, b) {
                if (@yield('table_extracted_dates_columns', '[]').includes(sortName)) {
                    let dateAa = (a[sortName]).match(/\d\d\d\d-\d\d-\d\d/m);
                    let dateBb = (b[sortName]).match(/\d\d\d\d-\d\d-\d\d/m);
                    let aa = new Date(dateAa);
                    let bb = new Date(dateBb);

                    return (aa - bb) * order;
                } else {
                    let aa = @yield('table_extracted_columns', '[]').includes(sortName) ? (a[sortName]).match(/">[^<]+<\//gm) : +((a[sortName] + '').replace(/[^\d]/g, ''));
                    let bb = @yield('table_extracted_columns', '[]').includes(sortName) ? (b[sortName]).match(/">[^<]+<\//gm) : +((b[sortName] + '').replace(/[^\d]/g, ''));

                    if (aa < bb) {
                        return order * -1
                    }
                    if (aa > bb) {
                        return order
                    }
                    return 0
                }
            })
        }
    </script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>
